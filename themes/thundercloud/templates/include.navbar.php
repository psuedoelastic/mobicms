<nav class="navbar navbar-inverse" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <!-- Кнопка меню -->
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!-- Логотип -->
            <a class="navbar-brand" href="<?= App::cfg()->sys->homeurl ?>"><?= App::image('logo.png', ['alt' => 'mobiCMS']) ?></a>
        </div>

        <!-- Ссылки слева -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="visible-xs-inline-block"><i class="sitemap fw"></i><?= __('go_to') ?>&nbsp;</span><span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <?php if (App::router()->getModule() != 'home'): ?>
                            <li><a href="<?= App::cfg()->sys->homeurl ?>"><i class="home fw"></i><?= __('homepage') ?></a></li>
                        <?php endif ?>
                        <li><a href="<?= App::cfg()->sys->homeurl ?>news/"><i class="rss fw"></i><?= __('news') ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#"><i class="comments fw"></i><?= __('guestbook') ?></a></li>
                        <li><a href="<?= App::cfg()->sys->homeurl ?>forum/"><i class="comment fw"></i><?= __('forum') ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#"><i class="download fw"></i><?= __('downloads') ?></a></li>
                        <li><a href="#"><i class="books fw"></i><?= __('library') ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#"><i class="group fw"></i><?= __('users') ?></a></li>
                        <li><a href="#"><i class="picture fw"></i><?= __('photo_albums') ?></a></li>
                    </ul>
                </li>
            </ul>

            <!-- Ссылки справа -->
            <ul class="nav navbar-nav navbar-right">
                <?php if (App::user()->id): ?>
                    <?php if (App::user()->rights): ?>
                        <li<?= (App::router()->getModule() == 'admin' ? ' class="active"' : '') ?>>
                            <a href="<?= App::cfg()->sys->homeurl ?>admin/"><i class="cogs fw"></i><?= __('admin_panel') ?></a>
                        </li>
                    <?php endif ?>
                    <li<?= (App::router()->getModule() == 'mail' ? ' class="active"' : '') ?>>
                        <a href="#"><i class="envelope fw"></i><?= __('mail') ?> <span class="label label-warning">planned</span></a>
                    </li>
                    <li class="dropdown<?= (App::router()->getModule() == 'profile' ? ' active' : '') ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="user fw"></i><?= App::user()->data['nickname'] ?> <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?= App::cfg()->sys->homeurl ?>profile/<?= App::user()->id ?>/"><i class="user fw"></i><?= __('profile') ?></a></li>
                            <li class="divider"></li>
                            <li><a href="<?= App::cfg()->sys->homeurl ?>profile/<?= App::user()->id ?>/option/"><i class="cogs fw"></i><?= __('settings') ?></a></li>
                            <li class="divider"></li>
                            <li><a href="<?= App::cfg()->sys->homeurl ?>login/"><i class="sign-out fw"></i><?= __('exit') ?></a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <?php if (App::cfg()->sys->usr_reg_allow): ?>
                        <li<?= (App::router()->getModule() == 'registration' ? ' class="active"' : '') ?>>
                            <a href="<?= App::cfg()->sys->homeurl ?>registration/"><i class="pencil fw"></i><?= __('registration') ?></a>
                        </li>
                    <?php endif ?>
                    <li<?= (App::router()->getModule() == 'login' ? ' class="active"' : '') ?>>
                        <a href="<?= App::cfg()->sys->homeurl ?>login/"><i class="sign-in fw"></i><?= __('login') ?></a>
                    </li>
                <?php endif ?>
            </ul>
        </div>
    </div>
</nav>