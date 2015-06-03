<!-- Заголовок раздела -->
<div class="titlebar <?= Users::$data['id'] == App::user()->id ? 'private' : '' ?>">
    <div class="button"><a href="<?= App::router()->getUri(2) ?>"><i class="arrow-circle-left lg"></i></a></div>
    <div class="separator"></div>
    <div><h1><?= (App::user()->id && Users::$data['id'] == App::user()->id ? __('my_profile') : __('user_profile')) ?></h1></div>
    <div class="button"></div>
</div>

<!-- информация о пользователе -->
<?php if (!isset($this->hideuser)): ?>
    <!-- Информация о пользователе -->
    <?php $user = Users::$data; ?>
    <div class="info-block m-list">
        <ul><?php include_once $this->getPath('include.user.php') ?></ul>
    </div>
<?php endif ?>

<!-- График репутации -->
<ul class="nav nav-pills nav-stacked">
    <li class="title"><?= __('reputation') ?></li>
    <li>
        <a href="<?= App::router()->getUri(2) ?>">
            <table style="width: 100%">
                <tr>
                    <td class="progress-counter"><?= $this->reputation_total ?></td>
                    <td style="width: 100%; padding-bottom: 6px">
                        <!-- Отлично -->
                        <div class="reputation-desc">
                            <?= __('reputation_excellent') ?>: <?= $this->counters['a'] ?>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" style="width: <?= $this->reputation['a'] ?>%;"></div>
                        </div>

                        <!-- Хорошо -->
                        <div class="reputation-desc">
                            <?= __('reputation_good') ?>: <?= $this->counters['b'] ?>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-info" style="width: <?= $this->reputation['b'] ?>%;"></div>
                        </div>

                        <!-- Нейтрально -->
                        <div class="reputation-desc">
                            <?= __('reputation_neutrally') ?>: <?= $this->counters['c'] ?>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-neytral" style="width: <?= $this->reputation['c'] ?>%;"></div>
                        </div>

                        <!-- Плохо -->
                        <div class="reputation-desc">
                            <?= __('reputation_bad') ?>: <?= $this->counters['d'] ?>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-warning" style="width: <?= $this->reputation['d'] ?>%;"></div>
                        </div>

                        <!-- Очень плохо -->
                        <div class="reputation-desc">
                            <?= __('reputation_very_bad') ?>: <?= $this->counters['e'] ?>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-danger" style="width: <?= $this->reputation['e'] ?>%;"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </a>
    </li>
</ul>

<!-- Форма -->
<?php if (isset($this->form)): ?>
    <div class="content box padding">
        <?= $this->form ?>
    </div>
<?php endif ?>
