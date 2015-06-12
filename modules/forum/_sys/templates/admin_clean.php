<!-- Заголовок раздела -->
<div class="titlebar admin">
    <div class="button"><a href="<?= App::router()->getUri(1) ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= __('forum') ?>: <?= __('clear') ?></h1></div>
    <div class="button"></div>
</div>

<ul class="nav nav-pills nav-stacked">
    <li><a href="<?= $this->uri ?>hidden_posts/"><i class="eye-blocked lg fw"></i><?= __('hidden_posts') ?><span class="badge badge-right"><?= $this->total_msg_del ?></span></a></a></li>
    <li><a href="<?= $this->uri ?>hidden_topics/"><i class="eye-blocked lg fw"></i><?= __('hidden_topics') ?><span class="badge badge-right"><?= $this->total_thm_del ?></span></a></a></li>
</ul>