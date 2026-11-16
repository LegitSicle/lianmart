<?php
use core\web\Viewer;
$url ??= "home"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/lianmart/assets/styles/main.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/color-vars.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/footer.css">
    <link rel="stylesheet" href="<?= safe_url("/lianmart/assets/styles/{$url}.css") ?>">
    <link rel="icon" href="/lianmart/assets/images/favicon.svg" type="image/svg+xml">
    <title><?= safe_encode($title ?? "LianMart") ?></title>
</head>
<body>
    <main>
        {{content}}
    </main>
    <?php echo Viewer::getComponents("footer") ?>
</body>
</html>