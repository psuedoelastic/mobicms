<!-- Заголовок раздела -->
<div class="titlebar">
    <div class="button"></div>
    <div><h1><?= __('who_on_site') ?></h1></div>
    <div class="separator"></div>
    <div class="button">
        <!-- Кнопка меню -->
        <button type="button" class="slider-button" title="<?= __('control') ?>">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
</div>

<!-- Слайдер с Админскими кнопками -->
<div class="content slider close">
    <ul class="nav nav-pills nav-justified">
        <li>
            <a href="<?= $this->uri ?>"><i class="user fw"></i><?= __('users') ?></a>
        </li>
        <li>
            <a href="<?= $this->uri ?>history/"><i class="sort-amount-desc fw"></i><?= __('history') ?></i></a>
        </li>
        <li class="active">
            <a href="<?= $this->uri ?>guests/"><i class="group fw"></i><?= __('guests') ?></a>
        </li>
        <li>
            <a href="<?= $this->uri ?>ip/"><i class="bolt fw"></i><?= __('ip_activity') ?></a>
        </li>
    </ul>
</div>

<!-- Список онлайн -->
<div class="content box m-list">
    <?php if (App::user()->id || App::cfg()->sys->usr_view_online): ?>
        <h2><?= $this->list_header ?></h2>
        <ul class="striped">
            <?php if (isset($this->list)): ?>
                <?php foreach ($this->list as $guest): ?>
                    <li>
                        <!-- Кнопка выпадающего меню -->
                        <div>
                            <a href="#" class="lbtn dropdown dropdown-toggle" data-toggle="dropdown"></a>
                            <ul class="dropdown-menu" role="menu">
                                <li class="dropdown-header">IP Whois</li>
                                <li>
                                    <a href="<?= App::cfg()->sys->homeurl ?>whois/<?= long2ip($guest['ip']) ?>"><i class="search fw"></i>IP</a>
                                </li>
                                <?php if (isset($guest['ip_via_proxy']) && !empty($guest['ip_via_proxy'])): ?>
                                    <li>
                                        <a href="<?= App::cfg()->sys->homeurl ?>whois/<?= long2ip($guest['ip_via_proxy']) ?>"><i class="search fw"></i>IP via Proxy</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </div>
                        <a href="" class="mlink has-lbtn">
                            <dl class="description">
                                <dt class="wide">
                                    V: <?= $guest['views'] ?><br>
                                    M: <?= $guest['movings'] ?>
                                </dt>
                                <dd>
                                    <div class="small inline margin"><?= $guest['user_agent'] ?></div>
                                    <div class="small">
                                        <?php if (isset($guest['ip_via_proxy']) && !empty($guest['ip_via_proxy'])): ?>
                                            <span class="danger"><?= long2ip($guest['ip']) ?></span> &raquo; <?= long2ip($guest['ip_via_proxy']) ?>
                                        <?php else: ?>
                                            <?= long2ip($guest['ip']) ?>
                                        <?php endif ?>
                                    </div>
                                </dd>
                            </dl>
                        </a>
                    </li>
                <?php endforeach ?>
            <?php else: ?>
                <li style="text-align: center; padding: 27px"><?= __('list_empty') ?></li>
            <?php endif ?>
        </ul>
        <h3><?= __('total') ?>:&#160;<?= $this->total ?></h3>
    <?php else: ?>
        <div class="content box padding text-center">
            <?= __('access_guest_forbidden') ?>
        </div>
    <?php endif ?>
</div>
