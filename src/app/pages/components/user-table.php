<?php
/** @var string $firstname */
/** @var string $lastname */
/** @var string $email */
/** @var string $created_at */
/** @var string $role */
/** @var string $status */
$date = DateTime::createFromFormat('Y-m-d', $created_at);
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
    <td class="col-actions">
        <div class="actions">
            <button type="button" class="icon-btn icon-btn-edit" aria-label="Edit Long Name N. Random Sr.">
                <img src="/lianmart/assets/images/admins/edit-pencil.svg" width="22" height="22" alt="Edit Icon">
            </button>
            <button type="button" class="icon-btn icon-btn-delete" aria-label="Delete Long Name N. Random Sr.">
                <img src="/lianmart/assets/images/admins/delete-trash.svg" width="22" height="22" alt="Delete Icon">
            </button>
        </div>
    </td>
</tr>