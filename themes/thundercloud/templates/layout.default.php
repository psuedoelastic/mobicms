<?php
\App::view()->setCss('mobicms.min.css', ['first' => true, 'version' => 1]);
\App::view()->setJs('mobicms.min.js');
?>
<!DOCTYPE html>
<html lang="<?= \App::cfg()->sys->lng ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes">
    <meta name="keywords" content="<?= htmlspecialchars(App::cfg()->sys->meta_key) ?>"/>
    <meta name="description" content="<?= htmlspecialchars(App::cfg()->sys->meta_desc) ?>"/>
    <meta name="HandheldFriendly" content="true"/>
    <meta name="MobileOptimized" content="width"/>
    <meta content="yes" name="apple-mobile-web-app-capable"/>
    <title><?= isset($this->pagetitle) ? $this->pagetitle : App::cfg()->sys->home_title ?></title>
    <link rel="shortcut icon" href="<?= App::image('favicon.ico', [], false, false) ?>"/>
    <link rel="alternate" type="application/rss+xml" title="<?= __('site_news', 1) ?>" href="<?= App::cfg()->sys->homeurl ?>rss"/>
    <?= $this->loadHeader() ?>
</head>
<body>
<div class="container">
    <a name="top"></a>

    <!-- Панель навигации -->
    <?php include($this->getPath('include.navbar.php')) ?>

    <!-- Содержимое -->
    <div class="content">
        <?php $this->loadTemplate() ?>
        <?= $this->loadRawContent(true) ?>
    </div>

    <!-- Нижняя панель инструментов -->
    <ul class="bottom">
        <li><a href="<?= App::cfg()->sys->homeurl ?>online/"><i class="user fw"></i><?= Counters::usersOnline() ?> :: <?= Counters::guestsOnline() ?></a></li>
        <li><a href="<?= App::cfg()->sys->homeurl ?>help/"><i class="life-bouy fw"></i>FAQ</a></li>
        <li><a href="#top"><i class="arrow-up fw"></i><?= __('up') ?></a></li>
    </ul>

    <!-- Информация внизу страницы -->
    <div class="text-center text-primary small">
        <div><?= App::cfg()->sys->copyright ?></div>
        <div class="profiler">
            <?php if (App::cfg()->sys->profiling_generation): ?>
                <div>Generation: <?= round((microtime(true) - START_TIME), 4) ?> sec</div>
            <?php endif ?>
            <?php if (App::cfg()->sys->profiling_memory): ?>
                <div>Memory: <?= round((memory_get_usage() - START_MEMORY) / 1024, 2) ?> kb</div>
            <?php endif ?>
        </div>
        <div><a href="http://mobicms.net">mobiCMS</a></div>
    </div>
</div>
<!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>-->
<script src="<?= App::cfg()->sys->homeurl ?>assets/js/jquery-2.1.3.min.js"></script>
<?= $this->loadFooter() ?>
</body>
</html>