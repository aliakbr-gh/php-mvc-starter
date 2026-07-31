<div class="pagination-bar">
    <p>Showing <?= $pagination['first'] ?>–<?= $pagination['last'] ?> of <?= $pagination['total'] ?></p>

    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination" aria-label="Pagination">
            <?php
            $start = max(1, $pagination['current_page'] - 2);
            $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
            $query = $pagination['query'] + ['per_page' => $pagination['per_page']];
            $paginationPath = $paginationPath ?? 'users';
            ?>

            <?php if ($pagination['current_page'] > 1): ?>
                <a href="<?= url($paginationPath . '?' . http_build_query($query + ['page' => $pagination['current_page'] - 1])) ?>">Previous</a>
            <?php endif; ?>

            <?php for ($page = $start; $page <= $end; $page++): ?>
                <a
                    href="<?= url($paginationPath . '?' . http_build_query($query + ['page' => $page])) ?>"
                    class="<?= $page === $pagination['current_page'] ? 'active' : '' ?>"
                ><?= $page ?></a>
            <?php endfor; ?>

            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="<?= url($paginationPath . '?' . http_build_query($query + ['page' => $pagination['current_page'] + 1])) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
