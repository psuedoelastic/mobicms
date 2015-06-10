<li>
    <?php if (isset($user)): ?>
        <!-- Кнопка выпадающего меню -->
        <?php if (App::user()->rights): ?>
            <div>
                <a href="#" class="lbtn dropdown dropdown-toggle" data-toggle="dropdown"></a>
                <ul class="dropdown-menu" role="menu">
                    <li class="dropdown-header">IP Whois</li>
                    <li>
                        <a href="<?= App::cfg()->sys->homeurl ?>whois/<?= long2ip($user['ip']) ?>"><i class="search fw"></i>IP</a>
                    </li>
                    <?php if (isset($user['ip_via_proxy']) && !empty($user['ip_via_proxy'])): ?>
                        <li>
                            <a href="<?= App::cfg()->sys->homeurl ?>whois/<?= long2ip($user['ip_via_proxy']) ?>"><i class="search fw"></i>IP via Proxy</a>
                        </li>
                    <?php endif ?>
                </ul>
            </div>
        <?php endif ?>

        <!-- Ссылка на профиль, контейнер -->
        <a href="<?= App::cfg()->sys->homeurl.'profile/'.$user['id'] ?>" class="mlink<?= App::user()->rights ? ' has-lbtn' : '' ?>">
            <dl class="description">
                <dt>
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= $user['avatar'] ?>"/>
                    <?php else: ?>
                        <?= App::image('empty_user.png') ?>
                    <?php endif; ?>
                </dt>
                <dd>
                    <div class="header">
                        <?php if ($user['sex']): ?>
                            <span class="sex<?= (time() > $user['last_visit'] + 300 ? '' : ' online') ?>">
                                <i class="<?= ($user['sex'] == 'm' ? '' : 'fe') ?>male lg"></i>
                            </span>
                        <?php endif ?>
                        <?= $user['nickname'] ?>
                    </div>
                    <?php if (isset($user['status']) && !empty($user['status'])): ?>
                        <div class="small bold colored"><?= $user['status'] ?></div>
                    <?php endif ?>
                    <?php if ($user['last_visit'] < time() - 300): ?>
                        <div><?= __('last_visit').': '.Functions::displayDate($user['last_visit']) ?></div>
                    <?php endif ?>
                    <?php if (App::user()->rights): ?>
                        <div class="small inline margin"><?= $user['user_agent'] ?></div>
                        <div class="small">
                            <?php if (isset($user['ip_via_proxy']) && !empty($user['ip_via_proxy'])): ?>
                                <span class="danger"><?= long2ip($user['ip']) ?></span> &raquo; <?= long2ip($user['ip_via_proxy']) ?>
                            <?php else: ?>
                                <?= long2ip($user['ip']) ?>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </dd>
            </dl>
        </a>
    <?php else: ?>
        <p style="text-align: center">ERROR: <strong>$user</strong> variable is not defined</p>
    <?php endif ?>
</li>