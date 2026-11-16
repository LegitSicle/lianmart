<?php
/** @var string $productName */
/** @var string $productDetails */
/** @var float $productPrice */
/** @var int $stock */
/** @var int $id */

$stockStatus = $stock <= 0 ? "Out of Stock" : "In Stock";
$imagePath ??= "/lianmart/assets/images/logo.svg";
?>

<div class="parent-container">
    <div class="images-container">
        <p class="location-header">Catalog / Product Details</p>
        <div class="primary-image-container product-image-container">
            <img width="426" height="340" src="<?= safe_encode($imagePath) ?>">
        </div>
    </div>
    <div class="texts-container">
        <div class="product-title-and-status-container">
            <h1 class="product-title"><?= safe_encode($productName) ?></h1>
            <div class="product-status-container">
                <p class="product-status"><?= safe_encode($stockStatus) ?></p>
            </div>
        </div>
        <div class="product-price-container">
            <p class="product-price">&#8369;<?= safe_encode($productPrice) ?></p>
        </div>
        <div class="product-description-container">
            <p class="product-description"><?= safe_encode($productDetails) ?></p>
            <p class="stock-number"><?= safe_encode($stock) ?> in stock</p>
        </div>
        <?php if ($stock > 0): ?>
            <form method="post" class="actions-container">
                <input type="hidden" name="productId" value="<?= safe_encode($id) ?>">
                <div class="amount-action-container">
                    <button type="button" class="amount-action-button minus-button">-</button>
                    <input 
                    class="amount-input"
                    value="1"
                    type="number" 
                    name="quantity"
                    min="1"
                    max="<?= safe_encode($stock) ?>"
                    />
                    <button type="button" class="amount-action-button plus-button">+</button>
                </div>
                <div class="add-to-cart-button-container">
                    <button type="submit" name="action" value="add" class="add-to-cart-button">
                        <p>Add to Cart</p>
                        <img src="/lianmart/assets/images/cart_white.svg" width="20px" height="20px">
                    </button>
                </div>
                <div class="buy-now-button-container">
                    <button type="submit" name="action" value="buy" class="buy-now-button">Buy Now</button>
                </div>
            </form>
        <?php else: ?>
            <div class="actions-container">
                <h2 class="out-of-stock">Out of Stock</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="/lianmart/assets/js/product-details.js"></script>