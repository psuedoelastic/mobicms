<?php
/*
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.md (see attached file)
 *
 * @module      IP WHOIS
 * @author      Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version     v.1.0.0 2015-02-01
 */

defined('MOBICMS') or die('Error: restricted access');

$form = new Mobicms\Form\Form(['action' => App::router()->getUri()]);
$form->infoMessages = false;
$query = App::router()->getQuery();

if (isset($query[0])) {
    $form->input['ip'] = $query[0];
    $form->isSubmitted = true;
    $form->isValid = true;
}

$form
    ->title('IP WHOIS')
    ->element('text', 'ip',
        [
            'label'    => __('ip_address'),
            'required' => true
        ]
    )
    ->divider()
    ->element('submit', 'submit',
        [
            'value' => __('search'),
            'class' => 'btn btn-primary'
        ]
    )
    ->html('<a class="btn btn-link" href="' . (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : App::router()->getUri()) . '">' . __('back') . '</a>');

$form->validate('ip', 'ip');

if ($form->process() === true) {
    include_once(__DIR__.'/_sys/classes/WhoisClient.php');
    include_once(__DIR__.'/_sys/classes/Whois.php');
    include_once(__DIR__.'/_sys/classes/IpTools.php');

    $result = (new Whois)->lookup($form->output['ip']);
    $whois = nl2br(implode("\n", $result['rawdata']));

    // Выделяем цветом важные параметры
    $whois = strtr($whois,
        [
            '%'         => '#',
            'inetnum:'  => '<span style="color: #c81237"><strong>inetnum:</strong></span>',
            'netname:'  => '<span style="color: #c81237"><strong>netname:</strong></span>',
            'country:'  => '<span style="color: #c81237"><strong>country:</strong></span>',
            'route:'    => '<span style="color: #c81237"><strong>route:</strong></span>',
            'org-name:' => '<span style="color: #c81237"><strong>org-name:</strong></span>',
            'descr:'    => '<span style="color: #26a51d"><strong>descr:</strong></span>',
            'address:'  => '<span style="color: #26a51d"><strong>address:</strong></span>'
        ]
    );

    $form
        ->divider()
        ->html('<div class="alert alert-neytral"><small>' . $whois . '</small></div>');
}

App::view()->setRawVar('form', $form->display());
App::view()->setTemplate('index.php');