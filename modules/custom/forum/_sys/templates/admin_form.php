<?php $id = abs(intval(App::request()->getQuery('id', 0))) ?>
<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button">
        <a href="<?= App::router()->getUri(2) ?>sections/<?= ($id ? '?id=' . $id : '') ?>" title="<?= __('back') ?>">
            <i class="arrow-circle-left lg"></i>
        </a>
    </div>
    <div class="separator"></div>
    <div><h1><?= __('forum_structure') ?></h1></div>
    <div class="button"></div>
</div>

<?php if (isset($this->section)): ?>
    <div class="info-block"><?= __('section') . ': ' . $this->section ?></div>
<?php endif ?>
<div class="content form-container">
    <?= $this->form ?>
</div>