<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button"><a href="<?= App::router()->getUri() ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('smilies') ?></h1></div>
    <div class="button"></div>
</div>

<div class="content box padding">
    <?php if (isset($this->error)): ?>
        <div class="alert alert-danger">
            <?= $this->error ?>
        </div>
    <?php elseif (isset($this->save)): ?>
        <div class="alert alert-success">
            <?= $this->save ?>
        </div>
    <?php endif ?>
    <?= $this->form ?>
</div>