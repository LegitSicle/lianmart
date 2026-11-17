<?php
/** @var int $userId */
/** @var string $firstname */
/** @var string $lastname */
/** @var string $email */
/** @var string $created_at */
/** @var string $role */
/** @var string $status */
$date = DateTime::createFromFormat('Y-m-d', $created_at);
$buttonClass = $status === "Disabled" ? "edit" : "delete";
$buttonAction = $status === "Disabled" ? "unban" : "ban";
?>
<tr>
    <td>
        <span class="user-cell">
            <!-- <span class="user-avatar" aria-hidden="true"></span> -->
            <span class="user-name"><?= safe_encode("{$firstname} {$lastname}") ?></span>
        </span>
    </td>
    <td><?= safe_encode($email) ?></td>
    <td><?= safe_encode($role) ?></td>
    <td><span class="status status-<?= safe_encode(strtolower($status)) ?>"><?= safe_encode($status) ?></span></td>
    <td><time datetime="<?= safe_encode($created_at) ?>"><?php echo $date->format("F j, Y") ?></time></td>
    <td>
        <form method="post">
            <input type="hidden" name="userId" value="<?= safe_encode($userId) ?>">
            <button type="submit" name="action" value="<?= $buttonAction ?>" class="icon-btn icon-btn-<?= $buttonClass ?>"<?= $role === "Admin" ? " disabled" : "" ?>><?= ucfirst($buttonAction) ?></button>
        </form>
    </td>
</tr>