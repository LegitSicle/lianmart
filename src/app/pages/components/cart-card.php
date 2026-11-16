<?php 
/** @var ?string $imagePath */
/** @var int $productId */
/** @var string $productName */
/** @var string $productDetails */
/** @var float $productPrice */
/** @var int $quantity */
/** @var int $stock */
?>
<article class="item" data-price="150" data-original="200">
    <input type="checkbox" name="products[]" value="<?= safe_encode($productId) ?>" class="checkbox item-check" aria-label="Select item">
    <div class="thumb" role="img" aria-label="Product image"><img width="130" height="130" src="<?= safe_url($imagePath ?? "/lianmart/assets/images/logo.svg") ?>" alt="thumbnail"></div>
    <div class="info">
        <h2><?= safe_encode($productName) ?></h2>
        <p class="variant"><?= safe_encode($productDetails) ?></p>
    </div>
    <div class="price-qty">
        <div class="price">
            <!-- <span class="old"></span><br> -->
            <span class="now">&#8369;<?= safe_encode($productPrice) ?></span>
            <!-- <span class="label">Discounted Price</span> -->
        </div>
        <div class="qty">
            <button type="button" class="minus" aria-label="Decrease quantity">-</button>
            <input type="number" class="qty-input" data-id="<?= safe_encode($productId) ?>" value="<?= safe_encode($quantity) ?>" min="1" max="<?= safe_encode($stock) ?>">
            <button type="button" class="plus" aria-label="Increase quantity">+</button>
        </div>
    </div>
</article>