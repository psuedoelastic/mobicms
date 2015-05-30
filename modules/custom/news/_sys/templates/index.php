<!-- Заголовок раздела -->
<div class="titlebar toogle-admin">
    <div class="button"></div>
    <div><h1><?= __('news') ?></h1></div>
    <?php if (App::user()->rights >= 7): ?>
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
    <?php else: ?>
        <div class="button"></div>
    <?php endif ?>
</div>

<?php if (App::user()->rights >= 7): ?>
    <!-- Слайдер с Админскими кнопками -->
    <div class="content slider close">
        <ul class="nav nav-pills nav-justified">
            <li><a href="<?= $this->uri ?>add/"><i class="plus fw"></i><?= __('add') ?></a></li>
            <li><a href="<?= $this->uri ?>clean/"><i class="bin fw"></i><?= __('clear') ?></a></li>
            <li><a href="<?= $this->uri ?>admin/"><i class="cogs fw"></i><?= __('settings') ?></a></li>
        </ul>
    </div>
<?php endif ?>

<!-- Список новостей -->
<div class="content box m-list">
    <h2><?= __('site_news') ?></h2>
    <?php if (isset($this->list)): ?>
        <ul class="striped">
            <?php foreach ($this->list as $val): ?>
                <li class="static">
                    <h2><?= $val['title'] ?></h2>

                    <div class="news-info">
                        <?= Functions::displayDate($val['time']) ?><br/>
                        <?= __('added') ?>: <a href="<?= App::cfg()->sys->homeurl ?>profile/<?= $val['author_id'] ?>/"><?= $val['author'] ?></a>
                    </div>
                    <p><?= $val['text'] ?></p>
                    <?php if ($val['comm_enable'] || $val['comm_count']): ?>
                        <div class="news-info"><a href="#"><?= __('comments') ?>: <?= $val['comm_count'] ?></a></div>
                    <?php endif ?>
                    <?php if (App::user()->rights >= 7): ?>
                        <div class="slider close">
                            <a href="<?= $this->uri ?>edit/?id=<?= $val['id'] ?>" class="btn btn-link btn-xs"><i class="edit"></i> <?= __('edit') ?></a>
                            <a href="<?= $this->uri ?>delete/?id=<?= $val['id'] ?>" class="btn btn-link btn-xs"><i class="bin"></i> <?= __('delete') ?></a>
                        </div>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <div style="text-align: center; padding: 27px"><?= __('list_empty') ?></div>
    <?php endif ?>
    <?php if ($this->total > App::user()->settings['page_size']): ?>
        <?= Functions::displayPagination($this->uri . '?', App::vars()->start, $this->total, App::user()->settings['page_size']) ?>
    <?php endif ?>
    <h3><?= __('total') ?>:&#160;<?= $this->total ?></h3>
</div>