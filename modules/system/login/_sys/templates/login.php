<div class="titlebar<?= App::user()->id ? ' private' : '' ?>">
    <div><h1><?= (App::user()->id ? __('exit') : __('login')) ?></h1></div>
</div>

<div class="content box padding text-center">
    <div style="max-width: 270px; margin: 0 auto">
        <?= $this->form ?>
    </div>
</div>
