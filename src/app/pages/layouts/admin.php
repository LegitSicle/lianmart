<?php
$url ??= "home"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="/lianmart/assets/styles/components.css">
	<link rel="stylesheet" href="<?= safe_url("/lianmart/assets/styles/{$url}.css") ?>">
    <link rel="icon" href="/lianmart/assets/images/favicon.svg" type="image/svg+xml">
    <title>Admin <?= $section ?? "" ?> - LianMart</title>
</head>
<body>
    <div class="app">
		<aside class="sidebar">
			<div class="brand">
				<img src="/lianmart/assets/images/logo.svg" width="120" height="120" alt="LianMart Logo">
				<img src="/lianmart/assets/images/admins/sidebar.svg" width="20" height="20" alt="Collapse Icon">
			</div>
			<ul class="nav" style="margin-top:0">
				<a href="/lianmart/admin/orders"><li>
					<img src="/lianmart/assets/images/admins/bullet-list.svg" width="20" height="20" alt="Orders Icon"><p>Orders</p>
                </li></a>
				<a href="/lianmart/admin/products"><li>
					<img src="/lianmart/assets/images/admins/shopping-bag.svg" width="20" height="20" alt="Products Icon"><p>Products</p>
				</li></a>
				<a href="/lianmart/admin/users"><li>
					<img src="/lianmart/assets/images/admins/person.svg" width="20" height="20" alt="Users Icon"><p>Users</p>
				</li></a>
				<a href="/lianmart/admin/finances"><li>
					<img src="/lianmart/assets/images/admins/finance.svg" width="20" height="20" alt="Finances Icon"><p>Finances</p>
				</li></a>
			</ul>
			<div class="system">
				<ul class="nav">
					<a href="/lianmart/logout"><li>
						<img src="/lianmart/assets/images/admins/logout.svg" width="25" height="25" alt="Log Out Icon">
						<p>Log Out</p>
					</li></a>
				</ul>
			</div>
		</aside>

		<div class="main">
            {{content}}
		</div>
	</div>
</body>
</html>