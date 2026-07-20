<link rel="stylesheet" href="<?= asset('frontend/css/checkout.css') ?>">

<div class="stepper-wrap">
  <div class="stepper">
    <div class="step active"><div class="step-circle">1</div><span class="step-label">Address</span></div>
    <div class="step"><div class="step-circle">2</div><span class="step-label">Payment</span></div>
    <div class="step"><div class="step-circle">3</div><span class="step-label">Confirm</span></div>
  </div>
</div>

<div class="container">
  <form method="POST" action="<?= APP_URL ?>/checkout" id="checkoutForm"
        data-subtotal="<?= $summary['subtotal'] ?>" data-shipping="<?= $summary['shipping'] ?>"
        data-wallet-max="<?= (float)($walletBalance ?? 0) ?>"
        data-loyalty-points="<?= (int)($loyaltyBalance ?? 0) ?>" data-loyalty-value="<?= (float)($loyaltyRupeeValue ?? 0) ?>"
        onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
    <div class="checkout-layout">
      <!-- LEFT: Address + Payment -->
      <div>
        <!-- SAVED ADDRESSES -->
        <?php if(!empty($addresses)): ?>
        <div class="card">
          <div class="card-head"><span class="card-title">📍 Saved Addresses</span></div>
          <div class="card-body tight-bottom">
            <?php foreach($addresses as $a): ?>
            <div class="addr-card"
                 data-name="<?= e($a['recipient_name']) ?>" data-phone="<?= e($a['phone']) ?>"
                 data-addr="<?= e($a['address_line1']) ?>" data-city="<?= e($a['city']) ?>"
                 data-state="<?= e($a['state']) ?>" data-pin="<?= e($a['pincode']) ?>">
              <div class="selected-check">✓</div>
              <div class="addr-type"><?= e($a['label']) ?><?= $a['is_default']?' · Default':'' ?></div>
              <div class="addr-name"><?= e($a['recipient_name']) ?></div>
              <div class="addr-line"><?= e($a['address_line1'].', '.$a['city'].', '.$a['state'].' - '.$a['pincode']) ?></div>
              <div class="addr-phone">📞 <?= e($a['phone']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- SHIPPING FORM -->
        <div class="card">
          <div class="card-head"><span class="card-title">🏠 Delivery Address</span></div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="shipping_name" id="f_name" class="form-control" required placeholder="Recipient name" oninput="validateField(this)" onblur="validateField(this)"></div>
              <div class="form-group"><label class="form-label">Phone *</label><input type="tel" name="shipping_phone" id="f_phone" class="form-control" required placeholder="+91 98765 43210" oninput="validateField(this)" onblur="validateField(this)"></div>
              <div class="form-group full"><label class="form-label">Address *</label><input type="text" name="shipping_address" id="f_addr" class="form-control" required placeholder="House/Flat No., Street, Area" oninput="validateField(this)" onblur="validateField(this)"></div>
              <div class="form-group"><label class="form-label">City *</label><input type="text" name="shipping_city" id="f_city" class="form-control" required placeholder="City" oninput="validateField(this)" onblur="validateField(this)"></div>
              <div class="form-group"><label class="form-label">State *</label><input type="text" name="shipping_state" id="f_state" class="form-control" required placeholder="State" oninput="validateField(this)" onblur="validateField(this)"></div>
              <div class="form-group"><label class="form-label">Pincode *</label><input type="text" name="shipping_pincode" id="f_pin" class="form-control" required placeholder="600001" maxlength="6" pattern="\d{6}" data-pattern-message="Enter a valid 6-digit pincode." oninput="validateField(this)" onblur="validateField(this)"></div>
            </div>
          </div>
        </div>

        <!-- PAYMENT -->
        <div class="card">
          <div class="card-head"><span class="card-title">💳 Payment Method</span></div>
          <div class="card-body">
            <div class="payment-opts">
              <?php foreach([['cod','💰','Cash on Delivery','Pay when your order arrives'],['online','💳','Pay Online','UPI, Cards & Net Banking via secure gateway']] as [$v,$ic,$t,$s]): ?>
              <div class="payment-opt <?= $v==='cod'?'selected':'' ?>">
                <span class="po-icon"><?= $ic ?></span>
                <div><div class="po-title"><?= $t ?></div><div class="po-sub"><?= $s ?></div></div>
                <input type="radio" name="payment_method" value="<?= $v ?>" class="payment-radio visually-hidden" <?= $v==='cod'?'checked':'' ?>>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Summary -->
      <div>
        <div class="card sticky-summary">
          <div class="card-head"><span class="card-title">📋 Order Summary</span></div>
          <div class="card-body">
            <div class="summary-items-list">
              <?php foreach($summary['items'] as $item):
                $price = (float)($item['sale_price']?:$item['price']) + (float)($item['price_modifier']??0);
                $img = !empty($item['image']) ? '<img src="'.UPLOAD_URL.'/'.$item['image'].'" alt="'.e($item['product_name'] ?? $item['name'] ?? 'Product image').'">' : '<span>🛍️</span>';
              ?>
              <div class="summary-item">
                <div class="si-img"><?= $img ?></div>
                <div><div class="si-name"><?= e($item['name']) ?></div><div class="si-qty">Qty: <?= $item['quantity'] ?></div></div>
                <div class="si-price"><?= currency($price * $item['quantity']) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="summary-row"><span class="label">Taxable Value</span><span><?= currency($summary['taxable']) ?></span></div>
            <?php foreach($summary['gstBreakup'] as $gstRate => $gstAmt): ?>
            <div class="summary-row gst-row"><span class="label">GST @ <?= $gstRate ?>%</span><span><?= currency($gstAmt) ?></span></div>
            <?php endforeach; ?>
            <div class="summary-row"><span class="label">Subtotal (incl. GST)</span><span id="sumSubtotal"><?= currency($summary['subtotal']) ?></span></div>
            <div class="summary-row"><span class="label">Shipping</span><span class="text-green"><?= $summary['shipping']==0?'FREE':currency($summary['shipping']) ?></span></div>

            <div class="summary-row is-hidden" id="couponRow"><span class="label">Coupon Discount</span><span class="text-green">− <span id="couponAmt">₹0</span></span></div>
            <div class="summary-row is-hidden" id="giftRow"><span class="label">Gift Card</span><span class="text-green">− <span id="giftAmt">₹0</span></span></div>
            <div class="summary-row is-hidden" id="walletRow"><span class="label">Wallet</span><span class="text-green">− <span id="walletAmt">₹0</span></span></div>
            <div class="summary-row is-hidden" id="loyaltyRow"><span class="label">Loyalty Points</span><span class="text-green">− <span id="loyaltyAmt">₹0</span></span></div>

            <div class="discount-panel">
              <div class="discount-input-row">
                <input type="text" id="couponInput" placeholder="Coupon code">
                <button type="button" data-action="apply-coupon" class="btn-save">Apply</button>
              </div>
              <div id="couponMsg" class="discount-msg"></div>

              <div class="discount-input-row">
                <input type="text" id="giftInput" placeholder="Gift card code">
                <button type="button" data-action="apply-gift-card" class="btn-save">Apply</button>
              </div>
              <div id="giftMsg" class="discount-msg"></div>

              <?php if(($walletBalance ?? 0) > 0): ?>
              <label class="wallet-check-label">
                <input type="checkbox" id="useWallet"> Use wallet balance (<?= currency($walletBalance) ?> available)
              </label>
              <?php endif; ?>

              <?php if(!empty($loyaltyEnabled) && ($loyaltyBalance ?? 0) >= ($loyaltyMinRedeem ?? PHP_INT_MAX)): ?>
              <label class="wallet-check-label">
                <input type="checkbox" id="useLoyalty"> Redeem <?= $loyaltyBalance ?> loyalty points (worth <?= currency($loyaltyRupeeValue) ?>)
              </label>
              <?php endif; ?>
            </div>

            <input type="hidden" name="coupon_code" id="couponCodeField">
            <input type="hidden" name="gift_card_code" id="giftCodeField">
            <input type="hidden" name="wallet_amount" id="walletAmountField" value="0">
            <input type="hidden" name="loyalty_points" id="loyaltyPointsField" value="0">

            <div class="summary-row total"><span class="label">Total</span><span class="amount" id="sumTotal"><?= currency($summary['total']) ?></span></div>
            <button type="submit" class="btn-place" id="placeBtn">🎉 Place Order →</button>
            <div class="ssl-note">🔒 Secured by 256-bit SSL encryption</div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<?php $scripts = '<script src="'.asset('frontend/js/checkout.js').'"></script>'; ?>
