<?php if (isset($this->titlebar)): ?>
    <ul class="title admin">
        <li class="center"><h1><?= $this->titlebar ?></h1></li>
    </ul>
<?php endif ?>
<div class="content form-container">
    <?php if (isset($this->message)): ?>
        <div class="alert"><?= $this->message ?></div>
    <?php endif ?>
    <?php if (isset($this->continue)): ?>
        <a class="btn btn-primary" href="<?= $this->continue ?>"><?= __('continue') ?></a>
    <?php endif ?>
    <?php if (isset($this->back)): ?>
        <a class="btn btn-link" href="<?= $this->back ?>"><?= __('back') ?></a>
    <?php endif ?>
</div>