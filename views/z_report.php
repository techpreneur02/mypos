<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$report_date = isset($report_date) ? $report_date : date('Y-m-d');
$can_view_all = isset($can_view_all) ? (bool) $can_view_all : false;
$staff_options = isset($staff_options) && is_array($staff_options) ? $staff_options : [];
$selected_staff_id = isset($selected_staff_id) ? (int) $selected_staff_id : 0;
$report = isset($report) && is_array($report) ? $report : ['summary' => ['invoices_count' => 0, 'gross_sales' => 0, 'discounts' => 0, 'net_sales' => 0, 'payments_total' => 0], 'payment_modes' => [], 'cashiers' => []];
$reward_summary = isset($reward_summary) && is_array($reward_summary) ? $reward_summary : ['points_earned' => 0, 'points_redeemed' => 0, 'tx_count' => 0];
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">POS Z Report</h4>
                        <hr class="hr-panel-separator" />

                        <form method="get" action="<?php echo admin_url('pos/pos_controller/z_report'); ?>" class="row" style="margin-bottom:15px;">
                            <div class="col-md-4">
                                <label>Report Date</label>
                                <input type="date" name="report_date" class="form-control" value="<?php echo html_escape($report_date); ?>">
                            </div>
                            <div class="col-md-5">
                                <label>Cashier</label>
                                <select name="staff_id" class="form-control" <?php echo !$can_view_all ? 'disabled' : ''; ?>>
                                    <option value="0">All Cashiers</option>
                                    <?php foreach ($staff_options as $staff) { ?>
                                        <option value="<?php echo (int) $staff['staffid']; ?>" <?php echo ((int) $selected_staff_id === (int) $staff['staffid']) ? 'selected' : ''; ?>>
                                            <?php echo html_escape($staff['staff_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <?php if (!$can_view_all) { ?>
                                    <input type="hidden" name="staff_id" value="<?php echo (int) $selected_staff_id; ?>">
                                <?php } ?>
                            </div>
                            <div class="col-md-3" style="padding-top:25px;">
                                <button type="submit" class="btn btn-primary btn-block">Generate</button>
                            </div>
                        </form>

                        <?php
                        $z_export_url = admin_url('pos/pos_controller/z_report_csv?report_date=' . urlencode($report_date) . '&staff_id=' . (int) $selected_staff_id);
                        $z_pdf_url = admin_url('pos/pos_controller/z_report_pdf?report_date=' . urlencode($report_date) . '&staff_id=' . (int) $selected_staff_id);
                        ?>
                        <div class="mbot15 text-right">
                            <a href="<?php echo $z_pdf_url; ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-file-pdf-o"></i> Export PDF
                            </a>
                            <a href="<?php echo $z_export_url; ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-download"></i> Export CSV
                            </a>
                        </div>

                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-2"><strong>Invoices:</strong><br><?php echo (int) $report['summary']['invoices_count']; ?></div>
                            <div class="col-md-2"><strong>Gross Sales:</strong><br><?php echo app_format_money($report['summary']['gross_sales'], get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Discounts:</strong><br><?php echo app_format_money($report['summary']['discounts'], get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Net Sales:</strong><br><?php echo app_format_money($report['summary']['net_sales'], get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Total Payments:</strong><br><?php echo app_format_money($report['summary']['payments_total'], get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Variance:</strong><br><?php echo app_format_money($report['summary']['payments_total'] - $report['summary']['net_sales'], get_base_currency()); ?></div>
                        </div>

                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-4"><strong>Points Earned:</strong> <?php echo (int) $reward_summary['points_earned']; ?></div>
                            <div class="col-md-4"><strong>Points Redeemed:</strong> <?php echo (int) $reward_summary['points_redeemed']; ?></div>
                            <div class="col-md-4"><strong>Reward Transactions:</strong> <?php echo (int) $reward_summary['tx_count']; ?></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Payment Mode Breakdown</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Payment Mode</th>
                                                <th class="text-right">Transactions</th>
                                                <th class="text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($report['payment_modes'])) { ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No payment records.</td>
                                                </tr>
                                            <?php } ?>
                                            <?php foreach ($report['payment_modes'] as $row) { ?>
                                                <tr>
                                                    <td><?php echo html_escape($row['mode_name']); ?></td>
                                                    <td class="text-right"><?php echo (int) $row['tx_count']; ?></td>
                                                    <td class="text-right"><?php echo app_format_money($row['total_amount'], get_base_currency()); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Cashier Breakdown</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Cashier</th>
                                                <th class="text-right">Invoices</th>
                                                <th class="text-right">Payments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($report['cashiers'])) { ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No cashier records.</td>
                                                </tr>
                                            <?php } ?>
                                            <?php foreach ($report['cashiers'] as $row) { ?>
                                                <tr>
                                                    <td><?php echo html_escape(trim($row['cashier_name']) !== '' ? $row['cashier_name'] : 'Unknown'); ?></td>
                                                    <td class="text-right"><?php echo (int) $row['invoices_count']; ?></td>
                                                    <td class="text-right"><?php echo app_format_money($row['payments_total'], get_base_currency()); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>