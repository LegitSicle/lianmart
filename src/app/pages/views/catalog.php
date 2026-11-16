<?php
use core\web\Viewer;

/** @var array $categories */
/** @var array $products */
/** @var string $avails */
/** @var string $sort */
/** @var string $cats */
/** @var array $cartedProducts */
$currentMin = isset($minPrice) && isset($maxPrice) && $minPrice <= $maxPrice - 10 ? $minPrice : 0;
$currentMax = isset($minPrice) && isset($maxPrice) && $maxPrice >= $minPrice + 10 ? $maxPrice : 550;
$trackerMin = round(($currentMin / 550) * 100);
$trackerMax = round(100 - (($currentMax / 550) * 100));
$page = max(1, (int) ($page ?? 1));
$maxPageCount = (int) ceil(count($products) / 20);

function createEmptyNotice(): string {
    return "<div class=\"empty\"><p>No products match your filters.</p></div>";
}

function isChecked(string $value, ?array $availability): string {
    return isset($availability) && in_array($value, $availability) ? "checked" : "";
}

function isSelected(string $value, ?string $sort): string {
    return isset($sort) && $sort === $value ? "selected" : "";
}
?>
<div class="main-content">
    <section class="top-section">
        <label for="sort">Sort by:</label>
        <select id="sort">
            <option value="new" <?= isSelected("new", $sort ?? "") ?>>Newest</option>
            <option value="asc" <?= isSelected("asc", $sort ?? "") ?>>Price: Low to High</option>
            <option value="desc" <?= isSelected("desc", $sort ?? "") ?>>Price: High to Low</option>
            <option value="az" <?= isSelected("az", $sort ?? "") ?>>Name: A to Z</option>
        </select>
    </section>
    <div class="filter-and-product">
        <aside class="filter">
            <h2>Filter Options</h2>
            <hr>
            <form method="get" id="filterForm">
                <h3>Categories</h3>
                <div class="checkboxes">
                    <?php foreach ($categories as $category): ?>
                    <?php
                    $categoryName = safe_encode($category["categoryName"]);
                    $categoryId = safe_encode($category["categoryId"]);
                    $isSelected = isset($cats) && in_array($categoryId, explode(",", $cats ?? "")) ? "checked" : "";
                    ?>
                    <label class="cats" for="<?= $categoryName ?>">
                        <input type="checkbox" name="cats" id="<?= $categoryName ?>" value="<?= $categoryId ?>" <?= $isSelected ?>>
                        <?= $categoryName ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <hr>
                <h3>Price Range</h3>
                <p class="price-label">&#8369; <span id="min"><?= $currentMin ?></span> - &#8369; <span id="max"><?= $currentMax ?></span></p>
                <div class="range">
                    <div class="track"></div>
                    <div id="track" class="track in-between" style="left: <?= "{$trackerMin}%" ?>; right: <?= "{$trackerMax}%" ?>;"></div>
                    <input type="range" name="minPrice" id="low" min="0" max="550" value="<?= $currentMin ?>" data-default="0">
                    <input type="range" name="maxPrice" id="high" min="0" max="550" value="<?= $currentMax ?>" data-default="550">
                </div>
                <hr>
                <h3>Availability</h3>
                <div class="checkboxes">
                    <label for="in-stock"><input type="checkbox" name="avails" value="in" id="in-stock" <?= isChecked("in", explode(",", $avails ?? "")) ?>> In Stock</label>
                    <label for="out-of-stock"><input type="checkbox" name="avails" value="out" id="out-of-stock" <?= isChecked("out", explode(",", $avails ?? "")) ?>> Out of Stock</label>
                </div>
                <button type="submit" class="styled-button primary">Filter</button>
            </form>
        </aside>
        <div class="product-and-pager">
            <form method="post">
                <section class="product-container-grid">
                        <?php
                        if (empty($products)) {
                            echo createEmptyNotice();
                        } else {
                            $iter = $maxPageCount === $page ? count($products) - (20 * ($maxPageCount - 1)) : 20;
                            for ($i = 0; $i < $iter; $i++) { 
                                echo Viewer::getComponents("product-display", [
                                    "cartedProducts" => $cartedProducts,
                                    ...$products[(($page - 1) * 20) + $i]
                                ]);
                            }
                        }
                        ?>
                </section>
            </form>
            <?php echo Viewer::getComponents("pagination", ["page" => $page, "maxPageCount" => $maxPageCount]) ?>
        </div>
    </div>
</div>
<script src="/lianmart/assets/js/filters.js"></script>
<script src="/lianmart/assets/js/search.js"></script>
<script src="/lianmart/assets/js/pagination.js"></script>
<script src="/lianmart/assets/js/price-slider.js"></script>
