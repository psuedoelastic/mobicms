<!-- Заголовок раздела -->
<div class="titlebar">
    <div class="button"></div>
    <div><h1><?= __('who_on_site') ?></h1></div>
    <div class="separator"></div>
    <div class="button">
        <!-- Кнопка меню -->
        <button type="button" class="slider-button" title="<?= __('control') ?>">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
</div>

<!-- Слайдер с Админскими кнопками -->
<div class="content slider close">
    <ul class="nav nav-pills nav-justified">
        <li<?= App::router()->getQuery(0) == false ? ' class="active"' : '' ?>>
            <a href="<?= $this->uri ?>"><i class="user fw"></i><?= __('users') ?></a>
        </li>
        <li<?= App::router()->getQuery(0) == 'history' ? ' class="active"' : '' ?>>
            <a href="<?= $this->uri ?>history/"><i class="sort-amount-desc fw"></i><?= __('history') ?></i></a>
        </li>
        <!-- Показываем только для администрации -->
        <?php if (App::user()->rights): ?>
            <li>
                <a href="<?= $this->uri ?>guests/"><i class="group fw"></i><?= __('guests') ?></a>
            </li>
            <li>
                <a href="<?= $this->uri ?>ip/"><i class="bolt fw"></i><?= __('ip_activity') ?></a>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Список онлайн -->
<div class="content box m-list">
    <?php if (App::user()->id || App::cfg()->sys->usr_view_online): ?>
        <h2><?= $this->list_header ?></h2>
        <ul class="striped">
            <?php if (isset($this->list)): ?>
                <?php foreach ($this->list as $user): ?>
                    <?php include $this->getPath('include.user.php') ?>
                <?php endforeach ?>
            <?php else: ?>
                <li style="text-align: center; padding: 27px"><?= __('list_empty') ?></li>
            <?php endif ?>
        </ul>
        <h3><?= __('total') ?>:&#160;<?= $this->total ?></h3>
    <?php else: ?>
        <div class="content box padding text-center">
            <?= __('access_guest_forbidden') ?>
        </div>
    <?php endif ?>
</div>
