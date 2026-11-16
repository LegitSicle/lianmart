<?php
function calculateGrowth(float|int|null $now, float|int|null $last): float {
    if ($now == 0 || $now === null) {
        return 0;
    } else if ($last == 0 || $last === null) {
        return 100;
    } else {
        return ($now - $last) / $last;
    }
}

/** @var int $totalProducts */
/** @var float $totalRevenue */
/** @var int $totalOrders */
/** @var int $totalCustomers */
/** @var int $lastTotalProducts */
/** @var float $lastTotalRevenue */
/** @var int $lastTotalOrders */
/** @var int $lastTotalCustomers */
/** @var int $daysPassed */

$productGrowth = calculateGrowth($totalProducts, $lastTotalProducts);
$revenueGrowth = calculateGrowth($totalRevenue, $lastTotalRevenue);
$orderGrowth = calculateGrowth($totalOrders, $lastTotalOrders);
$customersGrowth = calculateGrowth($totalCustomers, $lastTotalCustomers);
?>
<main class="content">
    <div class="page-head">
        <h1 class="page-title">Finances</h1>
        <select id="headInfo" class="select">
            <option value="1" selected>This Week</option>
            <option value="2">This Month</option>
            <option value="3">This Year</option>
        </select>
    </div>

    <section class="stats">
        <article class="stat-card">
            <h2>Total Products Added</h2>
            <div class="stat-body">
                <div id="totalProducts" class="stat-value"><?= safe_encode($totalProducts) ?></div>
                <div id="productGrowth" class="stat-change<?= $productGrowth > 0 ? "" : " down" ?>">
                    <?php echo $productGrowth > 0 ? "&uarr;" : "&darr;" ?><?= safe_encode(number_format(abs($productGrowth), 2)) ?>%
                </div>
                <div class="stat-period">Last <span class="timespan"><?= safe_encode($daysPassed) ?> days</span></div>
            </div>
        </article>
        <article class="stat-card">
            <h2>Total Revenue</h2>
            <div class="stat-body">
                <div class="stat-value">&#8369;<span id="totalRevenue"><?= safe_encode($totalRevenue ?? 0) ?></span></div>
                <div id="revenueGrowth" class="stat-change<?= $revenueGrowth > 0 ? "" : " down" ?>">
                    <?php echo $revenueGrowth > 0 ? "&uarr;" : "&darr;" ?><?= safe_encode(number_format(abs($revenueGrowth), 2)) ?>%
                </div>
                <div class="stat-period">Last <span class="timespan"><?= safe_encode($daysPassed) ?> days</span></div>
            </div>
        </article>
        <article class="stat-card">
            <h2>Total Orders</h2>
            <div class="stat-body">
                <div id="totalOrders" class="stat-value"><?= safe_encode($totalOrders) ?></div>
                <div id="orderGrowth" class="stat-change<?= $orderGrowth > 0 ? "" : " down" ?>">
                    <?php echo $orderGrowth > 0 ? "&uarr;" : "&darr;" ?><?= safe_encode(number_format(abs($orderGrowth), 2)) ?>%
                </div>
                <div class="stat-period">Last <span class="timespan"><?= safe_encode($daysPassed) ?> days</span></div>
            </div>
        </article>
        <article class="stat-card">
            <h2>Customers</h2>
            <div class="stat-body">
                <div id="totalCustomers" class="stat-value"><?= safe_encode($totalCustomers) ?></div>
                <div id="customerGrowth" class="stat-change<?= $customersGrowth > 0 ? "" : " down" ?>">
                    <?php echo $customersGrowth > 0 ? "&uarr;" : "&darr;" ?><?= safe_encode(number_format(abs($customersGrowth), 2)) ?>%
                </div>
                <div class="stat-period">Last <span class="timespan"><?= safe_encode($daysPassed) ?> days</span></div>
            </div>
        </article>
    </section>

    <section class="charts">
        <article class="chart-card sales">
            <div class="card-head">
                <h2>Sales Overview</h2>
                <select id="salesOverview" class="select">
                    <option value="1" selected>Monthly</option>
                    <option value="2">Weekly</option>
                    <option value="3">Daily</option>
                </select>
            </div>
            <div class="bar-chart-container">
                <canvas id="bar-chart"></canvas>
            </div>
        </article>

        <article class="chart-card status">
            <div class="card-head">
                <h2>Order Status</h2>
                <select id="ordersMade" class="select">
                    <option value="1" selected>This Week</option>
                    <option value="2">This Month</option>
                    <option value="3">This Year</option>
                </select>
            </div>
            <canvas id="doughnut-chart"></canvas>
        </article>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.6.1/dist/chart.umd.min.js"></script>
<script src="/lianmart/assets/js/admin-finances.js"></script>