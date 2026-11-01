<?php
nomadCommentsStyles();
$comments = $page->comments()->toStructure();
$approved = $comments->filterBy('status', 'approved')->count();
$pending  = $comments->filterBy('status', 'pending')->count();
?>
<span class="comment-meta">
  <?= $approved ?> comment<?= $approved === 1 ? '' : 's' ?>
  <?php if ($pending > 0): ?>
    · <a href="<?= $page->panelUrl() ?>"><?= $pending ?> pending</a>
  <?php endif ?>
</span>
