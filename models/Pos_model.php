<?php

defined("BASEPATH") or exit("No direct script access allowed");

class Pos_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_pos_products()
    {
        $previous_db_debug = $this->db->db_debug;
        $this->db->db_debug = false;

        $table = null;

        if ($this->db->table_exists($this->db->dbprefix('items'))) {
            $table = $this->db->dbprefix('items');
        } elseif ($this->db->table_exists('tblitems')) {
            $table = 'tblitems';
        }

        if (!$table) {
            $this->db->db_debug = $previous_db_debug;
            return [];
        }

        $fields = $this->db->list_fields($table);

        if (empty($fields)) {
            $this->db->db_debug = $previous_db_debug;
            return [];
        }

        $id_field = in_array('id', $fields, true) ? 'id' : 'itemid';
        $name_field = in_array('name', $fields, true) ? 'name' : 'description';
        $description_field = in_array('long_description', $fields, true)
            ? 'long_description'
            : (in_array('description', $fields, true) ? 'description' : null);

        $select_parts = [
            $id_field . ' as id',
            $name_field . ' as name',
            ($description_field ? $description_field : "''") . ' as description',
            'rate',
            (in_array('barcode', $fields, true) ? 'barcode' : "''") . ' as barcode',
            (in_array('pos_image', $fields, true) ? 'pos_image' : "''") . ' as pos_image',
            (in_array('reward_type', $fields, true) ? 'reward_type' : "'product'") . ' as reward_type',
            (in_array('reward_points_mode', $fields, true) ? 'reward_points_mode' : "'type_rate'") . ' as reward_points_mode',
            (in_array('reward_points_value', $fields, true) ? 'reward_points_value' : '0') . ' as reward_points_value',
        ];

        $this->db->select(implode(', ', $select_parts), false);
        $this->db->where('rate >', 0);

        $query = $this->db->get($table);
        $this->db->db_debug = $previous_db_debug;

        if (!$query) {
            return [];
        }

        return $query->result_array();
    }

    public function get_rewards_settings()
    {
        $row = $this->db->get($this->db->dbprefix('pos_reward_settings'))->row_array();

        if (!$row) {
            $defaults = [
                'enabled'                    => 1,
                'product_points_per_currency' => 1,
                'service_points_per_currency' => 0.5,
                'redeem_value_per_point'     => 0.01,
                'min_redeem_points'          => 100,
                'allow_walkin_rewards'       => 0,
                'expiry_enabled'             => 0,
                'expiry_days'                => 365,
                'created_at'                 => date('Y-m-d H:i:s'),
                'updated_at'                 => date('Y-m-d H:i:s'),
            ];
            $this->db->insert($this->db->dbprefix('pos_reward_settings'), $defaults);
            $row = $this->db->get($this->db->dbprefix('pos_reward_settings'))->row_array();
        }

        return $row;
    }

    public function update_rewards_settings($data)
    {
        $existing = $this->db->get($this->db->dbprefix('pos_reward_settings'))->row_array();
        if ($existing) {
            $this->db->where('id', (int) $existing['id'])->update($this->db->dbprefix('pos_reward_settings'), $data);
            return true;
        }

        return $this->db->insert($this->db->dbprefix('pos_reward_settings'), $data);
    }

    public function get_customer_points_balance($client_id)
    {
        $row = $this->db
            ->where('client_id', (int) $client_id)
            ->get($this->db->dbprefix('pos_reward_balances'))
            ->row_array();

        return $row ? (int) $row['points_balance'] : 0;
    }

    public function set_customer_points_balance($client_id, $points)
    {
        $existing = $this->db
            ->where('client_id', (int) $client_id)
            ->get($this->db->dbprefix('pos_reward_balances'))
            ->row_array();

        if ($existing) {
            $this->db->where('client_id', (int) $client_id)->update($this->db->dbprefix('pos_reward_balances'), [
                'points_balance' => (int) $points,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            return true;
        }

        return $this->db->insert($this->db->dbprefix('pos_reward_balances'), [
            'client_id'      => (int) $client_id,
            'points_balance' => (int) $points,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function add_reward_transaction($data)
    {
        return $this->db->insert($this->db->dbprefix('pos_reward_transactions'), $data);
    }

    public function get_reward_transactions($client_id = 0, $limit = 200)
    {
        $transactions_table = $this->db->dbprefix('pos_reward_transactions');
        $clients_table = $this->db->dbprefix('clients');
        $staff_table = $this->db->dbprefix('staff');

        $this->db->select(
            $transactions_table . '.id,
            ' . $transactions_table . '.client_id,
            ' . $transactions_table . '.invoice_id,
            ' . $transactions_table . '.points_earned,
            ' . $transactions_table . '.points_redeemed,
            ' . $transactions_table . '.points_balance_after,
            ' . $transactions_table . '.points_available,
            ' . $transactions_table . '.note,
            ' . $transactions_table . '.created_at,
            ' . $transactions_table . '.expires_at,
            COALESCE(' . $clients_table . '.company, "Unknown") AS customer_name,
            CONCAT(' . $staff_table . '.firstname, " ", ' . $staff_table . '.lastname) AS staff_name',
            false
        );
        $this->db->from($transactions_table);
        $this->db->join($clients_table, $clients_table . '.userid = ' . $transactions_table . '.client_id', 'left');
        $this->db->join($staff_table, $staff_table . '.staffid = ' . $transactions_table . '.staff_id', 'left');

        if ((int) $client_id > 0) {
            $this->db->where($transactions_table . '.client_id', (int) $client_id);
        }

        $this->db->order_by($transactions_table . '.id', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    public function get_reward_summary_for_period($from_date, $to_date, $staff_id = 0)
    {
        $transactions_table = $this->db->dbprefix('pos_reward_transactions');
        $invoices_table = $this->db->dbprefix('invoices');

        $this->db->select(
            'SUM(points_earned) AS points_earned,
             SUM(points_redeemed) AS points_redeemed,
             COUNT(*) AS tx_count',
            false
        );
        $this->db->from($transactions_table);
        $this->db->join($invoices_table, $invoices_table . '.id = ' . $transactions_table . '.invoice_id', 'left');
        $this->db->where('DATE(' . $transactions_table . '.created_at) >=', $from_date);
        $this->db->where('DATE(' . $transactions_table . '.created_at) <=', $to_date);

        if ((int) $staff_id > 0) {
            $this->db->where($invoices_table . '.addedfrom', (int) $staff_id);
        }

        $row = $this->db->get()->row_array();
        if (!$row) {
            return [
                'points_earned' => 0,
                'points_redeemed' => 0,
                'tx_count' => 0,
            ];
        }

        return [
            'points_earned' => (int) ($row['points_earned'] ?? 0),
            'points_redeemed' => (int) ($row['points_redeemed'] ?? 0),
            'tx_count' => (int) ($row['tx_count'] ?? 0),
        ];
    }

    public function get_reward_summary_for_day($report_date, $staff_id = 0)
    {
        return $this->get_reward_summary_for_period($report_date, $report_date, $staff_id);
    }

    public function get_reward_history_staff_list($from_date = '', $to_date = '')
    {
        $transactions_table = $this->db->dbprefix('pos_reward_transactions');
        $staff_table = $this->db->dbprefix('staff');

        $this->db->select(
            'DISTINCT ' . $staff_table . '.staffid, CONCAT(' . $staff_table . '.firstname, " ", ' . $staff_table . '.lastname) AS staff_name',
            false
        );
        $this->db->from($transactions_table);
        $this->db->join($staff_table, $staff_table . '.staffid = ' . $transactions_table . '.staff_id', 'left');

        if ($from_date !== '') {
            $this->db->where('DATE(' . $transactions_table . '.created_at) >=', $from_date);
        }
        if ($to_date !== '') {
            $this->db->where('DATE(' . $transactions_table . '.created_at) <=', $to_date);
        }

        $this->db->order_by('staff_name', 'asc');
        return $this->db->get()->result_array();
    }

    public function get_reward_history_rows($from_date, $to_date, $staff_id = 0, $client_id = 0, $limit = 1000)
    {
        $transactions_table = $this->db->dbprefix('pos_reward_transactions');
        $clients_table = $this->db->dbprefix('clients');
        $staff_table = $this->db->dbprefix('staff');

        $this->db->select(
            $transactions_table . '.id,
            ' . $transactions_table . '.client_id,
            ' . $transactions_table . '.invoice_id,
            ' . $transactions_table . '.staff_id,
            ' . $transactions_table . '.points_earned,
            ' . $transactions_table . '.points_redeemed,
            ' . $transactions_table . '.points_available,
            ' . $transactions_table . '.points_balance_after,
            ' . $transactions_table . '.note,
            ' . $transactions_table . '.created_at,
            ' . $transactions_table . '.expires_at,
            COALESCE(' . $clients_table . '.company, "Unknown") AS customer_name,
            CONCAT(' . $staff_table . '.firstname, " ", ' . $staff_table . '.lastname) AS staff_name',
            false
        );
        $this->db->from($transactions_table);
        $this->db->join($clients_table, $clients_table . '.userid = ' . $transactions_table . '.client_id', 'left');
        $this->db->join($staff_table, $staff_table . '.staffid = ' . $transactions_table . '.staff_id', 'left');

        $this->db->where('DATE(' . $transactions_table . '.created_at) >=', $from_date);
        $this->db->where('DATE(' . $transactions_table . '.created_at) <=', $to_date);

        if ((int) $staff_id > 0) {
            $this->db->where($transactions_table . '.staff_id', (int) $staff_id);
        }
        if ((int) $client_id > 0) {
            $this->db->where($transactions_table . '.client_id', (int) $client_id);
        }

        $this->db->order_by($transactions_table . '.id', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    public function get_pos_staff_list($from_date = '', $to_date = '')
    {
        $invoices_table = $this->db->dbprefix('invoices');
        $payments_table = $this->db->dbprefix('invoicepaymentrecords');
        $staff_table = $this->db->dbprefix('staff');

        $this->db->select('DISTINCT ' . $staff_table . '.staffid, CONCAT(' . $staff_table . '.firstname, " ", ' . $staff_table . '.lastname) AS staff_name', false);
        $this->db->from($invoices_table);
        $this->db->join($payments_table, $payments_table . '.invoiceid = ' . $invoices_table . '.id AND ' . $payments_table . '.note LIKE "POS Cash Tendered:%"', 'inner');
        $this->db->join($staff_table, $staff_table . '.staffid = ' . $invoices_table . '.addedfrom', 'left');

        if ($from_date !== '') {
            $this->db->where($invoices_table . '.date >=', $from_date);
        }
        if ($to_date !== '') {
            $this->db->where($invoices_table . '.date <=', $to_date);
        }

        $this->db->order_by('staff_name', 'asc');
        return $this->db->get()->result_array();
    }

    public function get_cashier_report_rows($from_date, $to_date, $staff_id = 0)
    {
        $invoices_table = $this->db->dbprefix('invoices');
        $payments_table = $this->db->dbprefix('invoicepaymentrecords');
        $staff_table = $this->db->dbprefix('staff');
        $clients_table = $this->db->dbprefix('clients');
        $payment_modes_table = $this->db->dbprefix('payment_modes');

        $payments_agg_sql = '(SELECT invoiceid, SUM(amount) AS paid_total FROM ' . $payments_table . ' WHERE note LIKE "POS Cash Tendered:%" GROUP BY invoiceid) pospay';
        $discount_agg_sql = '(SELECT rel_id AS invoiceid, SUM(CASE WHEN description = "POS Discount" THEN ABS(qty * rate) ELSE 0 END) AS discount_total FROM ' . $this->db->dbprefix('itemable') . ' WHERE rel_type = "invoice" GROUP BY rel_id) posdisc';

        $this->db->select(
            $invoices_table . '.id,
            ' . $invoices_table . '.number,
            ' . $invoices_table . '.prefix,
            ' . $invoices_table . '.date,
            ' . $invoices_table . '.total,
            COALESCE(pospay.paid_total, 0) AS paid_total,
            COALESCE(posdisc.discount_total, 0) AS discount_total,
            CONCAT(' . $staff_table . '.firstname, " ", ' . $staff_table . '.lastname) AS cashier_name,
            COALESCE(' . $clients_table . '.company, "Walk-In Customer") AS customer_name',
            false
        );

        $this->db->from($invoices_table);
        $this->db->join($payments_agg_sql, 'pospay.invoiceid = ' . $invoices_table . '.id', 'inner', false);
        $this->db->join($staff_table, $staff_table . '.staffid = ' . $invoices_table . '.addedfrom', 'left');
        $this->db->join($clients_table, $clients_table . '.userid = ' . $invoices_table . '.clientid', 'left');
        $this->db->join($discount_agg_sql, 'posdisc.invoiceid = ' . $invoices_table . '.id', 'left', false);

        $this->db->where($invoices_table . '.date >=', $from_date);
        $this->db->where($invoices_table . '.date <=', $to_date);

        if ($staff_id > 0) {
            $this->db->where($invoices_table . '.addedfrom', (int) $staff_id);
        }

        $this->db->order_by($invoices_table . '.id', 'desc');
        $rows = $this->db->get()->result_array();

        if (empty($rows)) {
            return [];
        }

        $invoice_ids = array_column($rows, 'id');
        $mode_rows = [];

        if (!empty($invoice_ids)) {
            $this->db->select(
                $payments_table . '.invoiceid,
                COALESCE(' . $payment_modes_table . '.name, ' . $payments_table . '.paymentmode) AS mode_name,
                SUM(' . $payments_table . '.amount) AS mode_amount',
                false
            );
            $this->db->from($payments_table);
            $this->db->join($payment_modes_table, $payment_modes_table . '.id = ' . $payments_table . '.paymentmode', 'left');
            $this->db->where_in($payments_table . '.invoiceid', $invoice_ids);
            $this->db->where($payments_table . '.note LIKE', 'POS Cash Tendered:%');
            $this->db->group_by([$payments_table . '.invoiceid', $payments_table . '.paymentmode', $payment_modes_table . '.name']);
            $mode_rows = $this->db->get()->result_array();
        }

        $modes_by_invoice = [];
        foreach ($mode_rows as $mode_row) {
            $invoice_id = (int) $mode_row['invoiceid'];
            if (!isset($modes_by_invoice[$invoice_id])) {
                $modes_by_invoice[$invoice_id] = [];
            }
            $modes_by_invoice[$invoice_id][] = [
                'name' => $mode_row['mode_name'],
                'amount' => (float) $mode_row['mode_amount'],
            ];
        }

        foreach ($rows as &$row) {
            $row['payment_modes'] = isset($modes_by_invoice[(int) $row['id']]) ? $modes_by_invoice[(int) $row['id']] : [];
        }
        unset($row);

        return $rows;
    }

    public function get_z_report_data($report_date, $staff_id = 0)
    {
        $invoices_table = $this->db->dbprefix('invoices');
        $payments_table = $this->db->dbprefix('invoicepaymentrecords');
        $staff_table = $this->db->dbprefix('staff');
        $payment_modes_table = $this->db->dbprefix('payment_modes');
        $itemable_table = $this->db->dbprefix('itemable');

        $this->db->select($invoices_table . '.id,' . $invoices_table . '.addedfrom', false);
        $this->db->from($invoices_table);
        $this->db->join($payments_table, $payments_table . '.invoiceid = ' . $invoices_table . '.id AND ' . $payments_table . '.note LIKE "POS Cash Tendered:%"', 'inner');
        $this->db->where($invoices_table . '.date', $report_date);
        if ($staff_id > 0) {
            $this->db->where($invoices_table . '.addedfrom', (int) $staff_id);
        }
        $this->db->group_by($invoices_table . '.id');
        $pos_invoices = $this->db->get()->result_array();

        $invoice_ids = array_map('intval', array_column($pos_invoices, 'id'));

        if (empty($invoice_ids)) {
            return [
                'summary' => [
                    'invoices_count' => 0,
                    'gross_sales' => 0,
                    'discounts' => 0,
                    'net_sales' => 0,
                    'payments_total' => 0,
                ],
                'payment_modes' => [],
                'cashiers' => [],
            ];
        }

        $invoice_ids_sql = implode(',', $invoice_ids);

        $summary_sql = 'SELECT
                COUNT(*) AS invoices_count,
                SUM(CASE WHEN rate >= 0 THEN qty * rate ELSE 0 END) AS gross_sales,
                SUM(CASE WHEN description = "POS Discount" THEN ABS(qty * rate) ELSE 0 END) AS discounts,
                SUM(qty * rate) AS net_sales
            FROM ' . $itemable_table . '
            WHERE rel_type = "invoice" AND rel_id IN (' . $invoice_ids_sql . ')';

        $summary_row = $this->db->query($summary_sql)->row_array();

        $payments_summary_sql = 'SELECT SUM(amount) AS payments_total
            FROM ' . $payments_table . '
            WHERE invoiceid IN (' . $invoice_ids_sql . ') AND note LIKE "POS Cash Tendered:%"';
        $payments_summary_row = $this->db->query($payments_summary_sql)->row_array();

        $payment_modes_sql = 'SELECT
                COALESCE(pm.name, p.paymentmode) AS mode_name,
                COUNT(*) AS tx_count,
                SUM(p.amount) AS total_amount
            FROM ' . $payments_table . ' p
            LEFT JOIN ' . $payment_modes_table . ' pm ON pm.id = p.paymentmode
            WHERE p.invoiceid IN (' . $invoice_ids_sql . ') AND p.note LIKE "POS Cash Tendered:%"
            GROUP BY p.paymentmode, pm.name
            ORDER BY total_amount DESC';
        $payment_modes = $this->db->query($payment_modes_sql)->result_array();

        $cashiers_sql = 'SELECT
                s.staffid,
                CONCAT(s.firstname, " ", s.lastname) AS cashier_name,
                COUNT(DISTINCT i.id) AS invoices_count,
                SUM(p.amount) AS payments_total
            FROM ' . $invoices_table . ' i
            INNER JOIN ' . $payments_table . ' p ON p.invoiceid = i.id AND p.note LIKE "POS Cash Tendered:%"
            LEFT JOIN ' . $staff_table . ' s ON s.staffid = i.addedfrom
            WHERE i.id IN (' . $invoice_ids_sql . ')
            GROUP BY s.staffid, s.firstname, s.lastname
            ORDER BY payments_total DESC';
        $cashiers = $this->db->query($cashiers_sql)->result_array();

        return [
            'summary' => [
                'invoices_count' => (int) ($summary_row['invoices_count'] ?? 0),
                'gross_sales' => (float) ($summary_row['gross_sales'] ?? 0),
                'discounts' => (float) ($summary_row['discounts'] ?? 0),
                'net_sales' => (float) ($summary_row['net_sales'] ?? 0),
                'payments_total' => (float) ($payments_summary_row['payments_total'] ?? 0),
            ],
            'payment_modes' => $payment_modes,
            'cashiers' => $cashiers,
        ];
    }
}
