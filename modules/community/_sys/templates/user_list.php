<!-- Заголовок раздела -->
<div class="titlebar">
    <div class="button"></div>
    <div><h1><?= __('users') ?></h1></div>
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

<!-- Список пользователей -->
<div class="content box m-list">
    <?php if (App::user()->id || App::cfg()->sys->usr_view_online): ?>
        <h2><?= __('users_list') ?></h2>
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
        <?php if ($this->total > App::user()->settings['page_size']): ?>
            <?= Functions::displayPagination($this->uri . '?', App::vars()->start, $this->total, App::user()->settings['page_size']) ?>
        <?php endif ?>
    <?php else: ?>
        <div class="content box padding text-center">
            <?= __('access_guest_forbidden') ?>
        </div>
    <?php endif ?>
</div>