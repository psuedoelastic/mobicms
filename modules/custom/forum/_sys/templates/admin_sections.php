<?php $id = abs(intval(App::request()->getQuery('id', 0))) ?>
<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button">
        <a href="<?= App::router()->getUri(($id ? 3 : 1)) ?>" title="<?= __('back') ?>">
            <i class="arrow-circle-left lg"></i>
        </a>
    </div>
    <div class="separator"></div>
    <div><h1><?= __('forum') ?>: <?= __('forum_structure') ?></h1></div>
    <div class="separator"></div>
    <div class="button">
        <a href="<?= $this->uri ?>add/<?= ($id ? '?id=' . $id : '') ?>" title="<?= __('add') ?>">
            <i class="plus lg"></i>
        </a>
    </div>
</div>

<div class="content box m-list">
    <h2><?= $this->title ?></h2>
    <?php if (isset($this->list)): ?>
        <ul class="striped">
            <?php foreach ($this->list as $val): ?>
                <li>
                    <div>
                        <a href="#" class="lbtn dropdown dropdown-toggle" data-toggle="dropdown"></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?= App::router()->getUri(2) ?>up/?id=<?= $val['id'] ?>"><i class="arrow-up fw"></i><?= __('up') ?></a></li>
                            <li><a href="<?= $this->uri ?>down/?id=<?= $val['id'] ?>"><i class="arrow-down fw"></i><?= __('down') ?></a></li>
                            <li><a href="<?= $this->uri ?>edit/?id=<?= $val['id'] ?>"><i class="edit fw"></i><?= __('edit') ?></a></li>
                            <li><a href="<?= $this->uri ?>delete/?id=<?= $val['id'] ?>"><i class="bin fw"></i><?= __('delete') ?></a></li>
                            <li><a href="<?= App::cfg()->sys->homeurl ?>forum/?id=<?= $val['id'] ?>"><i class="arrow-left fw"></i><?= __('to_section') ?></a></li>
                        </ul>
                    </div>
                    <a href="<?= $this->uri ?>sections/?id=<?= $val['id'] ?>" class="mlink has-lbtn">
                        <h2><?= $val['text'] ?></h2>
                        <?php if (!empty($val['soft'])): ?>
                            <p><?= $val['soft'] ?></p>
                        <?php endif ?>
                        <span class="badge badge-right"><?= $val['counter'] ?></span>
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