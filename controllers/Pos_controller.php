<?php

defined("BASEPATH") or exit("No direct script access allowed");

class Pos_controller extends AdminController
{
    private function stream_csv_download($filename, $headers, $rows)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    private function can_cashier()
    {
        return is_admin() || staff_can('cashiering', 'pos');
    }

    private function can_view_cashier_report()
    {
        return is_admin() || staff_can('cashier_report', 'pos') || staff_can('cashier_report_own', 'pos');
    }

    private function can_view_all_cashier_report()
    {
        return is_admin() || staff_can('cashier_report', 'pos');
    }

    private function can_view_z_report()
    {
        return is_admin() || staff_can('z_report', 'pos') || staff_can('z_report_own', 'pos');
    }

    private function can_view_all_z_report()
    {
        return is_admin() || staff_can('z_report', 'pos');
    }

    private function can_manage_rewards()
    {
        return is_admin() || staff_can('rewards_settings', 'pos');
    }

    private function is_walkin_customer($client_id)
    {
        if ((int) $client_id <= 0) {
            return true;
        }

        $row = $this->db
            ->select('company')
            ->where('userid', (int) $client_id)
            ->get($this->db->dbprefix('clients'))
            ->row();

        if (!$row) {
            return false;
        }

        return strtolower(trim((string) $row->company)) === 'walk-in customer';
    }

    private function calculate_points_earned_from_cart($cart, $settings)
    {
        if (empty($cart)) {
            return 0;
        }

        $item_ids = [];
        foreach ($cart as $item) {
            if (isset($item['id'])) {
                $item_ids[] = (int) $item['id'];
            }
        }

        $item_rules = [];
        if (!empty($item_ids)) {
            $rows = $this->db
                ->select('id, reward_type, reward_points_mode, reward_points_value')
                ->where_in('id', $item_ids)
                ->get($this->db->dbprefix('items'))
                ->result_array();

            foreach ($rows as $row) {
                $item_rules[(int) $row['id']] = $row;
            }
        }

        $product_rate = isset($settings['product_points_per_currency']) ? (float) $settings['product_points_per_currency'] : 0;
        $service_rate = isset($settings['service_points_per_currency']) ? (float) $settings['service_points_per_currency'] : 0;
        $total_points = 0;

        foreach ($cart as $item) {
            $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
            $rate = isset($item['rate']) ? (float) $item['rate'] : 0;
            $item_id = isset($item['id']) ? (int) $item['id'] : 0;

            if ($qty <= 0 || $rate <= 0) {
                continue;
            }

            $line_points = 0;
            $rule = isset($item_rules[$item_id]) ? $item_rules[$item_id] : null;
            $mode = $rule && !empty($rule['reward_points_mode']) ? $rule['reward_points_mode'] : 'type_rate';
            $reward_type = $rule && !empty($rule['reward_type']) ? strtolower(trim($rule['reward_type'])) : 'product';

            if ($mode === 'custom_points') {
                $custom = $rule ? (float) $rule['reward_points_value'] : 0;
                $line_points = floor($qty * max($custom, 0));
            } else {
                $points_rate = $reward_type === 'service' ? $service_rate : $product_rate;
                $line_points = floor(($qty * $rate) * max($points_rate, 0));
            }

            $total_points += (int) $line_points;
        }

        return max((int) $total_points, 0);
    }

    private function expire_customer_points_if_needed($client_id, $settings)
    {
        $client_id = (int) $client_id;
        if ($client_id <= 0) {
            return 0;
        }

        $expiry_enabled = !empty($settings['expiry_enabled']) && (int) $settings['expiry_enabled'] === 1;
        $expiry_days = isset($settings['expiry_days']) ? (int) $settings['expiry_days'] : 0;

        if (!$expiry_enabled || $expiry_days <= 0) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $transactions_table = $this->db->dbprefix('pos_reward_transactions');

        $rows = $this->db
            ->select('id, points_available')
            ->where('client_id', $client_id)
            ->where('points_available >', 0)
            ->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', $now)
            ->get($transactions_table)
            ->result_array();

        if (empty($rows)) {
            return 0;
        }

        $expired_points = 0;
        foreach ($rows as $row) {
            $expired_points += (int) $row['points_available'];
            $this->db->where('id', (int) $row['id'])->update($transactions_table, [
                'points_available' => 0,
            ]);
        }

        return $expired_points;
    }

    private function consume_points_fifo($client_id, $points_to_consume)
    {
        $points_to_consume = (int) $points_to_consume;
        if ((int) $client_id <= 0 || $points_to_consume <= 0) {
            return 0;
        }

        $transactions_table = $this->db->dbprefix('pos_reward_transactions');
        $consumed = 0;

        $rows = $this->db
            ->select('id, points_available')
            ->where('client_id', (int) $client_id)
            ->where('points_available >', 0)
            ->order_by('created_at', 'asc')
            ->order_by('id', 'asc')
            ->get($transactions_table)
            ->result_array();

        foreach ($rows as $row) {
            if ($consumed >= $points_to_consume) {
                break;
            }

            $available = (int) $row['points_available'];
            $need = $points_to_consume - $consumed;
            $take = min($available, $need);

            if ($take <= 0) {
                continue;
            }

            $this->db->where('id', (int) $row['id'])->update($transactions_table, [
                'points_available' => $available - $take,
            ]);

            $consumed += $take;
        }

        return $consumed;
    }

    public function __construct()
    {
        parent::__construct();

        $saved_db_debug = $this->db->db_debug;
        $this->db->db_debug = false;

        $items_table = $this->db->dbprefix('items');
        if ($this->db->table_exists($items_table)) {
            if (!$this->db->field_exists('barcode', $items_table)) {
                $this->db->query("ALTER TABLE " . $this->db->dbprefix('items') . " ADD `barcode` VARCHAR(255) NULL;");
            }

            if (!$this->db->field_exists('pos_image', $items_table)) {
                $this->db->query("ALTER TABLE " . $this->db->dbprefix('items') . " ADD `pos_image` TEXT NULL;");
            }

            if (!$this->db->field_exists('reward_type', $items_table)) {
                $this->db->query("ALTER TABLE " . $this->db->dbprefix('items') . " ADD `reward_type` VARCHAR(20) NOT NULL DEFAULT 'product';");
            }

            if (!$this->db->field_exists('reward_points_mode', $items_table)) {
                $this->db->query("ALTER TABLE " . $this->db->dbprefix('items') . " ADD `reward_points_mode` VARCHAR(30) NOT NULL DEFAULT 'type_rate';");
            }

            if (!$this->db->field_exists('reward_points_value', $items_table)) {
                $this->db->query("ALTER TABLE " . $this->db->dbprefix('items') . " ADD `reward_points_value` DECIMAL(15,2) NOT NULL DEFAULT 0;");
            }
        }

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('pos_sessions') . "` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `staff_id` INT UNSIGNED NOT NULL,
                `opening_balance` DECIMAL(15,2) NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'open',
                `created_at` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('pos_reward_settings') . "` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `product_points_per_currency` DECIMAL(15,4) NOT NULL DEFAULT 1,
                `service_points_per_currency` DECIMAL(15,4) NOT NULL DEFAULT 0.5,
                `redeem_value_per_point` DECIMAL(15,4) NOT NULL DEFAULT 0.01,
                `min_redeem_points` INT NOT NULL DEFAULT 100,
                `allow_walkin_rewards` TINYINT(1) NOT NULL DEFAULT 0,
                `expiry_enabled` TINYINT(1) NOT NULL DEFAULT 0,
                `expiry_days` INT NOT NULL DEFAULT 365,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $reward_settings_table = $this->db->dbprefix('pos_reward_settings');
        if ($this->db->table_exists($reward_settings_table)) {
            if (!$this->db->field_exists('expiry_enabled', $reward_settings_table)) {
                $this->db->query("ALTER TABLE `" . $reward_settings_table . "` ADD `expiry_enabled` TINYINT(1) NOT NULL DEFAULT 0;");
            }
            if (!$this->db->field_exists('expiry_days', $reward_settings_table)) {
                $this->db->query("ALTER TABLE `" . $reward_settings_table . "` ADD `expiry_days` INT NOT NULL DEFAULT 365;");
            }
        }

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('pos_reward_balances') . "` (
                `client_id` INT UNSIGNED PRIMARY KEY,
                `points_balance` INT NOT NULL DEFAULT 0,
                `updated_at` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('pos_reward_transactions') . "` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `client_id` INT UNSIGNED NOT NULL,
                `invoice_id` INT UNSIGNED NOT NULL,
                `staff_id` INT UNSIGNED NOT NULL,
                `points_earned` INT NOT NULL DEFAULT 0,
                `points_redeemed` INT NOT NULL DEFAULT 0,
                `points_balance_after` INT NOT NULL DEFAULT 0,
                `points_available` INT NOT NULL DEFAULT 0,
                `expires_at` DATETIME NULL,
                `note` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL,
                KEY `idx_client` (`client_id`),
                KEY `idx_invoice` (`invoice_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        $reward_transactions_table = $this->db->dbprefix('pos_reward_transactions');
        if ($this->db->table_exists($reward_transactions_table)) {
            if (!$this->db->field_exists('points_available', $reward_transactions_table)) {
                $this->db->query("ALTER TABLE `" . $reward_transactions_table . "` ADD `points_available` INT NOT NULL DEFAULT 0;");
                $this->db->query("UPDATE `" . $reward_transactions_table . "` SET `points_available` = `points_earned` WHERE `points_available` = 0 AND `points_earned` > 0;");
            }
            if (!$this->db->field_exists('expires_at', $reward_transactions_table)) {
                $this->db->query("ALTER TABLE `" . $reward_transactions_table . "` ADD `expires_at` DATETIME NULL;");
            }
        }

        $this->db->db_debug = $saved_db_debug;

        if (!is_staff_logged_in()) {
            redirect(admin_url('authentication'));
        }
    }

    public function index()
    {
        if (!$this->can_cashier()) {
            access_denied('pos_cashiering');
        }

        $this->load->model('Pos_model', 'pos_model');
        $data['products'] = $this->pos_model->get_pos_products();
        $data["title"] = "B2C Quick POS";
        $data['customers'] = [];
        $data['reward_settings'] = $this->pos_model->get_rewards_settings();

        // Dynamic direct query to grab both online gateways and offline modes reliably
        $data['payment_modes'] = [];
        try {
            $query = $this->db->where('active', 1)->get($this->db->dbprefix('payment_modes'));
            if ($query) {
                $data['payment_modes'] = $query->result_array();
            }

            $customers_query = $this->db
                ->select('userid, company')
                ->from($this->db->dbprefix('clients'))
                ->order_by('company', 'asc')
                ->limit(200)
                ->get();

            if ($customers_query) {
                $data['customers'] = $customers_query->result_array();
            }
        } catch (Exception $e) {
            // Safe runtime exception fallback block
        }

        $this->load->view("pos/dashboard", $data);
    }

    public function rewards_settings()
    {
        if (!$this->can_manage_rewards()) {
            access_denied('pos_rewards_settings');
        }

        $this->load->model('Pos_model', 'pos_model');
        $data['title'] = 'POS Rewards Settings';
        $data['settings'] = $this->pos_model->get_rewards_settings();
        $this->load->view('pos/rewards_settings', $data);
    }

    public function save_rewards_settings()
    {
        if (!$this->can_manage_rewards()) {
            access_denied('pos_rewards_settings_save');
        }

        if ($this->input->method(true) !== 'POST') {
            redirect(admin_url('pos/pos_controller/rewards_settings'));
        }

        $this->load->model('Pos_model', 'pos_model');
        $data = [
            'enabled'                     => $this->input->post('enabled') ? 1 : 0,
            'product_points_per_currency' => (float) $this->input->post('product_points_per_currency'),
            'service_points_per_currency' => (float) $this->input->post('service_points_per_currency'),
            'redeem_value_per_point'      => (float) $this->input->post('redeem_value_per_point'),
            'min_redeem_points'           => (int) $this->input->post('min_redeem_points'),
            'allow_walkin_rewards'        => $this->input->post('allow_walkin_rewards') ? 1 : 0,
            'expiry_enabled'              => $this->input->post('expiry_enabled') ? 1 : 0,
            'expiry_days'                 => (int) $this->input->post('expiry_days'),
            'updated_at'                  => date('Y-m-d H:i:s'),
        ];

        if ($data['product_points_per_currency'] < 0) {
            $data['product_points_per_currency'] = 0;
        }
        if ($data['service_points_per_currency'] < 0) {
            $data['service_points_per_currency'] = 0;
        }
        if ($data['redeem_value_per_point'] < 0.0001) {
            $data['redeem_value_per_point'] = 0.0001;
        }
        if ($data['min_redeem_points'] < 0) {
            $data['min_redeem_points'] = 0;
        }
        if ($data['expiry_days'] < 1) {
            $data['expiry_days'] = 1;
        }

        $existing = $this->pos_model->get_rewards_settings();
        if (!$existing || empty($existing['id'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $this->pos_model->update_rewards_settings($data);
        set_alert('success', 'Rewards settings updated successfully.');
        redirect(admin_url('pos/pos_controller/rewards_settings'));
    }

    public function customer_points($client_id = 0)
    {
        header('Content-Type: application/json');

        if (!$this->can_cashier()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        $client_id = (int) $client_id;
        if ($client_id <= 0) {
            echo json_encode(['success' => true, 'points' => 0]);
            return;
        }

        $this->load->model('Pos_model', 'pos_model');
        $settings = $this->pos_model->get_rewards_settings();
        $expired = $this->expire_customer_points_if_needed($client_id, $settings);
        if ($expired > 0) {
            $current = $this->pos_model->get_customer_points_balance($client_id);
            $this->pos_model->set_customer_points_balance($client_id, max(0, $current - $expired));
        }
        $points = $this->pos_model->get_customer_points_balance($client_id);
        echo json_encode(['success' => true, 'points' => (int) $points]);
    }

    public function rewards_history()
    {
        if (!$this->can_manage_rewards()) {
            access_denied('pos_rewards_history');
        }

        $this->load->model('Pos_model', 'pos_model');

        $from_date = trim((string) $this->input->get('from_date'));
        $to_date = trim((string) $this->input->get('to_date'));
        $staff_id = (int) $this->input->get('staff_id');
        $client_id = (int) $this->input->get('client_id');

        if ($from_date === '') {
            $from_date = date('Y-m-d', strtotime('-30 days'));
        }
        if ($to_date === '') {
            $to_date = date('Y-m-d');
        }

        $data['title'] = 'POS Rewards History';
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['selected_staff_id'] = $staff_id;
        $data['selected_client_id'] = $client_id;
        $data['staff_options'] = $this->pos_model->get_reward_history_staff_list($from_date, $to_date);
        $data['reward_customers'] = $this->db
            ->select('userid, company')
            ->order_by('company', 'asc')
            ->get($this->db->dbprefix('clients'))
            ->result_array();
        $data['rows'] = $this->pos_model->get_reward_history_rows($from_date, $to_date, $staff_id, $client_id, 2000);

        $this->load->view('pos/rewards_history', $data);
    }

    public function rewards_history_csv()
    {
        if (!$this->can_manage_rewards()) {
            access_denied('pos_rewards_history_csv');
        }

        $this->load->model('Pos_model', 'pos_model');

        $from_date = trim((string) $this->input->get('from_date'));
        $to_date = trim((string) $this->input->get('to_date'));
        $staff_id = (int) $this->input->get('staff_id');
        $client_id = (int) $this->input->get('client_id');

        if ($from_date === '') {
            $from_date = date('Y-m-d', strtotime('-30 days'));
        }
        if ($to_date === '') {
            $to_date = date('Y-m-d');
        }

        $rows = $this->pos_model->get_reward_history_rows($from_date, $to_date, $staff_id, $client_id, 10000);

        $csv_rows = [];
        foreach ($rows as $row) {
            $csv_rows[] = [
                isset($row['created_at']) ? $row['created_at'] : '',
                trim((string) $row['customer_name']) !== '' ? $row['customer_name'] : 'Unknown',
                !empty($row['invoice_id']) ? (int) $row['invoice_id'] : '',
                trim((string) $row['staff_name']) !== '' ? $row['staff_name'] : 'Unknown',
                (int) $row['points_earned'],
                (int) $row['points_redeemed'],
                (int) $row['points_available'],
                (int) $row['points_balance_after'],
                !empty($row['expires_at']) ? $row['expires_at'] : '',
                isset($row['note']) ? $row['note'] : '',
            ];
        }

        $this->stream_csv_download(
            'pos-rewards-history-' . $from_date . '-to-' . $to_date . '.csv',
            ['Date Time', 'Customer', 'Invoice ID', 'Staff', 'Points Earned', 'Points Redeemed', 'Points Available', 'Balance After', 'Expires At', 'Note'],
            $csv_rows
        );
    }

    public function cashier_report()
    {
        if (!$this->can_view_cashier_report()) {
            access_denied('pos_cashier_report');
        }

        $this->load->model('Pos_model', 'pos_model');

        $from_date = trim((string) $this->input->get('from_date'));
        $to_date = trim((string) $this->input->get('to_date'));
        $staff_id = (int) $this->input->get('staff_id');

        if ($from_date === '') {
            $from_date = date('Y-m-d', strtotime('-30 days'));
        }
        if ($to_date === '') {
            $to_date = date('Y-m-d');
        }

        if (!$this->can_view_all_cashier_report()) {
            $staff_id = (int) get_staff_user_id();
        }

        $data['title'] = 'POS Cashier Report';
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['selected_staff_id'] = $staff_id;
        $data['staff_options'] = $this->pos_model->get_pos_staff_list($from_date, $to_date);
        $data['rows'] = $this->pos_model->get_cashier_report_rows($from_date, $to_date, $staff_id);
        $data['reward_summary'] = $this->pos_model->get_reward_summary_for_period($from_date, $to_date, $staff_id);
        $data['can_view_all'] = $this->can_view_all_cashier_report();

        $this->load->view('pos/cashier_report', $data);
    }

    public function z_report()
    {
        if (!$this->can_view_z_report()) {
            access_denied('pos_z_report');
        }

        $this->load->model('Pos_model', 'pos_model');

        $report_date = trim((string) $this->input->get('report_date'));
        $staff_id = (int) $this->input->get('staff_id');

        if ($report_date === '') {
            $report_date = date('Y-m-d');
        }

        if (!$this->can_view_all_z_report()) {
            $staff_id = (int) get_staff_user_id();
        }

        $data['title'] = 'POS Z Report';
        $data['report_date'] = $report_date;
        $data['selected_staff_id'] = $staff_id;
        $data['staff_options'] = $this->pos_model->get_pos_staff_list($report_date, $report_date);
        $data['report'] = $this->pos_model->get_z_report_data($report_date, $staff_id);
        $data['reward_summary'] = $this->pos_model->get_reward_summary_for_day($report_date, $staff_id);
        $data['can_view_all'] = $this->can_view_all_z_report();

        $this->load->view('pos/z_report', $data);
    }

    public function cashier_report_csv()
    {
        if (!$this->can_view_cashier_report()) {
            access_denied('pos_cashier_report_csv');
        }

        $this->load->model('Pos_model', 'pos_model');

        $from_date = trim((string) $this->input->get('from_date'));
        $to_date = trim((string) $this->input->get('to_date'));
        $staff_id = (int) $this->input->get('staff_id');

        if ($from_date === '') {
            $from_date = date('Y-m-d', strtotime('-30 days'));
        }
        if ($to_date === '') {
            $to_date = date('Y-m-d');
        }

        if (!$this->can_view_all_cashier_report()) {
            $staff_id = (int) get_staff_user_id();
        }

        $rows = $this->pos_model->get_cashier_report_rows($from_date, $to_date, $staff_id);
        $reward_summary = $this->pos_model->get_reward_summary_for_period($from_date, $to_date, $staff_id);

        $csv_rows = [];
        $csv_rows[] = ['Summary', 'Points Earned', number_format((float) $reward_summary['points_earned'], 0, '.', '')];
        $csv_rows[] = ['Summary', 'Points Redeemed', number_format((float) $reward_summary['points_redeemed'], 0, '.', '')];
        $csv_rows[] = ['Summary', 'Reward Transactions', number_format((float) $reward_summary['tx_count'], 0, '.', '')];
        $csv_rows[] = [];
        foreach ($rows as $row) {
            $payment_modes = '-';
            if (!empty($row['payment_modes']) && is_array($row['payment_modes'])) {
                $mode_parts = [];
                foreach ($row['payment_modes'] as $mode) {
                    $mode_parts[] = $mode['name'] . ': ' . number_format((float) $mode['amount'], 2, '.', '');
                }
                $payment_modes = implode(' | ', $mode_parts);
            }

            $open_amount = (float) $row['total'] - (float) $row['paid_total'];

            $csv_rows[] = [
                format_invoice_number($row['id']),
                $row['date'],
                trim($row['cashier_name']) !== '' ? $row['cashier_name'] : 'Unknown',
                $row['customer_name'],
                $payment_modes,
                number_format((float) $row['discount_total'], 2, '.', ''),
                number_format((float) $row['total'], 2, '.', ''),
                number_format((float) $row['paid_total'], 2, '.', ''),
                number_format((float) $open_amount, 2, '.', ''),
            ];
        }

        $this->stream_csv_download(
            'pos-cashier-report-' . $from_date . '-to-' . $to_date . '.csv',
            ['Invoice', 'Date', 'Cashier', 'Customer', 'Payment Modes', 'Discount', 'Total', 'Paid', 'Open'],
            $csv_rows
        );
    }

    public function z_report_csv()
    {
        if (!$this->can_view_z_report()) {
            access_denied('pos_z_report_csv');
        }

        $this->load->model('Pos_model', 'pos_model');

        $report_date = trim((string) $this->input->get('report_date'));
        $staff_id = (int) $this->input->get('staff_id');

        if ($report_date === '') {
            $report_date = date('Y-m-d');
        }

        if (!$this->can_view_all_z_report()) {
            $staff_id = (int) get_staff_user_id();
        }

        $report = $this->pos_model->get_z_report_data($report_date, $staff_id);
        $reward_summary = $this->pos_model->get_reward_summary_for_day($report_date, $staff_id);

        $csv_rows = [];
        $csv_rows[] = ['Summary', 'Report Date', $report_date];
        $csv_rows[] = ['Summary', 'Invoices', number_format((float) $report['summary']['invoices_count'], 0, '.', '')];
        $csv_rows[] = ['Summary', 'Gross Sales', number_format((float) $report['summary']['gross_sales'], 2, '.', '')];
        $csv_rows[] = ['Summary', 'Discounts', number_format((float) $report['summary']['discounts'], 2, '.', '')];
        $csv_rows[] = ['Summary', 'Net Sales', number_format((float) $report['summary']['net_sales'], 2, '.', '')];
        $csv_rows[] = ['Summary', 'Total Payments', number_format((float) $report['summary']['payments_total'], 2, '.', '')];
        $csv_rows[] = ['Summary', 'Variance', number_format((float) $report['summary']['payments_total'] - (float) $report['summary']['net_sales'], 2, '.', '')];
        $csv_rows[] = ['Summary', 'Points Earned', number_format((float) $reward_summary['points_earned'], 0, '.', '')];
        $csv_rows[] = ['Summary', 'Points Redeemed', number_format((float) $reward_summary['points_redeemed'], 0, '.', '')];
        $csv_rows[] = ['Summary', 'Reward Transactions', number_format((float) $reward_summary['tx_count'], 0, '.', '')];

        $csv_rows[] = [];
        $csv_rows[] = ['Payment Mode Breakdown', 'Transactions', 'Amount'];
        if (!empty($report['payment_modes'])) {
            foreach ($report['payment_modes'] as $mode_row) {
                $csv_rows[] = [
                    $mode_row['mode_name'],
                    number_format((float) $mode_row['tx_count'], 0, '.', ''),
                    number_format((float) $mode_row['total_amount'], 2, '.', ''),
                ];
            }
        }

        $csv_rows[] = [];
        $csv_rows[] = ['Cashier Breakdown', 'Invoices', 'Payments'];
        if (!empty($report['cashiers'])) {
            foreach ($report['cashiers'] as $cashier_row) {
                $csv_rows[] = [
                    trim($cashier_row['cashier_name']) !== '' ? $cashier_row['cashier_name'] : 'Unknown',
                    number_format((float) $cashier_row['invoices_count'], 0, '.', ''),
                    number_format((float) $cashier_row['payments_total'], 2, '.', ''),
                ];
            }
        }

        $this->stream_csv_download(
            'pos-z-report-' . $report_date . '.csv',
            ['Section', 'Metric', 'Value'],
            $csv_rows
        );
    }

    public function z_report_pdf()
    {
        if (!$this->can_view_z_report()) {
            access_denied('pos_z_report_pdf');
        }

        $this->load->model('Pos_model', 'pos_model');

        $report_date = trim((string) $this->input->get('report_date'));
        $staff_id = (int) $this->input->get('staff_id');

        if ($report_date === '') {
            $report_date = date('Y-m-d');
        }

        if (!$this->can_view_all_z_report()) {
            $staff_id = (int) get_staff_user_id();
        }

        $report = $this->pos_model->get_z_report_data($report_date, $staff_id);
        $reward_summary = $this->pos_model->get_reward_summary_for_day($report_date, $staff_id);

        $staff_label = 'All Cashiers';
        if ($staff_id > 0) {
            $staff = $this->db
                ->select('firstname, lastname')
                ->where('staffid', $staff_id)
                ->get($this->db->dbprefix('staff'))
                ->row();
            if ($staff) {
                $staff_label = trim($staff->firstname . ' ' . $staff->lastname);
            }
        }

        $pdf_path = module_dir_path('pos', 'libraries/pdf/Pos_z_report_pdf.php');
        if (!file_exists($pdf_path)) {
            show_error('PDF renderer not found.', 500);
            return;
        }

        include_once $pdf_path;
        $pdf = (new Pos_z_report_pdf($report, $report_date, $staff_label, $reward_summary))->prepare();
        $pdf->Output('pos-z-report-' . $report_date . '.pdf', 'D');
    }

    public function quick_add_customer()
    {
        if (!$this->can_cashier()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        header('Content-Type: application/json');

        if (!$this->input->is_ajax_request() || $this->input->method(true) !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $company = trim((string) $this->input->post('company'));
        $phone = trim((string) $this->input->post('phonenumber'));
        $email = trim((string) $this->input->post('email'));

        if ($company === '') {
            echo json_encode(['success' => false, 'message' => 'Customer name is required.']);
            return;
        }

        $this->load->model('clients_model');
        $new_customer_id = $this->clients_model->add([
            'company'     => $company,
            'phonenumber' => $phone,
            'email'       => $email,
        ]);

        if (!$new_customer_id) {
            echo json_encode(['success' => false, 'message' => 'Unable to create customer.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Customer created successfully.',
            'customer' => [
                'id' => $new_customer_id,
                'name' => $company,
            ],
        ]);
    }

    public function checkout()
    {
        if (!$this->can_cashier()) {
            echo json_encode(['success' => false, 'message' => 'Access denied.']);
            return;
        }

        header('Content-Type: application/json');

        $previous_db_debug = $this->db->db_debug;
        $this->db->db_debug = false;

        $respond = function ($payload) use ($previous_db_debug) {
            $this->db->db_debug = $previous_db_debug;
            echo json_encode($payload);
        };

        if (!$this->input->is_ajax_request() && $this->input->method(true) !== 'POST') {
            $respond(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $cart = $this->input->post('cart');
        $split_payments_raw = $this->input->post('split_payments');
        $selected_customer_id = (int) $this->input->post('customer_id');
        $discount_type = strtolower(trim((string) $this->input->post('discount_type')));
        $discount_value = (float) $this->input->post('discount_value');
        $redeem_points = (int) $this->input->post('redeem_points');

        if (is_string($cart)) {
            $cart = json_decode($cart, true);
        }

        if (!is_array($cart) || empty($cart)) {
            $respond(['success' => false, 'message' => 'Cart is empty or invalid.']);
            return;
        }

        $this->load->model('clients_model');
        $this->load->model('Pos_model', 'pos_model');

        $walk_in_customer = $this->db
            ->where('company', 'Walk-In Customer')
            ->get($this->db->dbprefix('clients'))
            ->row();

        $walk_in_customer_id = 0;

        if ($selected_customer_id > 0) {
            $selected_customer = $this->db
                ->where('userid', $selected_customer_id)
                ->get($this->db->dbprefix('clients'))
                ->row();

            if ($selected_customer) {
                $walk_in_customer_id = (int) $selected_customer->userid;
            }
        }

        if (empty($walk_in_customer_id) && $walk_in_customer) {
            $walk_in_customer_id = $walk_in_customer->userid;
        } elseif (empty($walk_in_customer_id)) {
            $walk_in_customer_id = $this->clients_model->add([
                'company'        => 'Walk-In Customer',
                'billing_street' => 'POS Terminal',
            ]);
        }

        if (!$walk_in_customer_id) {
            $respond(['success' => false, 'message' => 'Unable to resolve walk-in customer.']);
            return;
        }

        $newitems = [];
        $total_amount = 0;

        foreach ($cart as $index => $item) {
            $qty = isset($item['quantity']) ? (float) $item['quantity'] : 0;
            $rate = isset($item['rate']) ? (float) $item['rate'] : 0;
            $name = isset($item['name']) ? $item['name'] : '';

            if ($qty <= 0 || $rate < 0 || $name === '') {
                continue;
            }

            $total_amount += ($qty * $rate);
            $newitems[] = [
                'order'            => $index,
                'description'      => $name,
                'long_description' => '',
                'qty'              => $qty,
                'rate'             => $rate,
                'unit'             => '',
            ];
        }

        if (empty($newitems) || $total_amount <= 0) {
            $respond(['success' => false, 'message' => 'No valid cart items found.']);
            return;
        }

        $discount_amount = 0;
        if ($discount_value > 0) {
            if ($discount_type === 'percent') {
                $discount_amount = ($total_amount * $discount_value) / 100;
            } else {
                $discount_amount = $discount_value;
            }

            if ($discount_amount > $total_amount) {
                $discount_amount = $total_amount;
            }
        }

        if ($discount_amount > 0) {
            $newitems[] = [
                'order'            => count($newitems),
                'description'      => 'POS Discount',
                'long_description' => '',
                'qty'              => 1,
                'rate'             => -1 * $discount_amount,
                'unit'             => '',
            ];
            $total_amount -= $discount_amount;
        }

        $reward_settings = $this->pos_model->get_rewards_settings();
        $rewards_enabled = !empty($reward_settings['enabled']) && (int) $reward_settings['enabled'] === 1;
        $eligible_for_rewards = $rewards_enabled;

        if ($eligible_for_rewards && empty($reward_settings['allow_walkin_rewards']) && $this->is_walkin_customer($walk_in_customer_id)) {
            $eligible_for_rewards = false;
        }

        $points_earned = 0;
        $points_redeemed = 0;
        $reward_redemption_amount = 0.0;
        $current_points_balance = 0;
        $updated_points_balance = 0;

        if ($eligible_for_rewards) {
            $expired_points = $this->expire_customer_points_if_needed($walk_in_customer_id, $reward_settings);
            if ($expired_points > 0) {
                $live_balance = (int) $this->pos_model->get_customer_points_balance($walk_in_customer_id);
                $this->pos_model->set_customer_points_balance($walk_in_customer_id, max(0, $live_balance - $expired_points));
            }

            $current_points_balance = (int) $this->pos_model->get_customer_points_balance($walk_in_customer_id);
            $updated_points_balance = $current_points_balance;

            if ($redeem_points > 0 && $total_amount > 0) {
                $redeem_value_per_point = isset($reward_settings['redeem_value_per_point'])
                    ? (float) $reward_settings['redeem_value_per_point']
                    : 0;
                $min_redeem_points = isset($reward_settings['min_redeem_points'])
                    ? (int) $reward_settings['min_redeem_points']
                    : 0;

                if ($redeem_value_per_point > 0) {
                    $max_by_balance = min($redeem_points, $current_points_balance);
                    $max_by_total = (int) floor($total_amount / $redeem_value_per_point);
                    $usable_points = min($max_by_balance, $max_by_total);

                    if ($min_redeem_points > 0 && $usable_points < $min_redeem_points) {
                        $usable_points = 0;
                    }

                    if ($usable_points > 0) {
                        $points_redeemed = (int) $usable_points;
                        $reward_redemption_amount = (float) number_format($points_redeemed * $redeem_value_per_point, 2, '.', '');

                        if ($reward_redemption_amount > $total_amount) {
                            $reward_redemption_amount = $total_amount;
                        }

                        if ($reward_redemption_amount > 0) {
                            $newitems[] = [
                                'order'            => count($newitems),
                                'description'      => 'Reward Points Redemption',
                                'long_description' => '',
                                'qty'              => 1,
                                'rate'             => -1 * $reward_redemption_amount,
                                'unit'             => '',
                            ];
                            $total_amount -= $reward_redemption_amount;
                        }
                    }
                }
            }

            $points_earned = $this->calculate_points_earned_from_cart($cart, $reward_settings);
        }

        if (is_string($split_payments_raw)) {
            $decoded_split = json_decode($split_payments_raw, true);
            if (is_array($decoded_split)) {
                $split_payments_raw = $decoded_split;
            }
        }

        $split_payments = [];
        if (is_array($split_payments_raw) && !empty($split_payments_raw)) {
            foreach ($split_payments_raw as $payment_segment) {
                if (!is_array($payment_segment)) {
                    continue;
                }

                $segment_amount = isset($payment_segment['amount']) ? (float) $payment_segment['amount'] : 0;
                $segment_mode = isset($payment_segment['mode']) && is_scalar($payment_segment['mode'])
                    ? (string) $payment_segment['mode']
                    : '1';

                if ($segment_amount <= 0) {
                    continue;
                }

                $split_payments[] = [
                    'mode'   => $segment_mode,
                    'amount' => $segment_amount,
                ];
            }
        }

        if (empty($split_payments)) {
            $split_payments = [['mode' => $this->input->post('payment_mode') ?: '1', 'amount' => $total_amount]];
        }

        $payments_total = 0.0;
        foreach ($split_payments as $payment_segment) {
            $payments_total += isset($payment_segment['amount']) ? (float) $payment_segment['amount'] : 0;
        }

        if ($payments_total <= 0) {
            $respond(['success' => false, 'message' => 'Invalid payment allocation.']);
            return;
        }

        if (abs($payments_total - $total_amount) > 0.05) {
            $fallback_mode = isset($split_payments[0]['mode']) ? (string) $split_payments[0]['mode'] : '1';
            $split_payments = [[
                'mode'   => $fallback_mode,
                'amount' => (float) number_format($total_amount, 2, '.', ''),
            ]];
        } else {
            $rounding_delta = (float) number_format($total_amount - $payments_total, 2, '.', '');
            if (!empty($split_payments)) {
                $last_index = count($split_payments) - 1;
                $split_payments[$last_index]['amount'] = (float) number_format(
                    ((float) $split_payments[$last_index]['amount']) + $rounding_delta,
                    2,
                    '.',
                    ''
                );
            }
        }

        try {
            $this->load->model('invoices_model');

            $client = $this->db
                ->where('userid', $walk_in_customer_id)
                ->get($this->db->dbprefix('clients'))
                ->row_array();

            $invoice_total = (float) number_format($total_amount, 2, '.', '');
            $invoice_currency = !empty($client['default_currency']) ? (int) $client['default_currency'] : (int) get_base_currency()->id;

            $invoice_data = [
                'clientid'         => $walk_in_customer_id,
                'number'           => get_option('next_invoice_number'),
                'date'             => date('Y-m-d'),
                'duedate'          => date('Y-m-d'),
                'status'           => 1,
                'currency'         => $invoice_currency,
                'subtotal'         => $invoice_total,
                'total'            => $invoice_total,
                'total_tax'        => 0,
                'discount_percent' => 0,
                'discount_total'   => 0,
                'discount_type'    => '',
                'adjustment'       => 0,
                'adminnote'        => 'POS Sale',
                'billing_street'   => isset($client['billing_street']) ? $client['billing_street'] : '',
                'billing_city'     => isset($client['billing_city']) ? $client['billing_city'] : '',
                'billing_state'    => isset($client['billing_state']) ? $client['billing_state'] : '',
                'billing_zip'      => isset($client['billing_zip']) ? $client['billing_zip'] : '',
                'billing_country'  => isset($client['billing_country']) ? $client['billing_country'] : 0,
                'newitems'         => $newitems,
            ];

            $invoice_id = $this->invoices_model->add($invoice_data);

            if (!$invoice_id) {
                $respond(['success' => false, 'message' => 'Database constraint blocked invoice or payment validation.']);
                return;
            }

            // POS invoices are created from API payloads, so force header totals to match lines.
            $this->db->where('id', $invoice_id)->update($this->db->dbprefix('invoices'), [
                'subtotal' => $invoice_total,
                'total'    => $invoice_total,
            ]);

            $this->load->model('payments_model');
            $amount_tendered = $this->input->post('amount_tendered');
            $change_due = $this->input->post('change_due');
            $payment_saved = false;

            foreach ($split_payments as $payment_segment) {
                $segment_amount = isset($payment_segment['amount']) ? (float) $payment_segment['amount'] : 0;
                $segment_mode = isset($payment_segment['mode']) && is_scalar($payment_segment['mode'])
                    ? (string) $payment_segment['mode']
                    : '1';
                $segment_mode = preg_replace('/[^a-zA-Z0-9_-]/', '', $segment_mode);

                if ($segment_mode === '') {
                    $segment_mode = '1';
                }

                if ($segment_amount <= 0) {
                    continue;
                }

                $payment_id = $this->payments_model->add([
                    'invoiceid'   => $invoice_id,
                    'amount'      => $segment_amount,
                    'paymentmode' => $segment_mode,
                    'date'        => date('Y-m-d'),
                    'note'        => 'POS Cash Tendered: ' . $amount_tendered . ' | Change: ' . $change_due,
                ]);

                if (!$payment_id) {
                    $respond(['success' => false, 'message' => 'Database constraint blocked invoice or payment validation.']);
                    return;
                }

                $payment_saved = true;
            }

            if (!$payment_saved) {
                $respond(['success' => false, 'message' => 'Database constraint blocked invoice or payment validation.']);
                return;
            }

            if ($eligible_for_rewards && ($points_earned > 0 || $points_redeemed > 0)) {
                if ($points_redeemed > 0) {
                    $points_redeemed = (int) $this->consume_points_fifo($walk_in_customer_id, $points_redeemed);
                }

                $updated_points_balance = max(0, $current_points_balance - $points_redeemed + $points_earned);
                $this->pos_model->set_customer_points_balance($walk_in_customer_id, $updated_points_balance);

                $expires_at = null;
                if ($points_earned > 0 && !empty($reward_settings['expiry_enabled']) && (int) $reward_settings['expiry_enabled'] === 1) {
                    $expiry_days = isset($reward_settings['expiry_days']) ? (int) $reward_settings['expiry_days'] : 0;
                    if ($expiry_days > 0) {
                        $expires_at = date('Y-m-d H:i:s', strtotime('+' . $expiry_days . ' days'));
                    }
                }

                $this->pos_model->add_reward_transaction([
                    'client_id'            => (int) $walk_in_customer_id,
                    'invoice_id'           => (int) $invoice_id,
                    'staff_id'             => (int) get_staff_user_id(),
                    'points_earned'        => (int) $points_earned,
                    'points_redeemed'      => (int) $points_redeemed,
                    'points_balance_after' => (int) $updated_points_balance,
                    'points_available'     => (int) max(0, $points_earned),
                    'expires_at'           => $expires_at,
                    'note'                 => 'POS reward transaction',
                    'created_at'           => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception $e) {
            $respond(['success' => false, 'message' => 'Database constraint blocked invoice or payment validation.']);
            return;
        }

        $respond([
            'success'              => true,
            'invoice_id'           => $invoice_id,
            'points_earned'        => (int) $points_earned,
            'points_redeemed'      => (int) $points_redeemed,
            'points_balance_after' => (int) $updated_points_balance,
            'reward_redemption'    => (float) $reward_redemption_amount,
        ]);
    }

    public function repair_invoice_totals()
    {
        header('Content-Type: application/json');

        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Only administrators can run this repair.']);
            return;
        }

        $invoice_id = (int) $this->input->post('invoice_id');

        $this->db->select('id, total_tax, adjustment, discount_total');
        $this->db->from($this->db->dbprefix('invoices'));
        if ($invoice_id > 0) {
            $this->db->where('id', $invoice_id);
        } else {
            $this->db->where('total', 0);
        }

        $invoices = $this->db->get()->result_array();
        if (empty($invoices)) {
            echo json_encode(['success' => true, 'message' => 'No invoices needed repair.', 'updated' => 0]);
            return;
        }

        $updated = 0;
        foreach ($invoices as $invoice) {
            $sum_row = $this->db
                ->select('SUM(qty * rate) AS subtotal_amount', false)
                ->where('rel_id', (int) $invoice['id'])
                ->where('rel_type', 'invoice')
                ->get($this->db->dbprefix('itemable'))
                ->row_array();

            $subtotal = isset($sum_row['subtotal_amount']) ? (float) $sum_row['subtotal_amount'] : 0;
            if ($subtotal <= 0) {
                continue;
            }

            $total_tax = isset($invoice['total_tax']) ? (float) $invoice['total_tax'] : 0;
            $adjustment = isset($invoice['adjustment']) ? (float) $invoice['adjustment'] : 0;
            $discount_total = isset($invoice['discount_total']) ? (float) $invoice['discount_total'] : 0;
            $total = ($subtotal + $total_tax + $adjustment) - $discount_total;

            $this->db->where('id', (int) $invoice['id'])->update($this->db->dbprefix('invoices'), [
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'total'    => number_format($total, 2, '.', ''),
            ]);

            update_invoice_status((int) $invoice['id']);
            $updated++;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Invoice totals repair completed.',
            'updated' => $updated,
        ]);
    }
}
