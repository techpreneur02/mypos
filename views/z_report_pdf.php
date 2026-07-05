<?php

defined('BASEPATH') or exit('No direct script access allowed');

$pdf = isset($pdf) ? $pdf : null;
$font_name = isset($font_name) ? $font_name : 'freesans';
$font_size = isset($font_size) ? (int) $font_size : 10;
$report_date = isset($report_date) ? $report_date : date('Y-m-d');
$staff_label = isset($staff_label) ? $staff_label : 'All Cashiers';
$summary = isset($summary) && is_array($summary) ? $summary : [];
$payment_modes = isset($payment_modes) && is_array($payment_modes) ? $payment_modes : [];
$cashiers = isset($cashiers) && is_array($cashiers) ? $cashiers : [];
$reward_summary = isset($reward_summary) && is_array($reward_summary) ? $reward_summary : [];

$summary_invoices = isset($summary['invoices_count']) ? (int) $summary['invoices_count'] : 0;
$summary_gross = isset($summary['gross_sales']) ? (float) $summary['gross_sales'] : 0;
$summary_discounts = isset($summary['discounts']) ? (float) $summary['discounts'] : 0;
$summary_net = isset($summary['net_sales']) ? (float) $summary['net_sales'] : 0;
$summary_payments = isset($summary['payments_total']) ? (float) $summary['payments_total'] : 0;
$points_earned = isset($reward_summary['points_earned']) ? (int) $reward_summary['points_earned'] : 0;
$points_redeemed = isset($reward_summary['points_redeemed']) ? (int) $reward_summary['points_redeemed'] : 0;
$reward_tx_count = isset($reward_summary['tx_count']) ? (int) $reward_summary['tx_count'] : 0;

$variance = $summary_payments - $summary_net;

$html = '<h2 style="margin:0 0 8px 0;">POS Z Report</h2>';
$html .= '<table cellpadding="6" cellspacing="0" border="1" style="width:100%; border-collapse:collapse; font-size:10px;">';
$html .= '<tr><td><strong>Report Date</strong></td><td>' . html_escape($report_date) . '</td><td><strong>Cashier</strong></td><td>' . html_escape($staff_label) . '</td></tr>';
$html .= '</table>';

$html .= '<br><h4 style="margin:0 0 6px 0;">Rewards Summary</h4>';
$html .= '<table cellpadding="6" cellspacing="0" border="1" style="width:100%; border-collapse:collapse; font-size:10px;">';
$html .= '<tr style="background-color:#f3f4f6;">'
    . '<th><strong>Points Earned</strong></th>'
    . '<th><strong>Points Redeemed</strong></th>'
    . '<th><strong>Transactions</strong></th>'
    . '</tr>';
$html .= '<tr>'
    . '<td align="right">' . $points_earned . '</td>'
    . '<td align="right">' . $points_redeemed . '</td>'
    . '<td align="right">' . $reward_tx_count . '</td>'
    . '</tr>';
$html .= '</table>';

$html .= '<br><table cellpadding="6" cellspacing="0" border="1" style="width:100%; border-collapse:collapse; font-size:10px;">';
$html .= '<tr style="background-color:#f3f4f6;">'
    . '<th><strong>Invoices</strong></th>'
    . '<th><strong>Gross Sales</strong></th>'
    . '<th><strong>Discounts</strong></th>'
    . '<th><strong>Net Sales</strong></th>'
    . '<th><strong>Total Payments</strong></th>'
    . '<th><strong>Variance</strong></th>'
    . '</tr>';
$html .= '<tr>'
    . '<td align="right">' . $summary_invoices . '</td>'
    . '<td align="right">' . app_format_money($summary_gross, get_base_currency()) . '</td>'
    . '<td align="right">' . app_format_money($summary_discounts, get_base_currency()) . '</td>'
    . '<td align="right">' . app_format_money($summary_net, get_base_currency()) . '</td>'
    . '<td align="right">' . app_format_money($summary_payments, get_base_currency()) . '</td>'
    . '<td align="right">' . app_format_money($variance, get_base_currency()) . '</td>'
    . '</tr>';
$html .= '</table>';

$html .= '<br><h4 style="margin:0 0 6px 0;">Payment Mode Breakdown</h4>';
$html .= '<table cellpadding="6" cellspacing="0" border="1" style="width:100%; border-collapse:collapse; font-size:10px;">';
$html .= '<tr style="background-color:#f3f4f6;"><th><strong>Payment Mode</strong></th><th><strong>Transactions</strong></th><th><strong>Amount</strong></th></tr>';
if (empty($payment_modes)) {
    $html .= '<tr><td colspan="3" align="center">No payment records.</td></tr>';
} else {
    foreach ($payment_modes as $row) {
        $html .= '<tr>'
            . '<td>' . html_escape($row['mode_name']) . '</td>'
            . '<td align="right">' . (int) $row['tx_count'] . '</td>'
            . '<td align="right">' . app_format_money($row['total_amount'], get_base_currency()) . '</td>'
            . '</tr>';
    }
}
$html .= '</table>';

$html .= '<br><h4 style="margin:0 0 6px 0;">Cashier Breakdown</h4>';
$html .= '<table cellpadding="6" cellspacing="0" border="1" style="width:100%; border-collapse:collapse; font-size:10px;">';
$html .= '<tr style="background-color:#f3f4f6;"><th><strong>Cashier</strong></th><th><strong>Invoices</strong></th><th><strong>Payments</strong></th></tr>';
if (empty($cashiers)) {
    $html .= '<tr><td colspan="3" align="center">No cashier records.</td></tr>';
} else {
    foreach ($cashiers as $row) {
        $cashier_name = trim($row['cashier_name']) !== '' ? $row['cashier_name'] : 'Unknown';
        $html .= '<tr>'
            . '<td>' . html_escape($cashier_name) . '</td>'
            . '<td align="right">' . (int) $row['invoices_count'] . '</td>'
            . '<td align="right">' . app_format_money($row['payments_total'], get_base_currency()) . '</td>'
            . '</tr>';
    }
}
$html .= '</table>';

if ($pdf) {
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->writeHTML($html, true, false, true, false, '');
}
