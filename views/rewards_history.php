<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$from_date = isset($from_date) ? $from_date : date('Y-m-d', strtotime('-30 days'));
$to_date = isset($to_date) ? $to_date : date('Y-m-d');
$selected_staff_id = isset($selected_staff_id) ? (int) $selected_staff_id : 0;
$selected_client_id = isset($selected_client_id) ? (int) $selected_client_id : 0;
$staff_options = isset($staff_options) && is_array($staff_options) ? $staff_options : [];
$reward_customers = isset($reward_customers) && is_array($reward_customers) ? $reward_customers : [];
$rows = isset($rows) && is_array($rows) ? $rows : [];
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">POS Rewards History</h4>
                        <hr class="hr-panel-separator" />

                        <form method="get" action="<?php echo admin_url('pos/pos_controller/rewards_history'); ?>" class="row" style="margin-bottom:15px;">
                            <div class="col-md-2">
                                <label>From</label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo html_escape($from_date); ?>">
                            </div>
                            <div class="col-md-2">
                                <label>To</label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo html_escape($to_date); ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Staff</label>
                                <select name="staff_id" class="form-control">
                                    <option value="0">All Staff</option>
                                    <?php foreach ($staff_options as $staff) { ?>
                                        <option value="<?php echo (int) $staff['staffid']; ?>" <?php echo ((int) $selected_staff_id === (int) $staff['staffid']) ? 'selected' : ''; ?>>
                                            <?php echo html_escape($staff['staff_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Customer</label>
                                <select name="client_id" class="form-control">
                                    <option value="0">All Customers</option>
                                    <?php foreach ($reward_customers as $customer) { ?>
                                        <option value="<?php echo (int) $customer['userid']; ?>" <?php echo ((int) $selected_client_id === (int) $customer['userid']) ? 'selected' : ''; ?>>
                                            <?php echo html_escape($customer['company']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2" style="padding-top:25px;">
                                <button type="submit" class="btn btn-primary btn-block">Apply</button>
                            </div>
                        </form>

                        <?php
                        $export_url = admin_url('pos/pos_controller/rewards_history_csv?from_date=' . urlencode($from_date) . '&to_date=' . urlencode($to_date) . '&staff_id=' . (int) $selected_staff_id . '&client_id=' . (int) $selected_client_id);
                        ?>
                        <div class="mbot15 text-right">
                            <a href="<?php echo $export_url; ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-download"></i> Export CSV
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Invoice</th>
                                        <th>Staff</th>
                                        <th class="text-right">Earned</th>
                                        <th class="text-right">Redeemed</th>
                                        <th class="text-right">Available</th>
                                        <th class="text-right">Balance After</th>
                                        <th>Expires</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)) { ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">No reward transactions found for selected filters.</td>
                                        </tr>
                                    <?php } ?>
                                    <?php foreach ($rows as $row) { ?>
                                        <tr>
                                            <td><?php echo html_escape(_dt($row['created_at'])); ?></td>
                                            <td><?php echo html_escape($row['customer_name']); ?></td>
                                            <td>
                                                <?php if (!empty($row['invoice_id'])) { ?>
                                                    <a href="<?php echo admin_url('invoices/list_invoices/' . (int) $row['invoice_id']); ?>" target="_blank">
                                                        #<?php echo (int) $row['invoice_id']; ?>
                                                    </a>
                                                <?php } else { ?>
                                                    -
                                                <?php } ?>
                                            </td>
                                            <td><?php echo html_escape(trim((string) $row['staff_name']) !== '' ? $row['staff_name'] : 'Unknown'); ?></td>
                                            <td class="text-right"><?php echo (int) $row['points_earned']; ?></td>
                                            <td class="text-right"><?php echo (int) $row['points_redeemed']; ?></td>
                                            <td class="text-right"><?php echo (int) $row['points_available']; ?></td>
                                            <td class="text-right"><?php echo (int) $row['points_balance_after']; ?></td>
                                            <td><?php echo !empty($row['expires_at']) ? html_escape(_dt($row['expires_at'])) : '-'; ?></td>
                                            <td><?php echo html_escape($row['note']); ?></td>
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