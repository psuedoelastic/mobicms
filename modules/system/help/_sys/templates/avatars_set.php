<!-- Заголовок раздела -->
<div class="titlebar">
    <div class="button">
        <a href="<?= App::router()->getUri(2) ?>list/<?= \App::router()->getQuery(2) ?>" title="<?= __('back') ?>">
            <i class="arrow-circle-left lg"></i>
        </a>
    </div>
    <div class="separator"></div>
    <div><h1><?= __('avatars') ?></h1></div>
    <div class="button"></div>
</div>

<!-- Информация о пользователе -->
<?php if (!isset($this->hideuser)): ?>
    <?php $user = App::user()->data ?>
    <div class="info-block m-list">
        <ul><?php include_once $this->getPath('include.user.php') ?></ul>
    </div>
<?php endif ?>

<!-- Форма установки аватара -->
<div class="content box padding">
    <?= $this->form ?>
</div>