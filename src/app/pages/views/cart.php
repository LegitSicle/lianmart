<?php
use core\web\Viewer;

/** @var array $cartItems */
/** @var float $subtotal */
$totalQuantity = array_sum(array_column($cartItems, "quantity"));
$checkOutDisabled = empty($cartItems) ? " disabled" : "";
?>
<form method="post">
    <div class="cart">
        <section aria-label="Cart items">
            <div class="select-bar">
                <label>
                    <input type="checkbox" class="checkbox" id="selectAll" data-container="items">
                    <span>Select All ( <span id="totalItems"><?= count($cartItems) ?></span> ITEM(S) )</span>
                </label>
                <button type="submit" name="action" value="delete" class="delete-btn">
                    <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M2 2l12 12M14 2L2 14"/></svg>
                    Delete
                </button>
            </div>
            
            <div class="items" id="items">
                <?php
                foreach ($cartItems as $cartItem) {
                    echo Viewer::getComponents("cart-card", $cartItem);
                }
                ?>
            </div>
        </section>
        <aside class="summary-wrap" aria-label="Order summary">
            <div class="summary">
                <h2>Order Summary</h2>
                
                <div class="row"><span>Subtotal (<span id="totalQuantity"><?= safe_encode($totalQuantity) ?></span>)</span><span id="subtotalItems">&#8369;<?= number_format(safe_encode($subtotal), 2) ?></span></div>
                <div class="row"><span>Shipping Fee</span><span id="shipping">&#8369;0.00</span></div>
                
                <div class="voucher">
                    <input type="text" id="voucherInput" placeholder="Enter voucher code" aria-label="Voucher code">
                    <button type="button" class="btn">Apply</button>
                </div>
                
                <div class="row"><span>Total</span><span id="total">&#8369;0.00</span></div>
                
                <button type="submit" name="action" value="checkout" class="btn checkout" id="checkoutBtn"<?= $checkOutDisabled ?>>Proceed to checkout</button>
            </div>
        </aside>
    </div>
</form>
<script src="/lianmart/assets/js/check-all.js"></script>
<script src="/lianmart/assets/js/cart-sync.js"></script>
