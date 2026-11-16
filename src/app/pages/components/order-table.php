<?php
/** @var int $orderId */
/** @var string $customerName */
/** @var string $orderStatus */
/** @var string $address */
/** @var float $total */
/** @var string $created_at */
$date = DateTime::createFromFormat('Y-m-d', $created_at);
?>
<tr>
    <td>
        <span class="user-cell">
            <span>#<?= safe_encode($orderId) ?></span>
        </span>
    </td>
    <td><?= safe_encode($customerName) ?></td>
    <td>&#8369;<?= safe_encode($total) ?></td>
    <td><span class="status status-<?= safe_encode(strtolower($orderStatus)) ?>"><?= safe_encode($orderStatus) ?></span></td>
    <!--
        status-completed ("Completed"): green
        status-processing ("Processing"): yellow
        status-pending ("Pending"): orange
        status-cancelled ("Cancelled"): red
    -->
    <td id="td-address"><?= safe_encode($address) ?></td>
    <td><time datetime="<?= safe_encode($created_at) ?>"><?php echo $date->format("F j, Y") ?></time></td>
</tr>