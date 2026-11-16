<?php
/** @var array $cartedProducts */
/** @var ?string $imagePath */
/** @var string $productName */
/** @var float $productPrice */
/** @var int $productId */
/** @var int $stock */

if (in_array($productId, $cartedProducts)) {
    $isDisabled = "disabled";
    $style = " carted";
    $available = "Already Added";
} else if ($stock === 0) {
    $isDisabled = "disabled";
    $style = " disabled";
    $available = "Out of Stock";
} else {
    $isDisabled = "";
    $style = "";
    $available = "Add to Cart";
}
?>
<article class="product-card card-display">
    <div class="product-thumbnail thumbnail-display">
        <img src="<?= safe_url($imagePath ?? "/lianmart/assets/images/logo.svg") ?>" alt="thumbnail" class="thumbnail">
    </div>
    <div class="product-content">
        <a href="/lianmart/product-details?id=<?= safe_encode($productId) ?>"><span class="name truncated-text display-info"><?= safe_encode($productName) ?></span></a>
        <p class="price display-info">&#8369;<?= safe_encode($productPrice) ?></p>
        <button type="submit" name="productId" value="<?= safe_encode($productId) ?>" class="styled-button primary add<?= $style ?>" <?= $isDisabled ?>><?= $available ?></button>
    </div>
</article>