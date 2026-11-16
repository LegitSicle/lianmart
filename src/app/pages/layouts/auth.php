<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/lianmart/assets/styles/modal.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/components.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/auth.css">
    <link rel="stylesheet" href="/lianmart/assets/styles/form.css">
    <link rel="icon" href="/lianmart/assets/images/favicon.svg" type="image/svg+xml">
    <title><?= safe_encode($title ?? "LianMart") ?></title>
</head>
<body>
    <dialog class="modal">
        <h1>Are you sure?</h1>
        <p>Returning home will result in all provided information on the forms to be removed.</p>
        <form method="post">
            <input type="hidden" name="form_action" value="return_confirmed">
            <div class="two-fields">
                <button type="submit" class="styled-button other-option">Return</button>
                <button type="button" class="styled-button primary" onclick="closeModal()">Stay</button>
            </div>
        </form>
    </dialog>
    <div class="left-info">
        <button class="return" onclick="openModal()">&larr; Back to LianMart</button>
        <div class="left-content">
            <img width="500" src="/lianmart/assets/images/logo_white.svg" alt="logo">
            <p>Continue checkout without starting over.</p>
            <p>Review balances and payment updates in one place.</p>
            <p>Track items and shipments from a single account.</p>
        </div>
    </div>
    <div class="right-form">
        {{content}}
    </div>
    <script src="/lianmart/assets/js/modal.js"></script>
</body>
</html>