<!-- Заголовок раздела -->
<div class="titlebar">
    <div><h1><?= __('registration') ?></h1></div>
</div>

<!-- Форма -->
<div class="content box padding">
    <?php if (App::cfg()->sys->usr_reg_allow): ?>
        <div class="alert alert-warning">
            <?= str_replace('###', App::cfg()->sys->homeurl . 'help/rules/', __('terms')) ?>
        </div>
        <?php if (App::cfg()->sys->usr_reg_moderation): ?>
            <div class="alert alert-warning">
                <?= __('moderation_warning') ?>
            </div>
        <?php endif ?>
        <?= $this->form ?>
    <?php else: ?>
        <div class="alert alert-danger text-center">
            <?= __('registration_closed') ?>
        </div>
    <?php endif ?>
</div>