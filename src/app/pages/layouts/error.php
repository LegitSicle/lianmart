<?php
/** @var int $code */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/lianmart/assets/styles/<?= safe_encode("_{$code}" ?? "_404") ?>.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/footer.css">
    <link rel="icon" href="/lianmart/assets/images/favicon.svg" type="image/svg+xml">
    <title><?= safe_encode($error ?? "404 | Not Found!") ?></title>
</head>
<body>
    <main class="main-wrapper">
        {{content}}
    </main>
</body>
</html>