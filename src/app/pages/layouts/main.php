<?php
use core\web\Viewer;

function shouldBeActive(string $link, ?string $url): string {
    return isset($url) && $url === $link ? "active" : "";
}

function shouldBeLinked(string $link): bool {
    return isset($url) && $url === $link;
}

function shouldBeShown(?bool $flag): string {
    return isset($flag) && $flag ? "show" : "hide";
}
$url ??= "home"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/lianmart/assets/styles/main.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/color-vars.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/header.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/footer.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/components.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/product.css">
    <link rel="stylesheet" href="<?= safe_url("/lianmart/assets/styles/{$url}.css") ?>">
    <link rel="icon" href="/lianmart/assets/images/favicon.svg" type="image/svg+xml">
    <title><?= safe_encode($title ?? "LianMart") ?></title>
</head>
<body>
    <header>
        <div class="top-container">
            <img width="200" src="/lianmart/assets/images/logo.svg" alt="logo">
            <form action="/lianmart/catalog" method="get" id="searchForm" class="search">
                <input id="searchInput" type="search" name="search" placeholder="Search for products...">
            </form>
            <div class="top-icons">
                <a href="/lianmart/wishlist"><img src="/lianmart/assets/images/header/heart.svg" alt="heart"></a>
                <a href="/lianmart/cart"><img src="/lianmart/assets/images/header/cart.svg" alt="cart"></a>
                <a href="/lianmart/profile">
                    <div class="username">
                        <img src="/lianmart/assets/images/header/user.svg" alt="user">
                        <strong><?= safe_encode($username ?? "Guest") ?></strong>
                    </div>
                </a>
                <?php if ($isAdmin ?? false): ?>
                    <a href="/lianmart/admin/orders"><strong>Admin</strong></a>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <div class="nav-wrapper">
            <nav>
                <ul>
                    <li><a href="/lianmart/home" class="<?= shouldBeActive("home", $url) ?>">Home</a></li>
                    <li><a href="/lianmart/catalog" class="<?= shouldBeActive("catalog", $url) ?>">Catalog</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        {{content}}
    </main>
    <?php echo Viewer::getComponents("footer") ?>
</body>
</html>