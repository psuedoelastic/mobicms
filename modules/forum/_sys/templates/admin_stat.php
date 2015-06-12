<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button"><a href="<?= App::router()->getUri(1) ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('forum') ?>: <?= __('statistics') ?></h1></div>
    <div class="button"></div>
</div>

<div class="content box padding">
    <h3><?= __('administration') ?></h3>
    <table class="table table-bordered">
        <tr>
            <td style="width: 90%"><i class="admin fw"></i><a href="#"><?= __('moders') ?></a></td>
            <td style="width: 1em"><?= $this->total_moders ?></td>
        </tr>
        <tr>
            <td><i class="male fw"></i><a href="#"><?= __('curators') ?></a></td>
            <td><?= $this->total_curators ?></td>
        </tr>
    </table>

    <h3><?= __('common_statistics') ?></h3>

    <div style="overflow: auto">
        <table class="table table-bordered" style="white-space: nowrap">
            <tr style="font-size: 0.8em">
                <td style="width: 60%">&#160;</td>
                <td><?= __('open') ?></td>
                <td><?= __('closed') ?></td>
                <td><?= __('deleted') ?></td>
            </tr>
            <tr>
                <td><i class="folder fw"></i><?= __('categories') ?></td>
                <td><?= $this->total_cat ?></td>
                <td>&#160;</td>
                <td>&#160;</td>
            </tr>
            <tr>
                <td><i class="comments fw"></i><?= __('sections') ?></td>
                <td><?= $this->total_sub ?></td>
                <td>&#160;</td>
                <td>&#160;</td>
            </tr>
            <tr>
                <td><i class="comment fw"></i><?= __('topics') ?></td>
                <td><?= $this->total_thm ?></td>
                <td><?= $this->total_thm_closed ?></td>
                <td><a href="#"><?= $this->total_thm_del ?></a></td>
            </tr>
            <tr>
                <td><i class="edit fw"></i><?= __('messages') ?></td>
                <td><?= $this->total_msg ?></td>
                <td>&#160;</td>
                <td><a href="#"><?= $this->total_msg_del ?></a></td>
            </tr>
            <tr>
                <td><i class="attachment fw"></i><?= __('files') ?></td>
                <td><?= $this->total_files ?></td>
                <td>&#160;</td>
                <td>&#160;</td>
            </tr>
            <tr>
                <td><i class="bar-chart fw"></i><?= __('votes') ?></td>
                <td><?= $this->total_votes ?></td>
                <td>&#160;</td>
                <td>&#160;</td>
            </tr>
        </table>
    </div>

    <h3><?= __('history') ?></h3>
    <div style="overflow: auto">
        <table class="table table-bordered" style="white-space: nowrap">
            <tr style="font-size: 0.8em">
                <td style="width: 60%">&#160;</td>
                <td><?= __('today') ?></td>
                <td><?= __('yesterday') ?></td>
                <td><?= __('for_a_month') ?></td>
                <td><?= __('for_the_last_month') ?></td>
            </tr>
            <tr>
                <td><i class="comment fw"></i><?= __('created_topics') ?></td>
                <td>X</td>
                <td>X</td>
                <td>X</td>
                <td>X</td>
            </tr>
            <tr>
                <td><i class="edit fw"></i><?= __('written_messages') ?></td>
                <td>X</td>
                <td>X</td>
                <td>X</td>
                <td>X</td>
            </tr>
        </table>
    </div>
</div>
