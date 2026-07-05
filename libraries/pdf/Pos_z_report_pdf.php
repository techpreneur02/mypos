<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once APPPATH . 'libraries/pdf/App_pdf.php';

class Pos_z_report_pdf extends App_pdf
{
    protected $report;
    protected $report_date;
    protected $staff_label;
    protected $reward_summary;

    public function __construct($report, $report_date, $staff_label = 'All Cashiers', $reward_summary = [])
    {
        parent::__construct();

        $this->report = is_array($report) ? $report : [];
        $this->report_date = $report_date;
        $this->staff_label = $staff_label;
        $this->reward_summary = is_array($reward_summary) ? $reward_summary : [];

        $this->SetTitle('POS Z Report - ' . $this->report_date);
    }

    public function prepare()
    {
        $summary = isset($this->report['summary']) && is_array($this->report['summary'])
            ? $this->report['summary']
            : ['invoices_count' => 0, 'gross_sales' => 0, 'discounts' => 0, 'net_sales' => 0, 'payments_total' => 0];

        $payment_modes = isset($this->report['payment_modes']) && is_array($this->report['payment_modes'])
            ? $this->report['payment_modes']
            : [];

        $cashiers = isset($this->report['cashiers']) && is_array($this->report['cashiers'])
            ? $this->report['cashiers']
            : [];

        $this->set_view_vars([
            'report_date'   => $this->report_date,
            'staff_label'   => $this->staff_label,
            'summary'       => $summary,
            'payment_modes' => $payment_modes,
            'cashiers'      => $cashiers,
            'reward_summary' => $this->reward_summary,
        ]);

        return $this->build();
    }

    protected function type()
    {
        // Reuse existing PDF format option key to avoid adding new settings.
        return 'statement';
    }

    protected function file_path()
    {
        return module_dir_path('pos', 'views/z_report_pdf.php');
    }
}
