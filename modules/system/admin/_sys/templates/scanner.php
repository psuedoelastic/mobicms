<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button"><a href="<?= App::router()->getUri() ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('admin_panel') ?></h1></div>
    <div class="button"></div>
</div>

<div class="content box padding">
    <?php if (isset($this->errormsg)): ?>
        <div class="alert alert-danger">
            <?= $this->errormsg ?>
        </div>
    <?php elseif (isset($this->ok)): ?>
        <div class="alert alert-success">
            <?= $this->ok ?>
        </div>
    <?php endif ?>

    <?= $this->form ?>
    <br/>
    <?php if (!empty($this->modifiedFiles)): ?>
        <div class="alert alert-danger">
            <h4>(<?= count($this->modifiedFiles) ?>) <?= __('modified_files') ?></h4>
            <?= __('modified_files_help') ?><br/><br/>
            <?php foreach ($this->modifiedFiles as $file): ?>
                <div style="font-size: small; font-weight: bold; padding-top: 2px; padding-bottom: 2px; border-top: 1px dotted #ec8583;">
                    <?= htmlspecialchars($file['file_path']) ?>
                    <span style="font-size: small; font-weight: normal; color: #696969">
                        - <?= $file['file_date'] ?>
                    </span>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
    <?php if (!empty($this->missingFiles)): ?>
        <div class="alert alert-danger">
            <h4>(<?= count($this->missingFiles) ?>) <?= __('missing_files') ?></h4>
            <?= __('missing_files_help') ?><br/><br/>
            <?php foreach ($this->missingFiles as $file): ?>
                <div style="font-size: small; font-weight: bold; padding-top: 2px; padding-bottom: 2px; border-top: 1px dotted #ec8583;">
                    <?= htmlspecialchars($file) ?>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
    <?php if (!empty($this->extraFiles)): ?>
        <div class="alert alert-danger">
            <h4>(<?= count($this->extraFiles) ?>) <?= __('antispy_new_files') ?></h4>
            <?= __('antispy_new_files_help') ?><br/><br/>
            <?php foreach ($this->extraFiles as $file): ?>
                <div style="font-size: small; font-weight: bold; padding-top: 2px; padding-bottom: 2px; border-top: 1px dotted #ec8583;">
                    <?= htmlspecialchars($file['file_path']) ?>
                    <span style="font-size: small; font-weight: normal; color: #696969">
                        - <?= $file['file_date'] ?>
                    </span>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>