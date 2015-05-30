<!-- Заголовок раздела -->
<div class="titlebar">
    <div class="button">
        <a href="<?= App::router()->getUri(1) ?>" title="<?= __('back') ?>">
            <i class="arrow-circle-left lg"></i>
        </a>
    </div>
    <div class="separator"></div>
    <div><h1><?= __('rules') ?></h1></div>
    <div class="button"></div>
</div>

<!-- Справочная информация -->
<div class="content box padding">
    <?= __('rules', false, true) ?>
</div>