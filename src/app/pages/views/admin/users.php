<?php
use core\web\Viewer;
/** @var array $users */

$page = max(1, (int) ($page ?? 1));
$maxPageCount = (int) ceil(count($users) / 15);

?>
<header class="topbar">
    <form id="searchForm">
        <div class="search-container">
            <img class="search-icon" src="/lianmart/assets/images/admins/search.svg" width="20" height="20" alt="Search Icon">
            <input class="search-input" id="searchInput" type="text" placeholder="Search users..." aria-label="Search">
        </div>
    </form>
    <div class="sort-container">
        <div class="role-container">
            <img id="role-icon" src="/lianmart/assets/images/admins/person.svg" width="20" height="20" alt="Role Icon">
            <select for="role-select" id="role-select">
                <option selected disabled hidden value="">Role</option>
                <option value="all">All</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
        </div>
        <div class="status-container">
            <img id="status-icon" src="/lianmart/assets/images/admins/circle-status.svg" width="20" height="20" alt="Status Icon">
            <select for="status-select" id="status-select">
                <option selected disabled hidden value="">Status</option>
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="disabled">Disabled</option>
            </select>
        </div>
        <div class="date-container">
            <label for="date-input">Joined After:</label>
            <input id="date-input" type="date" aria-label="Filter by Joined Date" value="2026-11-08">
        </div>
    </div>
</header>

<main class="content">
    <div class="users-table-wrap">
        <table class="users-table">
            <caption class="users-sr-only">List of users</caption>
            <colgroup>
                <col class="c-name">
                <col class="c-email">
                <col class="c-role">
                <col class="c-status">
                <col class="c-joined">
                <col class="c-actions">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Full Name</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Full Name">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Email</p>
                            <button type="button" class="sort-btn" aria-label="Sort by Email">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                        </div>
                    </th>
                    <th scope="col">
                        <p>Role</p>
                    </th>
                    <th scope="col">
                        <p>Status</p>
                    </th>
                    <th scope="col">
                        <div class="th-inner">
                            <p>Joined Date</p>
                                <button type="button" class="sort-btn" aria-label="Sort by Joined Date">
                                <img width="12" height="16" src="/lianmart/assets/images/admins/sort.svg" alt="sort">
                            </button>
                            </div>
                    </th>
                    <th scope="col">
                        <p>Actions</p>
                    </th>
                </tr>
            </thead>
            <tbody id="checkboxContainer">
                <?php iterComponents("user-table", $page, 15, $users) ?>
            </tbody>
        </table>
    </div>
    <?php echo Viewer::getComponents("pagination", ["page" => $page, "maxPageCount" => $maxPageCount]) ?>
</main>
<script src="/lianmart/assets/js/search.js"></script>
<script src="/lianmart/assets/js/pagination.js"></script>