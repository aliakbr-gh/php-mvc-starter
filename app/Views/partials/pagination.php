<?php $pages=max(1,(int)ceil($result['total']/$perPage));$current=min($page,$pages);$query=['search'=>$search,'per_page'=>$perPage]; ?>
<div class="pagination-wrap"><span>Showing <?= $result['total']?(($current-1)*$perPage+1):0 ?>–<?= min($current*$perPage,$result['total']) ?> of <?= $result['total'] ?></span><nav class="pagination">
<?php if($current>1): ?><a href="<?= htmlspecialchars(url($baseUrl).'?'.http_build_query($query+['page'=>$current-1]),ENT_QUOTES,'UTF-8') ?>">Previous</a><?php endif; ?>
<strong>Page <?= $current ?> of <?= $pages ?></strong>
<?php if($current<$pages): ?><a href="<?= htmlspecialchars(url($baseUrl).'?'.http_build_query($query+['page'=>$current+1]),ENT_QUOTES,'UTF-8') ?>">Next</a><?php endif; ?>
</nav></div>
