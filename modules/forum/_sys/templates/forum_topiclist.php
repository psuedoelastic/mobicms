<!-- Заголовок раздела -->
<div class="titlebar toogle-admin">
    <?php if (isset($this->backlink)): ?>
        <div class="button">
            <a href="<?= $this->backlink ?>" title="<?= __('back') ?>">
                <i class="arrow-circle-left lg"></i>
            </a>
        </div>
        <div class="separator"></div>
    <?php else: ?>
        <div class="button"></div>
    <?php endif ?>
    <div><h1><?= __('forum') ?></h1></div>
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

<div class="content box m-list">
    <h2><?= $this->breadcrumb ?></h2>
    <?php if (isset($this->list)): ?>
        <ul class="striped">
            <?php foreach ($this->list as $val): ?>
                <li>
                    <a href="<?= $this->uri ?>?id=<?= $val['id'] ?>" class="mlink has-badge">
                        <dl class="description">
                            <dt class="narrow">
                                <?php if ($val['edit'] > 0): ?>
                                    <!-- Значок закрытой темы -->
                                    <i class="lock danger"></i>
                                <?php endif ?>
                                <?php if ($val['vip']): ?>
                                    <!-- Значок закрепленной темы -->
                                    <i class="bullhorn danger"></i>
                                <?php endif ?>
                                <?= ($val['unread']) ? '<i class="comment-o"></i>' : '<i class="comment danger"></i>' ?>
                            </dt>
                            <dd>
                                <div class="header"><?= $val['text'] ?></div>
                                <div><span class="small"><strong><?= $this->buddies ?></strong> (<?= Functions::displayDate($val['time']) ?>)</span></div>
                            </dd>
                        </dl>
                        <span class="badge badge-right"><?= $val['countmsg'] ?></span>
                    </a>
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