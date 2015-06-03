<!-- Заголовок раздела -->
<div class="titlebar <?= Users::$data['id'] == App::user()->id ? 'private' : 'admin' ?>">
    <div class="button"><a href="<?= App::router()->getUri(2) ?>"><i class="arrow-circle-left lg"></i></a></div>
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
    <li class="title"><?= __('profile') ?></li>
    <li><a href="<?= $this->uri ?>edit/"><i class="edit fw lg"></i><?= __('profile_edit') ?></a></li>
    <li><a href="<?= $this->uri ?>avatar/"><i class="picture fw lg"></i><?= __('change_avatar') ?></a></li>
    <li><a href="<?= $this->uri ?>password/"><i class="shield fw lg"></i><?= __('change_password') ?></a></li>
    <li><a href="<?= $this->uri ?>email/"><i class="shield fw lg"></i><?= __('change_email') ?></a></li>
    <?php if (App::cfg()->sys->usr_change_nickname || App::user()->rights >= 7): ?>
        <li><a href="<?= $this->uri ?>nickname/"><i class="shield fw lg"></i><?= __('change_nickname') ?></a></li>
    <?php endif ?>
    <li class="title"><?= __('settings') ?></li>
    <li><a href="<?= $this->uri ?>settings/"><i class="settings fw lg"></i><?= __('system_settings') ?></a></li>
    <li><a href="<?= $this->uri ?>theme/"><i class="paint-format fw lg"></i><?= __('design_template') ?> <span class="label label-danger">draft</span></a></li>
    <?php if (App::cfg()->sys->lng_switch): ?>
        <li><a href="<?= $this->uri ?>language/"><i class="language fw lg"></i><?= __('language') ?></a></li>
    <?php endif ?>
    <?php if (App::user()->rights == 9 || (App::user()->rights == 7 && App::user()->rights > Users::$data['rights'])): ?>
        <li><a href="<?= $this->uri ?>rank/"><span class="danger"><i class="graduation-cap fw lg"></i><?= __('rank') ?></span></a></li>
    <?php endif ?>
</ul>