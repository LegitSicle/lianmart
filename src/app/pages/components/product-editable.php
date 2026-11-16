<?php
/** @var string $imagePath */
/** @var int $productId */
/** @var string $productName */
/** @var float $productPrice */
/** @var int $stock */
/** @var string $categoryName */
/** @var string $created_at */
/** @var array $categories */
?>
<article class="card">
    <div class="ph img" role="img" aria-label="Product image placeholder">
        <img class="thumbnail" src="<?= safe_url($imagePath ?? "/lianmart/assets/images/logo.svg") ?>" alt="thumbnail">
    </div>
    <h2><?= safe_encode($productName) ?></h2>
    <div class="price">&#8369;<?= safe_encode(number_format($productPrice, 2)) ?></div>
    <div class="stock"><i></i><?= safe_encode($stock) ?> in stock</div>
    <span class="tag"><?= safe_encode($categoryName) ?></span>
    <div class="foot">
        <span>Added on: <?= safe_encode($created_at) ?></span>
        <button class="edit-btn" onclick="openEdit('/lianmart/admin/products', <?= safe_encode($productId) ?>)"><img src="/lianmart/assets/images/admins/three-dots.svg" class="dots" width="20" height="20" alt="Three Dots Icon"></button>
    </div>
</article>