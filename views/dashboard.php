<?php init_head(); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap');

    body,
    #wrapper,
    .content,
    .panel_s,
    .panel-body,
    .form-control,
    .btn,
    .control-label {
        font-family: 'Manrope', 'Segoe UI', Tahoma, sans-serif;
    }

    #wrapper {
        background:
            radial-gradient(circle at 15% 0%, rgba(22, 163, 74, 0.08), transparent 40%),
            radial-gradient(circle at 100% 15%, rgba(2, 132, 199, 0.09), transparent 35%),
            linear-gradient(180deg, #f6fafc 0%, #f2f7fb 100%);
        min-height: 100vh;
    }

    .content {
        padding-top: 16px;
    }

    .panel_s {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid #dbe7ef;
        border-radius: 18px;
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(4px);
    }

    .panel-body {
        padding: 18px;
    }

    .no-margin {
        font-family: 'Space Grotesk', 'Manrope', sans-serif;
        font-size: 18px;
        letter-spacing: 0.2px;
        color: #0f172a;
    }

    .hr-panel-separator {
        margin: 10px 0 14px;
        border-top: 1px solid #e4edf4;
    }

    #pos-search-input {
        height: 42px;
        border-radius: 11px;
        border: 1px solid #ccdae5;
        background: #f8fbfd;
        transition: all .2s ease;
    }

    #pos-search-input:focus,
    .form-control:focus {
        border-color: #0ea5a3;
        box-shadow: 0 0 0 3px rgba(14, 165, 163, 0.15);
        background: #fff;
    }

    .pos-shortcuts-bar {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .pos-shortcut-chip {
        border: 1px solid #d6e4ee;
        background: #f7fbff;
        color: #334155;
        font-size: 11px;
        line-height: 1;
        padding: 6px 8px;
        border-radius: 999px;
        font-weight: 700;
    }

    .pos-shortcut-chip span {
        color: #0f766e;
        margin-right: 3px;
    }

    #pos-products-grid {
        max-height: calc(100vh - 260px);
        overflow-y: auto;
        padding-right: 6px;
    }

    #pos-products-grid::-webkit-scrollbar {
        width: 9px;
    }

    #pos-products-grid::-webkit-scrollbar-thumb {
        background: #c7d7e3;
        border-radius: 20px;
    }

    .product-grid-item {
        border: 1px solid #d4e1ea !important;
        border-radius: 14px !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        min-height: 108px !important;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        animation: posCardIn .35s ease both;
    }

    .product-grid-item:hover {
        transform: translateY(-3px);
        border-color: #0ea5a3 !important;
        box-shadow: 0 14px 26px rgba(14, 165, 163, 0.20);
    }

    .product-grid-item.is-keyboard-selected {
        border-color: #0f766e !important;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.22), 0 14px 26px rgba(14, 165, 163, 0.20);
        transform: translateY(-2px);
    }

    .pos-product-col:nth-child(2n) .product-grid-item {
        animation-delay: .04s;
    }

    .pos-product-col:nth-child(3n) .product-grid-item {
        animation-delay: .08s;
    }

    #pos-cart-items {
        max-height: 240px;
        overflow-y: auto;
        border: 1px solid #d9e7f0;
        background: #fbfdff;
        border-radius: 12px;
        padding: 10px 12px;
    }

    #pos-cart-items .clearfix:last-child {
        border-bottom: none !important;
    }

    .pos-remove-item {
        font-weight: 700;
        color: #dc2626 !important;
        opacity: 0.8;
    }

    .pos-remove-item:hover {
        opacity: 1;
        text-decoration: none;
    }

    .payment-modes-wrapper {
        background: #f6fafc !important;
        border: 1px solid #d8e5ef !important;
        border-radius: 12px !important;
        padding: 12px !important;
    }

    .split-amount-input,
    #pos-tendered-input,
    #pos-customer-select,
    #pos-discount-type,
    #pos-discount-value {
        border-radius: 10px;
        border: 1px solid #ccd9e4;
        height: 34px;
    }

    #pos-split-toggle-btn,
    #pos-hold-btn,
    #pos-resume-btn,
    #pos-reset-btn,
    #pos-quick-customer-btn {
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: 0.15px;
        border: none;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    }

    #pos-hold-btn {
        background: #f59e0b;
        color: #fff;
    }

    #pos-resume-btn {
        background: #0891b2;
        color: #fff;
    }

    #pos-reset-btn {
        background: #ef4444;
        color: #fff;
    }

    #pos-quick-customer-btn,
    #pos-split-toggle-btn {
        background: #0f766e;
        color: #fff;
    }

    #pos-checkout-btn {
        border: none;
        border-radius: 14px;
        padding: 14px !important;
        font-weight: 800;
        letter-spacing: 0.3px;
        background: linear-gradient(135deg, #0f766e 0%, #0ea5a3 100%);
        box-shadow: 0 12px 24px rgba(14, 116, 110, 0.28);
        transition: transform .15s ease, box-shadow .2s ease, filter .2s ease;
    }

    #pos-checkout-btn:hover:not([disabled]) {
        transform: translateY(-1px);
        filter: brightness(1.02);
        box-shadow: 0 16px 28px rgba(14, 116, 110, 0.36);
    }

    #pos-checkout-btn[disabled] {
        opacity: .65;
    }

    #pos-cart-summary {
        color: #0f172a;
        font-size: 20px !important;
    }

    #pos-discount-summary,
    #pos-payment-validation-msg {
        font-weight: 600;
    }

    #pos-change-display {
        font-family: 'Space Grotesk', 'Manrope', sans-serif;
        letter-spacing: 0.3px;
    }

    #pos-quick-customer-modal .modal-content {
        border-radius: 16px;
        border: 1px solid #d7e5ee;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.20);
    }

    #pos-quick-customer-modal .modal-title {
        font-family: 'Space Grotesk', 'Manrope', sans-serif;
        font-weight: 700;
    }

    #pos-quick-customer-save {
        border-radius: 10px;
        background: #0f766e;
        border-color: #0f766e;
    }

    #pos-repair-invoices-btn {
        border-radius: 999px;
        font-weight: 700;
        border: 1px solid #c8d8e4;
        background: #f8fbff;
        color: #0f4b6e;
    }

    #pos-repair-feedback {
        min-height: 16px;
        font-size: 11px;
        font-weight: 700;
    }

    @keyframes posCardIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 991px) {
        .content {
            padding-top: 10px;
        }

        .panel-body {
            padding: 14px;
        }

        #pos-products-grid {
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }

        #pos-cart-items {
            max-height: 180px;
        }

        #pos-checkout-btn {
            padding: 12px !important;
        }
    }

    @media print {
        body * {
            display: none !important;
        }

        #pos-receipt-print,
        #pos-receipt-print * {
            display: block !important;
        }

        #pos-receipt-print {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            line-height: 1.2;
            color: #000;
            white-space: pre-wrap;
        }
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Products Grid (Square-like UI)</h4>
                        <div class="row mtop5 mbot10">
                            <div class="col-md-12">
                                <input type="text" id="pos-search-input" class="form-control" style="padding: 6px 10px; font-size: 14px;" placeholder="Search products by name or scan barcode directly...">
                                <div class="pos-shortcuts-bar">
                                    <div class="pos-shortcut-chip"><span>F2</span>Quick Customer</div>
                                    <div class="pos-shortcut-chip"><span>Alt+1</span>Search</div>
                                    <div class="pos-shortcut-chip"><span>Alt+2</span>Tendered</div>
                                    <div class="pos-shortcut-chip"><span>Ctrl+Enter</span>Checkout</div>
                                    <div class="pos-shortcut-chip"><span>Del</span>Remove Last Item</div>
                                    <div class="pos-shortcut-chip"><span>Arrows+Enter</span>Browse/Add Product</div>
                                    <button type="button" id="pos-sound-toggle" class="pos-shortcut-chip" style="cursor:pointer;">Sound: ON</button>
                                </div>
                            </div>
                        </div>
                        <hr class="hr-panel-separator" />
                        <div class="row mtop15" id="pos-products-grid">
                            <?php if (!empty($products)) { ?>
                                <?php foreach ($products as $product) { ?>
                                    <div class="col-md-2 col-sm-3 col-xs-4 mtop10 pos-product-col">
                                        <div
                                            class="product-grid-item text-center"
                                            data-id="<?php echo $product['id']; ?>"
                                            data-name="<?php echo html_escape($product['name']); ?>"
                                            data-rate="<?php echo (float) $product['rate']; ?>"
                                            data-barcode="<?php echo html_escape(isset($product['barcode']) ? $product['barcode'] : ''); ?>"
                                            style="border: 1px solid #dce1ef; border-radius: 8px; padding: 10px 6px; min-height: 95px; cursor: pointer; display: flex; flex-direction: column; justify-content: space-between;">
                                            <div style="font-weight: 600; line-height: 1.3;"><?php echo html_escape($product['name']); ?></div>
                                            <div style="font-size: 14px; font-weight: 700; margin-top: 6px;"><?php echo app_format_money($product['rate'], get_base_currency()); ?></div>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="col-md-12">
                                    <p class="text-muted mtop10 mbot0">No active products found with a price.</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            Shopping Cart & Checkout
                            <div class="pull-right">
                                <button type="button" id="pos-hold-btn" class="btn btn-warning btn-xs">⏸ Hold</button>
                                <button type="button" id="pos-resume-btn" class="btn btn-info btn-xs" style="display:none;">▶ Resume</button>
                                <button type="button" id="pos-reset-btn" class="btn btn-danger btn-xs">Reset</button>
                            </div>
                        </h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-separator" />

                        <div class="form-group text-left">
                            <label class="control-label" style="font-weight:600; display:block; margin-bottom:8px;">Customer</label>
                            <div class="input-group">
                                <select id="pos-customer-select" class="form-control input-sm">
                                    <option value="0" selected>Walk-In Customer</option>
                                    <?php if (!empty($customers)) {
                                        foreach ($customers as $customer) {
                                            $customer_name = isset($customer['company']) ? $customer['company'] : '';
                                            if (strtolower(trim($customer_name)) === 'walk-in customer') {
                                                continue;
                                            }
                                    ?>
                                            <option value="<?php echo (int) $customer['userid']; ?>"><?php echo html_escape($customer_name); ?></option>
                                    <?php }
                                    } ?>
                                </select>
                                <span class="input-group-btn">
                                    <button type="button" id="pos-quick-customer-btn" class="btn btn-default btn-sm">Quick + Customer</button>
                                </span>
                            </div>
                            <div id="pos-points-balance-box" class="mtop5 text-muted" style="font-size:12px;">
                                Reward Points: <strong id="pos-customer-points">0</strong>
                            </div>
                            <div class="row mtop5" id="pos-redeem-wrapper">
                                <div class="col-xs-8">
                                    <input type="number" id="pos-redeem-points" class="form-control input-sm" min="0" step="1" placeholder="Redeem points">
                                </div>
                                <div class="col-xs-4">
                                    <button type="button" id="pos-redeem-max" class="btn btn-default btn-sm btn-block">Max</button>
                                </div>
                            </div>
                            <div id="pos-redeem-summary" class="mtop5 text-right text-muted" style="font-size: 12px;">Redemption: $<span id="pos-redeem-amount">0.00</span></div>
                        </div>

                        <div id="pos-cart-items" class="mtop15"></div>

                        <div class="form-group mtop15 text-left">
                            <label class="control-label" style="font-weight:600; display:block; margin-bottom:8px;">Payment Method</label>
                            <button type="button" id="pos-split-toggle-btn" class="btn btn-default btn-xs mbot10">Enable Split Payment</button>
                            <div class="payment-modes-wrapper" id="split-inputs-container" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
                                <?php if (!empty($payment_modes)) {
                                    foreach ($payment_modes as $key => $mode) { ?>
                                        <div class="row mtop5 payment-split-row">
                                            <div class="col-xs-7">
                                                <label style="font-weight:500; cursor:pointer; margin:0;">
                                                    <input type="checkbox" class="split-mode-check" data-mode-id="<?php echo html_escape($mode['id']); ?>" <?php echo $key === 0 ? 'checked' : ''; ?>>
                                                    <?php echo html_escape($mode['name']); ?>
                                                </label>
                                            </div>
                                            <div class="col-xs-5">
                                                <input type="number" step="0.01" min="0" class="form-control input-sm split-amount-input" data-mode-id="<?php echo html_escape($mode['id']); ?>" placeholder="0.00" <?php echo $key === 0 ? '' : 'disabled'; ?>>
                                            </div>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <span class="text-danger" style="font-size:12px;">No active payment modes found in Setup -> Finance -> Payment Modes.</span>
                                <?php } ?>

                                <div class="mtop10">
                                    <label style="font-weight:600; font-size:12px;">Cash Tendered / Till Box</label>
                                    <input type="number" id="pos-tendered-input" class="form-control input-sm" placeholder="0.00">
                                    <div class="btn-group btn-group-justified mtop5" role="group">
                                        <div class="btn-group" role="group"><button type="button" class="btn btn-default btn-xs denom-tile" data-val="5">$5</button></div>
                                        <div class="btn-group" role="group"><button type="button" class="btn btn-default btn-xs denom-tile" data-val="10">$10</button></div>
                                        <div class="btn-group" role="group"><button type="button" class="btn btn-default btn-xs denom-tile" data-val="20">$20</button></div>
                                        <div class="btn-group" role="group"><button type="button" class="btn btn-default btn-xs denom-tile" data-val="50">$50</button></div>
                                        <div class="btn-group" role="group"><button type="button" class="btn btn-default btn-xs denom-tile" data-val="100">$100</button></div>
                                    </div>
                                </div>
                                <div class="mtop10 text-right text-success" style="font-weight:700; font-size:15px;">Change Due: $<span id="pos-change-display">0.00</span></div>
                                <div id="pos-payment-validation-msg" class="mtop5 text-danger" style="font-size:12px; min-height:18px;"></div>
                            </div>
                        </div>

                        <div id="pos-cart-summary" class="mtop15 text-right" style="font-size: 18px; font-weight: 700;">Total: <span id="cart-total-amount">0.00</span></div>
                        <div class="row mtop10">
                            <div class="col-xs-5">
                                <select id="pos-discount-type" class="form-control input-sm">
                                    <option value="amount">Discount ($)</option>
                                    <option value="percent">Discount (%)</option>
                                </select>
                            </div>
                            <div class="col-xs-7">
                                <input type="number" id="pos-discount-value" class="form-control input-sm" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div id="pos-discount-summary" class="mtop5 text-right text-muted" style="font-size: 12px;">Discount Applied: $<span id="pos-discount-amount">0.00</span></div>
                        <button id="pos-checkout-btn" class="btn btn-success btn-block btn-lg mtop15" style="padding: 15px;" disabled>Proceed to Payment</button>
                        <div class="mtop15 text-center"><a href="<?php echo admin_url('expenses/expense'); ?>" target="_blank" style="font-weight:600; font-size:12px;">🧾 Process External Utility / Bill Payment</a></div>
                        <?php if (is_admin()) { ?>
                            <div class="mtop10 text-center">
                                <button type="button" id="pos-repair-invoices-btn" class="btn btn-default btn-xs">Repair Invoice Totals</button>
                                <div id="pos-repair-feedback" class="text-muted mtop5"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="pos-quick-customer-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Quick Add Customer</h4>
            </div>
            <form id="pos-quick-customer-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="pos-quick-customer-company">Customer Name</label>
                        <input type="text" id="pos-quick-customer-company" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="pos-quick-customer-phone">Phone (optional)</label>
                        <input type="text" id="pos-quick-customer-phone" class="form-control">
                    </div>
                    <div class="form-group mbot0">
                        <label for="pos-quick-customer-email">Email (optional)</label>
                        <input type="email" id="pos-quick-customer-email" class="form-control">
                    </div>
                    <div id="pos-quick-customer-feedback" class="text-danger mtop10" style="min-height:18px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="pos-quick-customer-save" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="pos-receipt-print" style="display: none;"></div>
<script>
    (function() {
        function bootPosCart($) {
            var cart = window.posCart || [];
            var checkoutUrl = '<?php echo admin_url("pos/pos_controller/checkout"); ?>';
            var quickCustomerUrl = '<?php echo admin_url("pos/pos_controller/quick_add_customer"); ?>';
            var repairInvoicesUrl = '<?php echo admin_url("pos/pos_controller/repair_invoice_totals"); ?>';
            var customerPointsBaseUrl = '<?php echo admin_url("pos/pos_controller/customer_points/"); ?>';
            var rewardsConfig = <?php echo json_encode(isset($reward_settings) ? $reward_settings : []); ?>;
            var heldBasketKey = 'pos_held_basket';
            var barcodeBuffer = '';
            var barcodeLastTime = 0;
            var barcodeMaxGapMs = 50;
            var dynamicTotal = 0;
            var remainingBalance = dynamicTotal;
            var isSplitMode = false;
            var userAdjustedSplit = false;
            var selectedCustomerId = 0;
            var keyboardSelectedProductIndex = -1;
            var soundFeedbackEnabled = true;
            var availableRewardPoints = 0;
            var rewardsEnabled = Number(rewardsConfig.enabled || 0) === 1;

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatMoney(value) {
                return Number(value || 0).toFixed(2);
            }

            function playFeedbackTone(type) {
                if (!soundFeedbackEnabled || !window.AudioContext && !window.webkitAudioContext) {
                    return;
                }

                try {
                    var Ctx = window.AudioContext || window.webkitAudioContext;
                    var audioCtx = new Ctx();
                    var oscillator = audioCtx.createOscillator();
                    var gainNode = audioCtx.createGain();
                    var now = audioCtx.currentTime;
                    var isError = type === 'error';

                    oscillator.type = isError ? 'sawtooth' : 'sine';
                    oscillator.frequency.setValueAtTime(isError ? 220 : 880, now);
                    gainNode.gain.setValueAtTime(0.0001, now);
                    gainNode.gain.exponentialRampToValueAtTime(0.08, now + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.0001, now + (isError ? 0.11 : 0.07));

                    oscillator.connect(gainNode);
                    gainNode.connect(audioCtx.destination);
                    oscillator.start(now);
                    oscillator.stop(now + (isError ? 0.12 : 0.08));
                } catch (err) {
                    // Ignore audio API failures.
                }
            }

            function getVisibleProductCards() {
                return $('.pos-product-col:visible .product-grid-item');
            }

            function setKeyboardSelectedProduct(index, ensureVisible) {
                var $cards = getVisibleProductCards();
                $('.product-grid-item').removeClass('is-keyboard-selected');

                if (!$cards.length) {
                    keyboardSelectedProductIndex = -1;
                    return;
                }

                if (index < 0) {
                    index = 0;
                }
                if (index >= $cards.length) {
                    index = $cards.length - 1;
                }

                keyboardSelectedProductIndex = index;
                var $selected = $cards.eq(index);
                $selected.addClass('is-keyboard-selected');

                if (ensureVisible && $selected.length && $selected[0].scrollIntoView) {
                    $selected[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'nearest'
                    });
                }
            }

            function getColumnCount() {
                if (window.innerWidth < 768) {
                    return 3;
                }
                if (window.innerWidth < 992) {
                    return 4;
                }
                return 6;
            }

            function addProductToCart($itemCard, sourceType) {
                if (!$itemCard || !$itemCard.length) {
                    return false;
                }

                var itemId = parseInt($itemCard.data('id'), 10) || 0;
                var itemName = String($itemCard.data('name') || '').trim();
                var itemPrice = parseFloat($itemCard.data('rate')) || 0;

                if (!itemId || !itemName || itemPrice <= 0) {
                    return false;
                }

                var missingMatch = true;
                for (var i = 0; i < cart.length; i++) {
                    if (cart[i].id === itemId) {
                        cart[i].quantity += 1;
                        missingMatch = false;
                        break;
                    }
                }

                if (missingMatch) {
                    cart.push({
                        id: itemId,
                        name: itemName,
                        rate: itemPrice,
                        quantity: 1
                    });
                }

                window.posCart = cart;
                updateCartUI();

                if (sourceType === 'scan' || sourceType === 'keyboard' || sourceType === 'click') {
                    playFeedbackTone('ok');
                }

                return true;
            }

            function getCartTotal() {
                var total = 0;
                $.each(cart, function(i, item) {
                    total += Number(item.rate) * Number(item.quantity);
                });
                return total;
            }

            function getDiscountValue() {
                return parseFloat($('#pos-discount-value').val()) || 0;
            }

            function getDiscountType() {
                return String($('#pos-discount-type').val() || 'amount').toLowerCase();
            }

            function getDiscountAmount(subTotal) {
                var discountValue = getDiscountValue();
                if (discountValue <= 0 || subTotal <= 0) {
                    return 0;
                }

                var discountAmount = 0;
                if (getDiscountType() === 'percent') {
                    discountAmount = (subTotal * discountValue) / 100;
                } else {
                    discountAmount = discountValue;
                }

                if (discountAmount > subTotal) {
                    discountAmount = subTotal;
                }

                return Number(discountAmount.toFixed(2));
            }

            function getRedeemPointsInput() {
                return parseInt($('#pos-redeem-points').val(), 10) || 0;
            }

            function getRedeemAmount(afterDiscountTotal) {
                if (!rewardsEnabled || selectedCustomerId <= 0 || afterDiscountTotal <= 0) {
                    return {
                        points: 0,
                        amount: 0
                    };
                }

                var redeemValue = parseFloat(rewardsConfig.redeem_value_per_point || 0);
                var minRedeemPoints = parseInt(rewardsConfig.min_redeem_points || 0, 10) || 0;
                if (redeemValue <= 0) {
                    return {
                        points: 0,
                        amount: 0
                    };
                }

                var requestedPoints = getRedeemPointsInput();
                var maxByBalance = Math.max(0, Math.min(requestedPoints, availableRewardPoints));
                var maxByTotal = Math.floor(afterDiscountTotal / redeemValue);
                var usablePoints = Math.min(maxByBalance, maxByTotal);

                if (minRedeemPoints > 0 && usablePoints < minRedeemPoints) {
                    usablePoints = 0;
                }

                return {
                    points: usablePoints,
                    amount: Number((usablePoints * redeemValue).toFixed(2))
                };
            }

            function refreshCustomerPoints() {
                if (selectedCustomerId <= 0) {
                    availableRewardPoints = 0;
                    $('#pos-customer-points').text('0');
                    $('#pos-redeem-points').val('');
                    updateCartUI();
                    return;
                }

                $.getJSON(customerPointsBaseUrl + selectedCustomerId, function(response) {
                    if (response && response.success) {
                        availableRewardPoints = parseInt(response.points, 10) || 0;
                        $('#pos-customer-points').text(String(availableRewardPoints));
                    } else {
                        availableRewardPoints = 0;
                        $('#pos-customer-points').text('0');
                    }
                    updateCartUI();
                }).fail(function() {
                    availableRewardPoints = 0;
                    $('#pos-customer-points').text('0');
                    updateCartUI();
                });
            }

            function getCheckedPaymentRows() {
                var checkedRows = [];
                $('.split-mode-check:checked').each(function() {
                    var modeId = String($(this).data('mode-id') || '').trim();
                    if (!modeId) {
                        return;
                    }

                    var $input = $('.split-amount-input[data-mode-id="' + modeId + '"]');
                    var $row = $(this).closest('.payment-split-row');
                    var modeLabel = $.trim($row.find('label').text()).toLowerCase();

                    checkedRows.push({
                        mode: modeId,
                        input: $input,
                        modeLabel: modeLabel,
                        modeText: $.trim($row.find('label').text())
                    });
                });

                return checkedRows;
            }

            function getCashPaymentRow(rows) {
                var paymentRows = rows || getCheckedPaymentRows();
                var cashRow = null;

                $.each(paymentRows, function(i, row) {
                    if (row.modeLabel.indexOf('cash') !== -1) {
                        cashRow = row;
                        return false;
                    }
                    return true;
                });

                return cashRow;
            }

            function setResumeButtonVisibility() {
                var held = window.sessionStorage.getItem(heldBasketKey);
                if (held) {
                    $('#pos-resume-btn').show();
                } else {
                    $('#pos-resume-btn').hide();
                }
            }

            function applyPaymentMatrix(forceAutoSplit, editedModeId) {
                var checkedRows = getCheckedPaymentRows();
                var cashRow = getCashPaymentRow(checkedRows);

                $('.split-amount-input').each(function() {
                    var $input = $(this);
                    var modeId = String($input.data('mode-id') || '');
                    var isChecked = $('.split-mode-check[data-mode-id="' + modeId + '"]').is(':checked');

                    if (isChecked) {
                        $input.prop('disabled', false);
                    } else {
                        $input.prop('disabled', true).val('');
                    }
                });

                if (!checkedRows.length) {
                    remainingBalance = dynamicTotal;
                    return;
                }

                if (!isSplitMode) {
                    var primaryRow = checkedRows[0];
                    $('.split-mode-check').prop('checked', false);
                    $('.split-mode-check[data-mode-id="' + primaryRow.mode + '"]').prop('checked', true);
                    primaryRow.input.val(formatMoney(dynamicTotal)).prop('disabled', true);
                    remainingBalance = 0;
                    return;
                }

                if (forceAutoSplit || !userAdjustedSplit) {
                    $.each(checkedRows, function(i, row) {
                        if (!row.input.val()) {
                            row.input.val('0.00');
                        }
                    });
                }

                if (checkedRows.length === 1) {
                    if (!checkedRows[0].input.val() || forceAutoSplit) {
                        checkedRows[0].input.val(formatMoney(dynamicTotal));
                    }
                    remainingBalance = Number((dynamicTotal - (parseFloat(checkedRows[0].input.val()) || 0)).toFixed(2));
                    return;
                }

                var targetRow = null;
                if (cashRow) {
                    targetRow = cashRow;
                } else if (checkedRows.length) {
                    targetRow = checkedRows[checkedRows.length - 1];
                }

                if (targetRow && editedModeId && targetRow.mode === editedModeId && checkedRows.length > 1) {
                    $.each(checkedRows, function(i, row) {
                        if (row.mode !== editedModeId) {
                            targetRow = row;
                            return false;
                        }
                        return true;
                    });
                }

                var nonTargetSum = 0;
                $.each(checkedRows, function(i, row) {
                    if (targetRow && row.mode === targetRow.mode) {
                        return;
                    }
                    nonTargetSum += parseFloat(row.input.val()) || 0;
                });

                remainingBalance = Number((dynamicTotal - nonTargetSum).toFixed(2));
                if (remainingBalance < 0) {
                    remainingBalance = 0;
                }

                if (targetRow) {
                    targetRow.input.val(formatMoney(remainingBalance));
                }
            }

            function getSumOfCheckedSegments() {
                var checkedRows = getCheckedPaymentRows();
                var sum = 0;

                $.each(checkedRows, function(i, row) {
                    sum += parseFloat(row.input.val()) || 0;
                });

                return Number(sum.toFixed(2));
            }

            function getAllocatedCashAmount() {
                var cashRow = getCashPaymentRow();
                if (!cashRow) {
                    return 0;
                }

                return Number((parseFloat(cashRow.input.val()) || 0).toFixed(2));
            }

            function updateCheckoutGuard() {
                var checkedRows = getCheckedPaymentRows();
                var hasCart = cart.length > 0;
                var sumSegments = getSumOfCheckedSegments();
                var exactTotalMatch = Math.abs(sumSegments - dynamicTotal) < 0.01;
                var cashAllocated = getAllocatedCashAmount();
                var tendered = parseFloat($('#pos-tendered-input').val()) || 0;
                var cashUsed = cashAllocated > 0;
                var cashCovered = !cashUsed || tendered >= cashAllocated;
                var canCheckout = hasCart && checkedRows.length > 0 && exactTotalMatch && cashCovered;
                var validationMessage = '';

                if (!hasCart) {
                    validationMessage = '';
                } else if (!checkedRows.length) {
                    validationMessage = 'Select at least one payment method.';
                } else if (!exactTotalMatch) {
                    var gap = Number((dynamicTotal - sumSegments).toFixed(2));
                    if (gap > 0) {
                        validationMessage = 'Remaining to allocate: $' + formatMoney(gap);
                    } else {
                        validationMessage = 'Over-allocated by: $' + formatMoney(Math.abs(gap));
                    }
                } else if (!cashCovered) {
                    validationMessage = 'Insufficient cash tendered for allocated cash segment ($' + formatMoney(cashAllocated) + ').';
                }

                $('#pos-checkout-btn').prop('disabled', !canCheckout);
                $('#pos-payment-validation-msg').text(validationMessage);
            }

            function updateSplitToggleUI() {
                var $btn = $('#pos-split-toggle-btn');
                if (isSplitMode) {
                    $btn.removeClass('btn-default').addClass('btn-primary').text('Split Payment: ON');
                } else {
                    $btn.removeClass('btn-primary').addClass('btn-default').text('Enable Split Payment');
                }
            }

            function buildSplitPaymentsPayload() {
                var splitPayments = [];
                var activeRows = getCheckedPaymentRows();

                $.each(activeRows, function(i, row) {
                    var amount = parseFloat(row.input.val()) || 0;
                    if (amount > 0) {
                        splitPayments.push({
                            mode: row.mode,
                            mode_name: row.modeText || row.mode,
                            amount: Number(amount.toFixed(2))
                        });
                    }
                });

                if (!splitPayments.length) {
                    splitPayments.push({
                        mode: '1',
                        mode_name: 'Payment',
                        amount: Number(getCartTotal().toFixed(2))
                    });
                }

                return splitPayments;
            }

            function updateChangeDueState() {
                var tendered = parseFloat($('#pos-tendered-input').val()) || 0;
                var cashAllocated = getAllocatedCashAmount();
                var cashUsed = cashAllocated > 0;
                var change = tendered - cashAllocated;

                if (cashUsed && change >= 0) {
                    $('#pos-change-display').text(formatMoney(change));
                } else {
                    $('#pos-change-display').text('0.00');
                }

                updateCheckoutGuard();
            }

            function updateCartUI() {
                var $itemsContainer = $('#pos-cart-items');
                var $btn = $('#pos-checkout-btn');
                var subTotal = 0;
                var discountAmount = 0;
                var redeemData = {
                    points: 0,
                    amount: 0
                };
                dynamicTotal = 0;
                remainingBalance = dynamicTotal;

                $btn.text('Proceed to Payment');
                $itemsContainer.empty();

                if (!cart.length) {
                    $itemsContainer.append('<p class="text-muted mbot0">No items added yet.</p>');
                    $('#cart-total-amount').text('0.00');
                    $('#pos-discount-amount').text('0.00');
                    $('#pos-redeem-amount').text('0.00');
                    $btn.prop('disabled', true);
                    applyPaymentMatrix(true);
                    updateChangeDueState();
                    return;
                }

                $.each(cart, function(i, item) {
                    var itemCost = item.rate * item.quantity;
                    subTotal += itemCost;

                    $itemsContainer.append(
                        '<div class="clearfix" style="border-bottom: 1px solid #e4e8f1; padding: 10px 0; display: block !important;">' +
                        '<div class="pull-left"><strong>' + escapeHtml(item.name) + '</strong> x ' + item.quantity + '</div>' +
                        '<div class="pull-right" style="font-weight: 600; white-space: nowrap;">$' + formatMoney(itemCost) + ' <button type="button" class="btn btn-link text-danger pos-remove-item" data-index="' + i + '" style="padding: 0 0 0 6px; vertical-align: baseline;">x</button></div>' +
                        '</div>'
                    );
                });

                discountAmount = getDiscountAmount(subTotal);
                redeemData = getRedeemAmount(Number((subTotal - discountAmount).toFixed(2)));
                dynamicTotal = Number((subTotal - discountAmount - redeemData.amount).toFixed(2));
                if (dynamicTotal < 0) {
                    dynamicTotal = 0;
                }

                $('#cart-total-amount').text(formatMoney(dynamicTotal));
                $('#pos-discount-amount').text(formatMoney(discountAmount));
                $('#pos-redeem-amount').text(formatMoney(redeemData.amount));
                applyPaymentMatrix(false);
                updateChangeDueState();
            }

            function filterProducts(term) {
                var normalized = String(term || '').toLowerCase().trim();
                $('.product-grid-item').each(function() {
                    var $item = $(this);
                    var productName = String($item.data('name') || '').toLowerCase();
                    var matched = !normalized || productName.indexOf(normalized) !== -1;
                    $item.closest('.pos-product-col').toggle(matched);
                });

                setKeyboardSelectedProduct(0, false);
            }

            function processScannedBarcode(barcodeValue) {
                var barcode = String(barcodeValue || '').trim();
                if (!barcode) {
                    return;
                }

                var $matchedItem = null;
                $('.product-grid-item').each(function() {
                    var $item = $(this);
                    var itemBarcode = String($item.data('barcode') || '').trim();
                    if (itemBarcode && itemBarcode === barcode) {
                        $matchedItem = $item;
                        return false;
                    }
                    return true;
                });

                if ($matchedItem && $matchedItem.length) {
                    addProductToCart($matchedItem, 'scan');
                    setKeyboardSelectedProduct(getVisibleProductCards().index($matchedItem), false);
                } else {
                    playFeedbackTone('error');
                }
            }

            function isTypingContext() {
                var activeEl = document.activeElement;
                if (!activeEl) {
                    return false;
                }

                var tagName = activeEl.tagName ? activeEl.tagName.toLowerCase() : '';
                if (tagName === 'input' || tagName === 'textarea' || tagName === 'select') {
                    return true;
                }

                return !!activeEl.isContentEditable;
            }

            function focusSearchInput(selectContent) {
                var $search = $('#pos-search-input');
                $search.focus();
                if (selectContent) {
                    $search.select();
                }
            }

            function buildReceiptHtml(response, soldItems, paymentMeta) {
                var now = new Date();
                var lines = [];
                var total = 0;
                var tenderedAmount = paymentMeta && paymentMeta.tendered ? Number(paymentMeta.tendered) : 0;
                var changeAmount = paymentMeta && paymentMeta.change ? Number(paymentMeta.change) : 0;
                var receiptPayments = paymentMeta && $.isArray(paymentMeta.splitPayments) ? paymentMeta.splitPayments : [];
                var receiptDiscount = paymentMeta && paymentMeta.discount ? Number(paymentMeta.discount) : 0;
                var receiptRedeem = paymentMeta && paymentMeta.redeem ? Number(paymentMeta.redeem) : 0;
                var receiptPointsEarned = paymentMeta && paymentMeta.pointsEarned ? Number(paymentMeta.pointsEarned) : 0;
                var receiptPointsRedeemed = paymentMeta && paymentMeta.pointsRedeemed ? Number(paymentMeta.pointsRedeemed) : 0;

                lines.push('B2C QUICK POS');
                lines.push('SALES RECEIPT');
                lines.push('------------------------------');
                lines.push('Date: ' + now.toLocaleString());
                lines.push('Invoice: ' + (response.invoice_id ? response.invoice_id : 'N/A'));
                lines.push('------------------------------');

                $.each(soldItems, function(i, item) {
                    var lineTotal = Number(item.rate) * Number(item.quantity);
                    total += lineTotal;
                    lines.push(item.name);
                    lines.push(item.quantity + ' x $' + formatMoney(item.rate) + ' = $' + formatMoney(lineTotal));
                });

                lines.push('------------------------------');
                lines.push('TOTAL: $' + formatMoney(total));
                if (receiptDiscount > 0) {
                    lines.push('DISCOUNT: -$' + formatMoney(receiptDiscount));
                    lines.push('NET TOTAL: $' + formatMoney(total - receiptDiscount));
                }
                if (receiptRedeem > 0) {
                    lines.push('POINTS REDEEMED: -$' + formatMoney(receiptRedeem));
                    lines.push('FINAL TOTAL: $' + formatMoney(total - receiptDiscount - receiptRedeem));
                }
                if (receiptPayments.length) {
                    lines.push('PAYMENTS:');
                    $.each(receiptPayments, function(i, p) {
                        var paymentLabel = p.mode_name ? p.mode_name : p.mode;
                        var paymentAmount = Number(p.amount || 0);
                        lines.push(paymentLabel + ': $' + formatMoney(paymentAmount));
                    });
                }
                lines.push('Amount Tendered: $' + formatMoney(tenderedAmount));
                if (changeAmount > 0) {
                    lines.push('Change: $' + formatMoney(changeAmount));
                }
                if (receiptPointsEarned > 0 || receiptPointsRedeemed > 0) {
                    lines.push('Points Earned: ' + Math.round(receiptPointsEarned));
                    lines.push('Points Redeemed: ' + Math.round(receiptPointsRedeemed));
                }
                lines.push('------------------------------');
                lines.push('Thank you for your purchase.');

                return '<pre style="margin:0;">' + escapeHtml(lines.join('\n')) + '</pre>';
            }

            $(document).on('click', '.product-grid-item', function(e) {
                e.preventDefault();
                var $itemCard = $(this);
                addProductToCart($itemCard, 'click');
                setKeyboardSelectedProduct(getVisibleProductCards().index($itemCard), false);
            });

            $(document).on('mouseenter', '.product-grid-item', function() {
                var idx = getVisibleProductCards().index($(this));
                if (idx >= 0) {
                    setKeyboardSelectedProduct(idx, false);
                }
            });

            $(document).on('click', '.pos-remove-item', function(e) {
                e.preventDefault();
                var index = parseInt($(this).data('index'), 10);
                if (isNaN(index) || index < 0 || index >= cart.length) {
                    return;
                }

                cart.splice(index, 1);
                window.posCart = cart;
                updateCartUI();
            });

            $(document).on('click', '#pos-reset-btn', function() {
                cart = [];
                window.posCart = cart;
                $('#pos-tendered-input').val('');
                $('#pos-change-display').text('0.00');
                $('#pos-discount-value').val('');
                userAdjustedSplit = false;
                updateCartUI();
                focusSearchInput(false);
            });

            $('#pos-search-input').on('keyup', function() {
                filterProducts($(this).val());
            });

            $(document).on('click', '#pos-sound-toggle', function() {
                soundFeedbackEnabled = !soundFeedbackEnabled;
                $(this).text(soundFeedbackEnabled ? 'Sound: ON' : 'Sound: OFF');
            });

            $(document).on('click', '#pos-repair-invoices-btn', function() {
                var $btn = $(this);
                if (!window.confirm('Run invoice totals repair now?')) {
                    return;
                }

                $('#pos-repair-feedback').removeClass('text-success text-danger').addClass('text-muted').text('Repair running...');
                $btn.prop('disabled', true).text('Repairing...');

                $.ajax({
                    url: repairInvoicesUrl,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            var updated = response.updated || 0;
                            $('#pos-repair-feedback').removeClass('text-muted text-danger').addClass('text-success').text('Repair complete. Updated invoices: ' + updated);
                            alert('Invoice totals repair completed. Updated: ' + updated);
                        } else {
                            $('#pos-repair-feedback').removeClass('text-muted text-success').addClass('text-danger').text((response && response.message) ? response.message : 'Repair failed.');
                        }
                    },
                    error: function() {
                        $('#pos-repair-feedback').removeClass('text-muted text-success').addClass('text-danger').text('Repair request failed.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Repair Invoice Totals');
                    }
                });
            });

            $(window).on('keydown', function(e) {
                if (e.key === 'F2') {
                    e.preventDefault();
                    $('#pos-quick-customer-btn').trigger('click');
                    return;
                }

                if (e.altKey && e.key === '1') {
                    e.preventDefault();
                    focusSearchInput(true);
                    return;
                }

                if (e.altKey && e.key === '2') {
                    e.preventDefault();
                    $('#pos-tendered-input').focus().select();
                    return;
                }

                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    if (!$('#pos-checkout-btn').prop('disabled')) {
                        $('#pos-checkout-btn').trigger('click');
                    }
                    return;
                }

                if (!e.altKey && !e.ctrlKey && !e.metaKey && !isTypingContext() && (e.key === '+' || e.key === '=' || e.key === '-' || e.key === '_') && cart.length) {
                    e.preventDefault();
                    var lastIndex = cart.length - 1;

                    if (e.key === '+' || e.key === '=') {
                        cart[lastIndex].quantity += 1;
                        playFeedbackTone('ok');
                    } else {
                        if (cart[lastIndex].quantity > 1) {
                            cart[lastIndex].quantity -= 1;
                        } else {
                            cart.pop();
                        }
                    }

                    window.posCart = cart;
                    updateCartUI();
                    return;
                }

                if (!isTypingContext() && (e.key === 'ArrowRight' || e.key === 'ArrowLeft' || e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    e.preventDefault();
                    var step = 0;
                    if (e.key === 'ArrowRight') {
                        step = 1;
                    } else if (e.key === 'ArrowLeft') {
                        step = -1;
                    } else if (e.key === 'ArrowDown') {
                        step = getColumnCount();
                    } else if (e.key === 'ArrowUp') {
                        step = -1 * getColumnCount();
                    }

                    var nextIdx = keyboardSelectedProductIndex;
                    if (nextIdx < 0) {
                        nextIdx = 0;
                    } else {
                        nextIdx += step;
                    }

                    setKeyboardSelectedProduct(nextIdx, true);
                    return;
                }

                if (!isTypingContext() && e.key === 'Enter' && !barcodeBuffer.length) {
                    var $cards = getVisibleProductCards();
                    if ($cards.length) {
                        e.preventDefault();
                        if (keyboardSelectedProductIndex < 0) {
                            setKeyboardSelectedProduct(0, false);
                        }
                        var $selectedCard = $cards.eq(keyboardSelectedProductIndex >= 0 ? keyboardSelectedProductIndex : 0);
                        addProductToCart($selectedCard, 'keyboard');
                        return;
                    }
                }

                if (e.key === 'Delete' && !isTypingContext() && cart.length) {
                    e.preventDefault();
                    cart.pop();
                    window.posCart = cart;
                    updateCartUI();
                    return;
                }

                if (e.key === 'Escape' && $('#pos-search-input').val()) {
                    $('#pos-search-input').val('');
                    filterProducts('');
                    return;
                }

                if (isTypingContext()) {
                    return;
                }

                var nowTime = Date.now();
                if (barcodeLastTime && (nowTime - barcodeLastTime) > barcodeMaxGapMs) {
                    barcodeBuffer = '';
                }
                barcodeLastTime = nowTime;

                if (e.key === 'Enter') {
                    if (barcodeBuffer.length) {
                        processScannedBarcode(barcodeBuffer);
                    }
                    barcodeBuffer = '';
                    return;
                }

                if (e.key.length === 1) {
                    barcodeBuffer += e.key;
                }
            });

            $(document).on('click', '#pos-hold-btn', function() {
                if (!cart.length) {
                    return;
                }
                window.sessionStorage.setItem(heldBasketKey, JSON.stringify(cart));
                cart = [];
                window.posCart = cart;
                setResumeButtonVisibility();
                updateCartUI();
                focusSearchInput(false);
            });

            $(document).on('click', '#pos-resume-btn', function() {
                var heldRaw = window.sessionStorage.getItem(heldBasketKey);
                if (!heldRaw) {
                    setResumeButtonVisibility();
                    return;
                }

                try {
                    var heldCart = JSON.parse(heldRaw);
                    if (Array.isArray(heldCart)) {
                        cart = heldCart;
                        window.posCart = cart;
                    }
                } catch (err) {
                    // Ignore malformed held basket.
                }

                window.sessionStorage.removeItem(heldBasketKey);
                setResumeButtonVisibility();
                updateCartUI();
                focusSearchInput(false);
            });

            $(document).on('change', '.split-mode-check', function() {
                if (!isSplitMode) {
                    $('.split-mode-check').not(this).prop('checked', false);
                }
                userAdjustedSplit = false;
                applyPaymentMatrix(true);
                updateChangeDueState();
            });

            $(document).on('input', '.split-amount-input', function() {
                userAdjustedSplit = true;
                var editedModeId = String($(this).data('mode-id') || '');
                applyPaymentMatrix(false, editedModeId);
                updateChangeDueState();
            });

            $(document).on('click', '#pos-split-toggle-btn', function() {
                isSplitMode = !isSplitMode;
                userAdjustedSplit = false;
                updateSplitToggleUI();
                applyPaymentMatrix(true);
                updateChangeDueState();
            });

            $('#pos-discount-type, #pos-discount-value').on('change keyup input', function() {
                userAdjustedSplit = false;
                updateCartUI();
            });

            $('#pos-redeem-points').on('change keyup input', function() {
                userAdjustedSplit = false;
                updateCartUI();
            });

            $(document).on('click', '#pos-redeem-max', function() {
                $('#pos-redeem-points').val(String(availableRewardPoints));
                userAdjustedSplit = false;
                updateCartUI();
            });

            $('#pos-customer-select').on('change', function() {
                selectedCustomerId = parseInt($(this).val(), 10) || 0;
                refreshCustomerPoints();
            });

            $(document).on('click', '#pos-quick-customer-btn', function() {
                $('#pos-quick-customer-form')[0].reset();
                $('#pos-quick-customer-feedback').text('');
                $('#pos-quick-customer-save').prop('disabled', false).text('Save Customer');
                $('#pos-quick-customer-modal').modal('show');
                window.setTimeout(function() {
                    $('#pos-quick-customer-company').focus();
                }, 150);
            });

            $(document).on('submit', '#pos-quick-customer-form', function(e) {
                e.preventDefault();

                var company = $.trim($('#pos-quick-customer-company').val());
                var phone = $.trim($('#pos-quick-customer-phone').val());
                var email = $.trim($('#pos-quick-customer-email').val());

                if (!company) {
                    $('#pos-quick-customer-feedback').text('Customer name is required.');
                    $('#pos-quick-customer-company').focus();
                    return;
                }

                $('#pos-quick-customer-feedback').text('');
                $('#pos-quick-customer-save').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: quickCustomerUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        company: company,
                        phonenumber: phone,
                        email: email
                    },
                    success: function(response) {
                        if (response && response.success && response.customer) {
                            var customerId = String(response.customer.id);
                            var customerName = response.customer.name;

                            if (!$('#pos-customer-select option[value="' + customerId + '"]').length) {
                                $('#pos-customer-select').append('<option value="' + customerId + '">' + escapeHtml(customerName) + '</option>');
                            }

                            $('#pos-customer-select').val(customerId).trigger('change');
                            $('#pos-quick-customer-modal').modal('hide');
                            focusSearchInput(false);
                        } else {
                            $('#pos-quick-customer-feedback').text((response && response.message) ? response.message : 'Unable to create customer.');
                        }
                    },
                    error: function() {
                        $('#pos-quick-customer-feedback').text('Unable to create customer.');
                    },
                    complete: function() {
                        $('#pos-quick-customer-save').prop('disabled', false).text('Save Customer');
                    }
                });
            });

            $(document).on('click', '.denom-tile', function() {
                var value = parseFloat($(this).data('val')) || 0;
                $('#pos-tendered-input').val(formatMoney(value));
                updateChangeDueState();
            });

            $('#pos-tendered-input').on('keyup input', function() {
                updateChangeDueState();
            });

            $(document).on('click', '#pos-checkout-btn', function() {
                var $checkoutBtn = $(this);
                var checkoutSucceeded = false;

                if (!cart.length) {
                    return;
                }

                var tendered = parseFloat($('#pos-tendered-input').val()) || 0;
                var cashAllocated = getAllocatedCashAmount();
                var changeDue = tendered - cashAllocated;
                var splitSum = getSumOfCheckedSegments();
                var discountAmount = getDiscountAmount(getCartTotal());
                var redeemData = getRedeemAmount(Number((getCartTotal() - discountAmount).toFixed(2)));

                if (Math.abs(splitSum - dynamicTotal) >= 0.01 || (cashAllocated > 0 && changeDue < 0)) {
                    updateChangeDueState();
                    return;
                }

                var splitPayments = buildSplitPaymentsPayload();
                var selectedPaymentMode = splitPayments.length ? splitPayments[0].mode : '1';

                $checkoutBtn.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: checkoutUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        cart: JSON.stringify(cart),
                        payment_mode: selectedPaymentMode,
                        split_payments: JSON.stringify(splitPayments),
                        amount_tendered: formatMoney(tendered),
                        change_due: formatMoney(changeDue),
                        customer_id: selectedCustomerId,
                        discount_type: getDiscountType(),
                        discount_value: getDiscountValue(),
                        redeem_points: redeemData.points
                    },
                    success: function(response) {
                        if (response && response.success) {
                            checkoutSucceeded = true;

                            var soldItems = $.map(cart, function(item) {
                                return {
                                    id: item.id,
                                    name: item.name,
                                    rate: item.rate,
                                    quantity: item.quantity
                                };
                            });

                            $('#pos-receipt-print').empty().html(buildReceiptHtml(response, soldItems, {
                                tendered: tendered,
                                change: changeDue,
                                splitPayments: splitPayments,
                                discount: discountAmount,
                                redeem: redeemData.amount,
                                pointsEarned: response.points_earned || 0,
                                pointsRedeemed: response.points_redeemed || 0
                            }));
                            alert('Sale Saved! Invoice generated successfully.');
                            window.print();

                            cart = [];
                            window.posCart = cart;
                            $('#pos-tendered-input').val('');
                            $('#pos-change-display').text('0.00');
                            $('#pos-redeem-points').val('');
                            userAdjustedSplit = false;
                            refreshCustomerPoints();
                            updateCartUI();
                            focusSearchInput(false);
                        } else {
                            alert((response && response.message) ? response.message : 'Checkout failed.');
                        }
                    },
                    error: function() {
                        alert('An error occurred during checkout.');
                    },
                    complete: function() {
                        if (!checkoutSucceeded) {
                            $checkoutBtn.text('Proceed to Payment');
                            updateChangeDueState();
                        }
                    }
                });
            });

            setResumeButtonVisibility();
            selectedCustomerId = parseInt($('#pos-customer-select').val(), 10) || 0;
            if (!rewardsEnabled) {
                $('#pos-points-balance-box').addClass('text-muted').text('Rewards program is disabled in settings.');
                $('#pos-redeem-wrapper, #pos-redeem-summary').hide();
            }
            refreshCustomerPoints();
            updateSplitToggleUI();
            applyPaymentMatrix(true);
            updateCartUI();
            setKeyboardSelectedProduct(0, false);
            focusSearchInput(false);
        }

        if (window.jQuery) {
            bootPosCart(window.jQuery);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                if (window.jQuery) {
                    bootPosCart(window.jQuery);
                }
            });
        }
    })();
</script>
<?php init_tail(); ?>