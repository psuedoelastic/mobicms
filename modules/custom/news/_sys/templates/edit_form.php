<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button"><a href="<?= App::router()->getUri() ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('news') ?></h1></div>
    <div class="button"></div>
</div>

<!-- Форма -->
<div class="content box padding">
    <?= $this->form ?>
</div>