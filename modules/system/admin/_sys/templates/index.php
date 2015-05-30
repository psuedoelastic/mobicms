<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div><h1><?= __('admin_panel') ?></h1></div>
</div>

<!-- Меню -->
<ul class="nav nav-pills nav-stacked">
    <?php if (App::user()->rights >= 7) : ?>
        <li class="title"><?= __('modules') ?></li>
        <?php if (App::user()->rights == 9): ?>
            <li><a href="#"><i class="dashboard lg fw"></i><?= __('counters') ?> <span class="label label-warning">planned</span></a></li>
            <li><a href="<?= $this->uri ?>sitemap/"><i class="sitemap lg fw"></i><?= __('sitemap') ?> <span class="label label-danger">draft</span></a></li>
        <?php endif ?>
        <li class="title"><?= __('system') ?></li>
        <?php if (App::user()->rights == 9): ?>
            <li><a href="<?= $this->uri ?>system_settings/"><i class="settings lg fw"></i><?= __('system_settings') ?></a></li>
            <li><a href="<?= $this->uri ?>users_settings/"><i class="settings lg fw"></i><?= __('users_settings') ?></a></li>
            <li><a href="<?= $this->uri ?>smilies/"><i class="smile lg fw"></i><?= __('smilies') ?></a></li>
        <?php endif ?>
        <li class="title"><?= __('security') ?></li>
        <li><a href="<?= $this->uri ?>acl/"><i class="graduation-cap lg fw"></i><?= __('acl') ?></a></li>
        <?php if (App::user()->rights == 9) : ?>
            <li><a href="#"><i class="shield lg fw"></i><?= __('firewall') ?> <span class="label label-warning">planned</span></a></li>
            <li><a href="<?= $this->uri ?>scanner/"><i class="bug lg fw"></i><?= __('antispy') ?></a></li>
        <?php endif ?>
        <li><a href="<?= App::cfg()->sys->homeurl ?>whois/"><i class="info-circle lg fw"></i>WHOIS</a></li>
    <?php endif ?>
</ul>