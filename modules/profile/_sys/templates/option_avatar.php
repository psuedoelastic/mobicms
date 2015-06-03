<!-- Заголовок раздела -->
<div class="titlebar <?= Users::$data['id'] == App::user()->id ? 'private' : 'admin' ?>">
    <div class="button"><a href="<?= App::router()->getUri(3) ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('settings') ?></h1></div>
    <div class="button"></div>
</div>

<!-- Информация о пользователе -->
<?php $user = Users::$data; ?>
<div class="info-block m-list">
    <ul><?php include_once $this->getPath('include.user.php') ?></ul>
</div>

<ul class="nav nav-pills nav-stacked">
    <li class="title"><?= __('avatar') ?></li>
    <?php if (App::cfg()->sys->usr_upload_avatars || App::user()->rights >= 7): ?>
        <li><a href="<?= App::router()->getUri(4) ?>image/"><i class="upload lg fw"></i><?= __('upload_image') ?></a></li>
        <li><a href="<?= App::router()->getUri(4) ?>animation/"><i class="upload lg fw"></i><?= __('upload_animation') ?></a></li>
    <?php endif ?>
    <?php if (App::cfg()->sys->usr_gravatar || App::user()->rights >= 7): ?>
        <li><a href="<?= App::router()->getUri(4) ?>gravatar/"><i class="link lg fw"></i><?= __('set_gravatar') ?></a></li>
    <?php endif ?>
    <?php if (Users::$data['id'] == App::user()->id) : ?>
        <li><a href="<?= App::cfg()->sys->homeurl ?>help/avatars/"><i class="picture lg fw"></i><?= __('select_in_catalog') ?></a></li>
    <?php endif ?>
    <?php if (!empty(Users::$data['avatar'])) : ?>
        <li><a href="<?= App::router()->getUri(4) ?>delete/"><i class="bin lg fw"></i><?= __('delete') ?></a></li>
    <?php endif ?>
</ul>