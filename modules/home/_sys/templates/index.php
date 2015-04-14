<!-- Заголовок раздела -->
<div class="titlebar">
    <div><h1><?= __('welcome') ?></h1></div>
</div>

<!-- Меню -->
<ul class="nav nav-pills nav-stacked">
    <li class="title"><?= __('information') ?></li>
    <li><a href="news/"><i class="rss fw lg"></i><?= __('news_archive') ?></a></li>
    <li class="title"><?= __('dialogue') ?></li>
    <li><a href="#"><i class="comments fw lg"></i><?= __('guestbook') ?> <span class="label label-warning">planned</span> <span class="badge pull-right">0</span></a></li>
    <li><a href="#"><i class="comment lg fw"></i><?= __('forum') ?> <span class="label label-warning">planned</span> <span class="badge pull-right">0</span></a></li>
    <li class="title"><?= __('useful') ?></li>
    <li><a href="downloads/"><i class="download lg fw"></i><?= __('downloads') ?> <span class="label label-danger">draft</span> <span class="badge pull-right">0</span></a></li>
    <li><a href="#"><i class="books lg fw"></i><?= __('library') ?> <span class="label label-warning">planned</span> <span class="badge pull-right">0</span></a></li>
    <li class="title"><?= __('community') ?></li>
    <li><a href="community/"><i class="user lg fw"></i><?= __('users') ?><span class="badge pull-right"><?= $this->total_users ?></span></a></li>
    <li><a href="#"><i class="picture lg fw"></i><?= __('photo_albums') ?> <span class="label label-warning">planned</span> <span class="badge pull-right">0</span></a></li>
</ul>
