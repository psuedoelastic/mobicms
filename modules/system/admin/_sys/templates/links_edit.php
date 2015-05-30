<?php
//TODO: переделать на генератор форм
$id = abs(intval(App::request()->getQuery('id', 0)));
?>
<form action="<?= App::router()->getUri() . 'links/' ?>?act=edit<?= ($id ? '&amp;id=' . $id : '') ?>" method="post">
    <div class="menu">
        <div class="formblock">
            <label><?= __('link') ?></label><br/>
            <input type="checkbox" name="show" <?= ($this->res['show'] ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('link_direct') ?><br/>
            <input type="text" name="link" value="<?= htmlentities($this->res['link'], ENT_QUOTES, 'UTF-8') ?>"/><br/>

            <div class="desc">
                <?= __('link_direct_help') ?>
            </div>
        </div>
        <div class="formblock">
            <label><?= __('title') ?></label><br/>
            <input type="text" name="name" value="<?= htmlentities($this->res['name'], ENT_QUOTES, 'UTF-8') ?>"/><br/>

            <div class="desc">
                <?= __('link_add_name_help') ?>
            </div>
        </div>
        <div class="formblock">
            <label><?= __('color') ?></label><br/>
            <input type="text" name="color" size="6" value="<?= $this->res['color'] ?>"/><br/>

            <div class="desc">
                <?= __('link_add_color_help') ?>
            </div>
        </div>
        <div class="formblock">
            <label><?= __('transitions') ?></label><br/>
            <input type="text" name="count" size="6" value="<?= $this->res['count_link'] ?>"/><br/>

            <div class="desc">
                <?= __('link_add_trans_help') ?>
            </div>
        </div>
        <div class="formblock">
            <label><?= __('days') ?></label><br/>
            <input type="text" name="day" size="6" value="<?= $this->res['day'] ?>"/><br/>

            <div class="desc">
                <?= __('link_add_days_help') ?>
            </div>
        </div>
    </div>
    <div class="gmenu">
        <div class="formblock">
            <label><?= __('to_show') ?></label><br/>
            <input type="radio" name="view" value="0" <?= (!$this->res['view'] ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('to_all') ?><br/>
            <input type="radio" name="view" value="1" <?= ($this->res['view'] == 1 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('to_guest') ?><br/>
            <input type="radio" name="view" value="2" <?= ($this->res['view'] == 2 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('to_users') ?>
        </div>
        <div class="formblock">
            <label><?= __('arrangement') ?></label><br/>
            <input type="radio" name="type" value="0" <?= (!$this->res['type'] ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('endwise') ?><br/>
            <input type="radio" name="type" value="1" <?= ($this->res['type'] == 1 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('above_content') ?><br/>
            <input type="radio" name="type" value="2" <?= ($this->res['type'] == 2 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('below_content') ?><br/>
            <input type="radio" name="type" value="3" <?= ($this->res['type'] == 3 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('below') ?>
        </div>
        <div class="formblock">
            <label><?= __('placing') ?></label><br/>
            <input type="radio" name="layout" value="0" <?= (!$this->res['layout'] ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('link_add_placing_all') ?><br/>
            <input type="radio" name="layout" value="1" <?= ($this->res['layout'] == 1 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('link_add_placing_front') ?><br/>
            <input type="radio" name="layout" value="2" <?= ($this->res['layout'] == 2 ? 'checked="checked"' : '') ?>/>&nbsp;<?= __('link_add_placing_child') ?>
        </div>
        <div class="formblock">
            <label><?= __('links_allocation') ?></label><br/>
            <input type="checkbox" name="bold" <?= ($this->res['bold'] ? 'checked="checked"' : '') ?>/>&nbsp;<b><?= __('font_bold') ?></b><br/>
            <input type="checkbox" name="italic" <?= ($this->res['italic'] ? 'checked="checked"' : '') ?>/>&nbsp;<i><?= __('font_italic') ?></i><br/>
            <input type="checkbox" name="underline" <?= ($this->res['underline'] ? 'checked="checked"' : '') ?>/>&nbsp;<u><?= __('font_underline') ?></u>
        </div>
    </div>
    <div class="phdr"><input type="submit" name="submit" value="<?= ($id ? __('edit') : __('add')) ?>"/>
    </div>
    <input type="hidden" name="form_token" value="<?= $this->form_token ?>"/>
</form>
<p>
    <a href="<?= App::router()->getUri() . 'links/' ?>"><?= __('advertisement') ?></a><br/>
    <a href="<?= App::router()->getUri() ?>"><?= __('admin_panel') ?></a>
</p>