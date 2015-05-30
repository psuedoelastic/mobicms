<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button"><a href="<?= App::router()->getUri() ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1>WHOIS</h1></div>
    <div class="button"></div>
</div>

<div class="content box padding">
    <?php if (isset($_GET['save'])): ?>
        <div class="alert alert-success"><?= __('data_saved') ?></div>
    <?php elseif (isset($_GET['default'])): ?>
        <div class="alert"><?= __('settings_default') ?></div>
    <?php endif ?>
    <?= $this->form ?>
</div>