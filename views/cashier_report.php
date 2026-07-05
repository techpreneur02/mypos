<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$from_date = isset($from_date) ? $from_date : date('Y-m-d', strtotime('-30 days'));
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$can_view_all = isset($can_view_all) ? (bool) $can_view_all : false;
$staff_options = isset($staff_options) && is_array($staff_options) ? $staff_options : [];
$selected_staff_id = isset($selected_staff_id) ? (int) $selected_staff_id : 0;
$rows = isset($rows) && is_array($rows) ? $rows : [];
$reward_summary = isset($reward_summary) && is_array($reward_summary) ? $reward_summary : ['points_earned' => 0, 'points_redeemed' => 0, 'tx_count' => 0];
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">POS Cashier Report</h4>
                        <hr class="hr-panel-separator" />

                        <form method="get" action="<?php echo admin_url('pos/pos_controller/cashier_report'); ?>" class="row" style="margin-bottom:15px;">
                            <div class="col-md-3">
                                <label>From</label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo html_escape($from_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label>To</label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo html_escape($to_date); ?>">
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-2" style="padding-top:25px;">
                                <button type="submit" class="btn btn-primary btn-block">Apply</button>
                            </div>
                        </form>

                        <?php
                        $cashier_export_url = admin_url('pos/pos_controller/cashier_report_csv?from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&staff_id=' . (int) $selected_staff_id);
                        ?>
                        <div class="mbot15 text-right">
                            <a href="<?php echo $cashier_export_url; ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-download"></i> Export CSV
                            </a>
                        </div>

                        <?php
                        $grand_total = 0;
                        $grand_paid = 0;
                        $grand_discount = 0;
                        foreach ($rows as $r) {
                            $grand_total += (float) $r['total'];
                            $grand_paid += (float) $r['paid_total'];
                            $grand_discount += (float) $r['discount_total'];
                        }
                        ?>

                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-md-2"><strong>Invoices:</strong> <?php echo count($rows); ?></div>
                            <div class="col-md-2"><strong>Sales Total:</strong> <?php echo app_format_money($grand_total, get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Paid Total:</strong> <?php echo app_format_money($grand_paid, get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Discounts:</strong> <?php echo app_format_money($grand_discount, get_base_currency()); ?></div>
                            <div class="col-md-2"><strong>Points Earned:</strong> <?php echo (int) $reward_summary['points_earned']; ?></div>
                            <div class="col-md-2"><strong>Points Redeemed:</strong> <?php echo (int) $reward_summary['points_redeemed']; ?></div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Cashier</th>
                                        <th>Customer</th>
                                        <th>Payment Modes</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Paid</th>
                                        <th class="text-right">Open</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)) { ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No POS records found for selected filters.</td>
                                        </tr>
                                    <?php } ?>
                                    <?php foreach ($rows as $row) {
                                        $invoice_label = format_invoice_number($row['id']);
                                        $open_amount = (float) $row['total'] - (float) $row['paid_total'];
                                    ?>
                                        <tr>
                                            <td><a href="<?php echo admin_url('invoices/list_invoices/' . $row['id']); ?>" target="_blank"><?php echo html_escape($invoice_label); ?></a></td>
                                            <td><?php echo html_escape(_d($row['date'])); ?></td>
                                            <td><?php echo html_escape(trim($row['cashier_name']) !== '' ? $row['cashier_name'] : 'Unknown'); ?></td>
                                            <td><?php echo html_escape($row['customer_name']); ?></td>
                                            <td>
                                                <?php if (!empty($row['payment_modes'])) {
                                                    $parts = [];
                                                    foreach ($row['payment_modes'] as $pm) {
                                                        $parts[] = $pm['name'] . ': ' . app_format_money($pm['amount'], get_base_currency());
                                                    }
                                                    echo html_escape(implode(' | ', $parts));
                                                } else {
                                                    echo '-';
                                                } ?>
                                            </td>
                                            <td class="text-right"><?php echo app_format_money($row['discount_total'], get_base_currency()); ?></td>
                                            <td class="text-right"><?php echo app_format_money($row['total'], get_base_currency()); ?></td>
                                            <td class="text-right"><?php echo app_format_money($row['paid_total'], get_base_currency()); ?></td>
                                            <td class="text-right"><?php echo app_format_money($open_amount, get_base_currency()); ?></td>
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
<?php init_tail(); ?>