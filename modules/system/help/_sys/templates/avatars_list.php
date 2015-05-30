<!-- Заголовок раздела -->
<div class="titlebar">
    <div class="button">
        <a href="<?= App::router()->getUri(2) ?>" title="<?= __('back') ?>">
            <i class="arrow-circle-left lg"></i>
        </a>
    </div>
    <div class="separator"></div>
    <div><h1><?= __('avatars') ?></h1></div>
    <div class="button"></div>
</div>

<!-- Список аватаров -->
<div class="content box m-list">
    <h2><?= __($this->cat) ?></h2>

    <div style="text-align: center; padding: 12px">
        <?php if ($this->total): ?>
            <?php foreach ($this->list as $val): ?>
                <a href="<?= $val['link'] ?>"><img src="<?= $val['image'] ?>" alt="" class="avatars-list"/></a>
            <?php endforeach ?>
        <?php else: ?>
            <?= __('list_empty') ?>
        <?php endif ?>
    </div>
    <h3><?= __('total') ?>:&#160;<?= $this->total ?></h3>
</div>