<?php
use core\web\Viewer;
/** @var array $orders */

$page = max(1, (int) ($page ?? 1));
$maxPageCount = (int) ceil(count($orders ?? []) / 15);
?>

<header class="topbar">
    <form method="get" id="searchForm">
        <div class="search-container">
            <img class="search-icon" src="/lianmart/assets/images/admins/search.svg" width="20" height="20" alt="Search Icon">
            <input class="search-input" id="searchInput" type="text" name="search" placeholder="Search orders..." aria-label="Search">
        </div>
    </form>
</header>

<main class="content">
    <div class="users-table-wrap">
        <table class="users-table">
            <caption class="users-sr-only">List of users</caption>
            <colgroup>
                <col class="c-order-id">
                <col class="c-customer">
                <col class="c-amount">
                <col class="c-status">
                <col class="c-address">
                <col class="c-date">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Order ID</p>
                            <button type="submit" class="sort-btn" aria-label="Sort by Order ID">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Customer</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Customer">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Amount</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Amount">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Status</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Status">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Address</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Address">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Date</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Date">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php iterComponents("order-table", $page, 11, $orders) ?>
            </tbody>
        </table>
    </div>
    <?php echo Viewer::getComponents("pagination", ["page" => $page, "maxPageCount" => $maxPageCount]) ?>
</main>
<script src="/lianmart/assets/js/search.js"></script>
<script src="/lianmart/assets/js/pagination.js"></script>