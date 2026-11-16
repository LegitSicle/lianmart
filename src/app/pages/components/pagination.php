<?php 
/** @var int $page */
/** @var int $maxPageCount */

if ($maxPageCount > 1):
    $set = array_unique(array_filter(
        [1, $page - 1, $page, $page + 1, $maxPageCount],
        fn ($n) => $n >= 1 && $n <= $maxPageCount
    ));
    sort($set);

    $pages = [];
    $prev = 0;
    foreach ($set as $n) {
        if ($n - $prev > 1) {
            $pages[] = "...";
        }
        $pages[] = $n;
        $prev = $n;
    }
?>
<nav class="pager" id="pager">
    <button type="button" data-page="<?= max(1, $page - 1) ?>">&lt;</button>
    
    <?php foreach ($pages as $p): ?>
        <?php if ($p === "..."): ?>
            <span class="page-dots">...</span>
        <?php else: ?>
            <?php $classAttr = $p === $page ? ' class="active"' : ""; ?>
            <button type="button" data-page="<?= $p ?>"<?= $classAttr ?>>
                <?= $p ?>
            </button>
        <?php endif; ?>
    <?php endforeach; ?>

    <button type="button" data-page="<?= min($maxPageCount, $page + 1) ?>">&gt;</button>
</nav>
<?php endif; ?>