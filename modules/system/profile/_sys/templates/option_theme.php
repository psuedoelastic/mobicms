<!-- Заголовок раздела -->
<div class="titlebar <?= Users::$data['id'] == App::user()->id ? 'private' : 'admin' ?>">
    <div class="button"><a href="<?= App::router()->getUri(3) ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('settings') ?></h1></div>
    <div class="button"></div>
</div>

<div class="content box m-list">
    <h2><?= __('design_template') ?></h2>
    <ul class="striped">
        <?php if (!empty($this->tpl_list)): ?>
            <?php foreach ($this->tpl_list as $key => $val): ?>
                <li>
                    <a href="?act=set&amp;mod=<?= $key ?>" class="mlink">
                        <dl class="description">
                            <dt class="wide">
                                <img src="<?= $val['thumbinal'] ?>"/>
                            </dt>
                            <dd>
                                <div class="header"><?= $val['name'] ?></div>
                                <p>
                                    <?php if (!empty($val['author'])): ?>
                                        <strong><?= __('author') ?></strong>: <?= $val['author'] ?>
                                    <?php endif ?>
                                    <?php if (!empty($val['author_url'])): ?>
                                        <br/><strong><?= __('site') ?></strong>: <?= $val['author_url'] ?>
                                    <?php endif ?>
                                    <?php if (!empty($val['author_email'])): ?>
                                        <br/><strong>Email</strong>: <?= $val['author_email'] ?>
                                    <?php endif ?>
                                    <?php if (!empty($val['description'])): ?>
                                        <br/><strong><?= __('description') ?></strong>: <?= $val['description'] ?>
                                    <?php endif ?>
                                </p>
                            </dd>
                        </dl>
                    </a>
                </li>
            <?php endforeach ?>
        <?php else: ?>
            <li style="text-align: center; padding: 27px"><?= __('list_empty') ?></li>
        <?php endif ?>
    </ul>
    <h3><?= __('total') . ': ' . (!empty($this->tpl_list) ? count($this->tpl_list) : 0) ?></h3>
</div>