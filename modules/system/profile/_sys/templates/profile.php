<?php $user = Users::$data ?>
<!-- Заголовок раздела -->
<div class="titlebar<?= $user['id'] == App::user()->id ? ' private' : '' ?> toogle-admin">
    <div class="button"></div>
    <div><h1><?= (App::user()->id && $user['id'] == App::user()->id ? __('my_profile') : __('user_profile')) ?></h1></div>
    <?php if ($user['id'] != App::user()->id && App::user()->rights >= 3 && (App::user()->rights == 9 || App::user()->rights > $user['rights'])): ?>
        <div class="separator"></div>
        <div class="button"><a class="slider-button" href="#" title="<?= __('control') ?>"><i class="cog lg"></i></a></div>
    <?php else: ?>
        <div class="button"></div>
    <?php endif ?>
</div>

<!-- Слайдер с Админскими кнопками -->
<div class="content slider close">
    <ul class="nav nav-pills nav-justified">
        <li><a href="<?= App::router()->getUri(2) ?>option/"><i class="settings fw"></i> <?= __('settings') ?></a></li>
        <?php if ($user['id'] != App::user()->id && (App::user()->rights == 9 || App::user()->rights > $user['rights'])): ?>
            <li><a href="#"><i class="sign-out fw"></i> <?= __('kick') ?></a></li>
            <li><a href="#"><i class="ban fw"></i> <?= __('ban') ?></a></li>
            <li><a href="#"><i class="bin fw"></i> <?= __('delete') ?></a></li>
        <?php endif ?>
    </ul>
</div>

<!-- Информация о пользователе -->
<div class="info-block m-list">
    <ul><?php include_once $this->getPath('include.user.php') ?></ul>
</div>

<!-- Навигация -->
<ul class="nav nav-pills nav-stacked">
    <li class="title"><?= __('reputation') ?></li>
    <li>
        <a href="<?= App::router()->getUri(2) ?>reputation/">
            <table style="width: 100%">
                <tr>
                    <td class="progress-counter"><?= $this->reputation_total ?></td>
                    <td style="width: 100%">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" style="width: <?= $this->reputation['a'] ?>%;"></div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-info" style="width: <?= $this->reputation['b'] ?>%;"></div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-neytral" style="width: <?= $this->reputation['c'] ?>%;"></div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-warning" style="width: <?= $this->reputation['d'] ?>%;"></div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-danger" style="width: <?= $this->reputation['e'] ?>%;"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </a>
    </li>

    <li class="title"><?= __('information') ?></li>
    <li><a href="#"><i class="info-circle lg fw"></i><?= __('personal_data') ?> <span class="label label-warning">planned</span></a></li>
    <li><a href="#"><i class="stats-bars lg fw"></i><?= __('activity') ?> <span class="label label-warning">planned</span></a></li>

    <li class="title"><?= __('assets') ?></li>
    <li><a href="#"><i class="pictures lg fw"></i><?= __('photo_album') ?> <span class="label label-warning">planned</span> <span class="badge badge-right">0</span></a></li>
    <li><a href="#"><i class="comments lg fw"></i><?= __('guestbook') ?> <span class="label label-warning">planned</span> <span class="badge badge-right">0</span></a></li>
    <li><a href="#"><i class="group lg fw"></i><?= __('friends') ?> <span class="label label-warning">planned</span> <span class="badge badge-right">0</span></a></li>

    <?php if (App::user()->id && App::user()->id != $user['id']): ?>
        <li class="title"><?= __('mail') ?></li>
        <?php if (empty($this->banned)): ?>
            <li><a href="<?= App::cfg()->sys->homeurl ?>mail/?act=messages&amp;id=<?= $this->user['id'] ?>"><i class="envelope lg fw"></i><?= __('contact_write') ?></a></li>
            <li><a href="<?= App::cfg()->sys->homeurl ?>contacts/?act=select&amp;mod=contact&amp;id=<?= $this->user['id'] ?>"><i class="adress_book lg fw"></i><?= ($this->num_cont ? __('contact_delete') : __('contact_add')) ?></a>
            </li>
        <?php endif ?>
        <li><a href="<?= App::cfg()->sys->homeurl ?>contacts/?act=select&amp;mod=banned&amp;id=<?= $this->user['id'] ?>">
                <i class="ban lg fw"></i><?= (isset($this->banned) && $this->banned == 1 ? __('contact_delete_ignor') : __('contact_add_ignor')) ?></a></li>
    <?php endif ?>
</ul>