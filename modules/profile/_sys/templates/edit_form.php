<!-- Заголовок раздела -->
<div class="titlebar <?= Users::$data['id'] == App::user()->id ? 'private' : 'admin' ?>">
    <div class="button"><a href="<?= App::router()->getUri(3) ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('settings') ?></h1></div>
    <div class="button"></div>
</div>

<?php if (!isset($this->hideuser)): ?>
    <!-- Информация о пользователе -->
    <?php $user = Users::$data; ?>
    <div class="info-block m-list">
        <ul><?php include_once $this->getPath('include.user.php') ?></ul>
    </div>
<?php endif ?>
<div class="content box padding">
    <?= $this->form ?>
</div>