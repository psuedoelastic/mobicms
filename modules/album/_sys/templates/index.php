<!-- Заголовок раздела -->
<div class="titlebar">
    <div><h1><?= __('photo_albums') ?></h1></div>
</div>

<ul class="nav nav-pills nav-stacked">
    <li class="title"><?= __('new') ?></li>
    <li><a href="<?= $this->link ?>?act=new"><i class="pictures fw lg"></i><?= __('photos') ?><i class="icn-arrow right"></i><span class="badge badge-right"><?= $this->new ?></span></a></li>
    <li><a href="<?= $this->link ?>/top?act=last_comm"><i class="comments fw lg"></i><?= __('comments') ?><i class="icn-arrow right"></i></a></li>
    <li class="title"><?= __('albums') ?></li>
    <li><a href="<?= $this->link ?>?act=users&amp;mod=boys"><i class="male fw lg"></i><?= __('mans') ?><i class="icn-arrow right"></i><span class="badge badge-right"><?= $this->count_m ?></span></a></li>
    <li><a href="<?= $this->link ?>?act=users&amp;mod=girls"><i class="female fw lg"></i><?= __('womans') ?><i class="icn-arrow right"></i><span class="badge badge-right"><?= $this->count_w ?></span></a></li>
    <?php if (App::user()->id): ?>
        <li><a href="<?= $this->link ?>?act=list"><i class="picture fw lg"></i><?= __('my_album') ?><i class="icn-arrow right"></i><span class="badge badge-right"><?= $this->count_my ?></span></a></li>
    <?php endif ?>
    <li class="title"><?= __('rating') ?></li>
    <li><a href="<?= $this->link ?>/top"><i class="podium fw lg"></i><?= __('top_votes') ?><i class="icn-arrow right"></i></a></li>
    <li><a href="<?= $this->link ?>/top?act=downloads"><i class="podium fw lg"></i><?= __('top_downloads') ?><i class="icn-arrow right"></i></a></li>
    <li><a href="<?= $this->link ?>/top?act=views"><i class="podium fw lg"></i><?= __('top_views') ?><i class="icn-arrow right"></i></a></li>
    <li><a href="<?= $this->link ?>/top?act=comments"><i class="podium fw lg"></i><?= __('top_comments') ?><i class="icn-arrow right"></i></a></li>
    <li><a href="<?= $this->link ?>/top?act=trash"><i class="podium fw lg"></i><?= __('top_trash') ?><i class="icn-arrow right"></i></a></li>
</ul>