<?php if (isset($this->error)): ?>
    <?= $this->error ?>
<?php endif ?>
<div class="menu">
    <form action="<?= App::router()->getUri(2) ?>?act=edit&amp;user=<?= $this->user['id'] . '&amp;al=' . $this->al ?>" method="post">
        <div class="formblock">
            <label for="name"><?= __('title') ?></label><br/>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($this->name) ?>" maxlength="30"/><br/>

            <div class="desc">Min. 2, Max. 30</div>
            <label><?= __('description') ?></label><br/>
            <textarea name="description" rows="<?= App::user()->settings['field_h'] ?>"><?= htmlspecialchars($this->description) ?></textarea><br/>

            <div class="desc"><?= __('not_mandatory_field') ?><br/>Max. 500</div>
        </div>
        <div class="formblock">
            <label><?= __('access') ?></label><br/>
            <input type="radio" name="access" value="4" <?= (!$this->access || $this->access == 4 ? 'checked="checked"' : '') ?>/>&#160;<?= __('access_all') ?><br/>
            <input type="radio" name="access" value="3" <?= ($this->access == 3 ? 'checked="checked"' : '') ?>/>&#160;<?= __('access_friends') ?><br/>
            <input type="radio" name="access" value="2" <?= ($this->access == 2 ? 'checked="checked"' : '') ?>/>&#160;<?= __('access_by_password') ?><br/>
            <input type="radio" name="access" value="1" <?= ($this->access == 1 ? 'checked="checked"' : '') ?>/>&#160;<?= __('access_closed') ?>
        </div>
        <div class="formblock">
            <label><?= __('password') ?></label><br/>
            <input type="text" name="password" value="<?= htmlspecialchars($this->password) ?>" maxlength="15"/><br/>

            <div class="desc"><?= __('access_help') ?><br/>Min. 3, Max. 15</div>
        </div>
        <div class="formblock">
            <input type="submit" name="submit" value="<?= __('save') ?>"/>
        </div>
    </form>
</div>
<div class="phdr">
    <a href="<?= App::router()->getUri(2) ?>?act=list&amp;user=<?= $this->user['id'] ?>"><?= __('cancel') ?></a>
</div>