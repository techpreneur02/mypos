<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$settings = isset($settings) && is_array($settings) ? $settings : [];
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">POS Rewards Settings</h4>
                        <hr class="hr-panel-separator" />

                        <?php echo form_open(admin_url('pos/pos_controller/save_rewards_settings')); ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php echo !empty($settings['enabled']) ? 'checked' : ''; ?>>
                            <label for="enabled">Enable rewards and points system</label>
                        </div>

                        <div class="row mtop15">
                            <div class="col-md-6">
                                <label>Product Points Per $1</label>
                                <input type="number" step="0.0001" min="0" name="product_points_per_currency" class="form-control" value="<?php echo html_escape(isset($settings['product_points_per_currency']) ? $settings['product_points_per_currency'] : 1); ?>">
                                <p class="text-muted mtop5">Example: 1.5 means customer gets 1.5 points for each $1 spent on products.</p>
                            </div>
                            <div class="col-md-6">
                                <label>Service Points Per $1</label>
                                <input type="number" step="0.0001" min="0" name="service_points_per_currency" class="form-control" value="<?php echo html_escape(isset($settings['service_points_per_currency']) ? $settings['service_points_per_currency'] : 0.5); ?>">
                                <p class="text-muted mtop5">Used for items marked as Service in item setup.</p>
                            </div>
                        </div>

                        <div class="row mtop15">
                            <div class="col-md-6">
                                <label>Redeem Value Per Point ($)</label>
                                <input type="number" step="0.0001" min="0.0001" name="redeem_value_per_point" class="form-control" value="<?php echo html_escape(isset($settings['redeem_value_per_point']) ? $settings['redeem_value_per_point'] : 0.01); ?>">
                                <p class="text-muted mtop5">Example: 0.01 means 100 points = $1 redemption value.</p>
                            </div>
                            <div class="col-md-6">
                                <label>Minimum Points To Redeem</label>
                                <input type="number" step="1" min="0" name="min_redeem_points" class="form-control" value="<?php echo html_escape(isset($settings['min_redeem_points']) ? $settings['min_redeem_points'] : 100); ?>">
                            </div>
                        </div>

                        <div class="checkbox checkbox-primary mtop15">
                            <input type="checkbox" id="allow_walkin_rewards" name="allow_walkin_rewards" value="1" <?php echo !empty($settings['allow_walkin_rewards']) ? 'checked' : ''; ?>>
                            <label for="allow_walkin_rewards">Allow rewards for Walk-In Customer</label>
                        </div>

                        <hr class="hr-panel-separator" />
                        <h5 class="mbot10">Points Expiration</h5>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="expiry_enabled" name="expiry_enabled" value="1" <?php echo !empty($settings['expiry_enabled']) ? 'checked' : ''; ?>>
                            <label for="expiry_enabled">Enable automatic points expiration</label>
                        </div>
                        <div class="row mtop10">
                            <div class="col-md-6">
                                <label>Expire Points After (Days)</label>
                                <input type="number" step="1" min="1" name="expiry_days" class="form-control" value="<?php echo html_escape(isset($settings['expiry_days']) ? $settings['expiry_days'] : 365); ?>">
                                <p class="text-muted mtop5">Example: 365 means earned points expire after one year.</p>
                            </div>
                        </div>

                        <div class="mtop20">
                            <button type="submit" class="btn btn-primary">Save Rewards Settings</button>
                            <a href="<?php echo admin_url('pos/pos_controller/rewards_history'); ?>" class="btn btn-default">Open Rewards History</a>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>