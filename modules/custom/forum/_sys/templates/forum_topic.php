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
                    <a href="<?= App::cfg()->sys->homeurl . 'profile/' . $val['id'] ?>" class="mlink<?= App::user()->rights ? ' has-lbtn' : '' ?>">
                        <dl class="description">
                            <dt>
                                <?php if (!empty($val['avatar'])): ?>
                                    <img src="<?= $val['avatar'] ?>"/>
                                <?php endif ?>
                            </dt>
                            <dd>
                                <div class="header"><?= $val['from'] ?></div>
                                <?php if (isset($val['status']) && !empty($val['status'])): ?>
                                    <div class="small bold colored"><?= $val['status'] ?></div>
                                <?php endif ?>
                                <?php if (App::user()->rights): ?>
                                    <div class="small inline margin"><?= $val['soft'] ?></div>
                                    <div class="small">
                                        <?php if (isset($val['ip_via_proxy']) && !empty($val['ip_via_proxy'])): ?>
                                            <span class="danger"><?= long2ip($val['ip']) ?></span> &raquo; <?= long2ip($val['ip_via_proxy']) ?>
                                        <?php else: ?>
                                            <?= long2ip($val['ip']) ?>
                                        <?php endif ?>
                                    </div>
                                <?php endif ?>
                            </dd>
                        </dl>
                    </a>

                    <div><?= $val['text'] ?></div>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <div style="text-align: center; padding: 27px"><?= __('list_empty') ?></div>
    <?php endif ?>
    <h3><?= __('total') ?>:&#160;<?= $this->total ?></h3>
</div>