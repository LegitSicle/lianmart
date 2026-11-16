<?php
use app\models\database\Products;
use core\web\Viewer;

/** @var array<int, array> $products */
/** @var array<int, string> $categories */
/** @var string $editProduct */
/** @var ?Products $model */

$page = max(1, (int) ($page ?? 1));
$maxPageCount = (int) ceil(count($products) / 12);
?>
<header class="topbar">
    <form id="searchForm" method="get">
        <div class="search-container">
            <img class="search-icon" src="/lianmart/assets/images/admins/search.svg" width="20" height="20" alt="Search Icon">
            <input class="search-input" id="searchInput" type="text" placeholder="Search products..." aria-label="Search">
        </div>
    </form>
    <button id="add-product-btn" class="btn" command="show-modal" commandfor="add-product">+ Add Product</button>
</header>

<main class="content">
    <div class="title">
        <h1>Products</h1><span class="count"><?= safe_encode(count($products)) ?> products</span>
    </div>
    <div class="grid">
        <?php iterComponents("product-editable", $page, 12, $products) ?>
    </div>
    <?php echo Viewer::getComponents("pagination", ["page" => $page, "maxPageCount" => $maxPageCount]) ?>
</main>

<dialog id="add-product" class="modal"<?= isset($editProduct) ? ' data-edit-product="true"' : "" ?><?= isset($model) && $model->hasAnyError() ? ' data-reopen="true"' : "" ?>>
    <div class="modal-content">
        <div class="modal-header">
            <h2><span id="editType"><?= isset($editProduct) ? "Edit" : "Add" ?></span> Product</h2>
            <img class="close-dialog-button" src="/lianmart/assets/images/admins/close.svg" width="30" height="30" alt="Close Icon">
        </div>
        <form class="modal-form" method="post" enctype="multipart/form-data">
            <?php if (isset($editProduct)): ?>
                <input type="hidden" name="productId" value="<?= safe_encode($editProduct) ?>">
            <?php endif; ?>
            <div class="drag-n-drop-container">
                <label id="drop-zone" data-image-path="<?= safe_encode($productImagePath ?? "") ?>">
                    <img id="modal-cloud-icon" src="/lianmart/assets/images/admins/upload.svg" width="50" height="50" alt="Cloud Icon">
                    <p class="change-to-white">Drag and drop an image here or click to upload</p>
                    <input type="file" id="file-input" name="productImages" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                    <p class="drag-n-drop-gray-text">PNG or JPG | Max 30MB</p>
                </label>
            </div>
            
            <div class="product-name-container">
                <label for="product-name">Product Name <span class="red-asterisk">*</span></label>
                <input type="text" id="product-name" name="productName" placeholder="Enter Product Name" <?= isset($model) ? "value=\"{$model->productName}\"" : "" ?> required>
                <?php if (isset($model) && $model->hasError("productName")): ?>
                    <p><?= $model->getFirstError("productName") ?></p>
                <?php endif; ?>
            </div>

            <div class="product-description-container">
                <label for="description">Description</label>
                <textarea id="description" name="productDetails" placeholder="Enter Product Description..."><?= isset($model) ? $model->productDetails : "" ?></textarea>
            </div>

            <div class="category-price-container">
                <div class="category-select-container">
                    <label for="product-category">Category <span class="red-asterisk">*</span></label>
                    <select id="categories" name="categoryId" required>
                        <option value="" disabled hidden<?= !isset($model) ? " selected" : "" ?>>Select Category</option>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= $id ?>"<?= isset($model) && $model->categoryId === $id ? " selected" : "" ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="price-container">
                    <label for="product-price">Selling Price <span class="red-asterisk">*</span></label>
                    <div class="price-input-with-currency-container">
                        <span class="currency-symbol">&#8369;</span>
                        <input type="number" id="product-price" name="productPrice" min="0" placeholder="0.00" step="0.01" <?= isset($model) ? "value=\"{$model->productPrice}\"" : "" ?>  required>
                    </div>
                </div>
            </div>

            <div class="product-stock-container">
                <label for="product-stock">Stock Quantity <span class="red-asterisk">*</span></label>	
                <input type="number" id="product-stock" name="stock" placeholder="Enter Quantity" min="0" <?= isset($model) ? "value=\"{$model->stock}\"" : "" ?>  required>
            </div>
            
            <div class="modal-footer">
                <?php if (isset($editProduct)): ?>
                    <button type="submit" name="action" value="delete" class="delete-dialog-button-style">Delete</button>
                <?php endif; ?>
                <button type="button" class="close-dialog-button-style">Cancel</button>
                <button type="submit" name="action" value="save" class="dialog-add-product-btn">Save <span id="saveType"><?= isset($editProduct) ? "Changes" : "Product" ?></span></button>
            </div>
        </form>
    </div>
</dialog>
<script src="/lianmart/assets/js/edit-products.js"></script>
<script src="/lianmart/assets/js/search.js"></script>
<script src="/lianmart/assets/js/pagination.js"></script>
<script src="/lianmart/assets/js/admin-products.js"></script>