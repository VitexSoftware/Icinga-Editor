<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - přehled kontactů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Přehled kontaktů')));


//    $oUser->addStatusMessage(_('Nemáte definovaný žádný contact'), 'warning');
//    $oPage->columnIII->addItem(new \Ease\TWB\LinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt <i class="icon-edit"></i>')));
$oPage->addItem(new \Ease\TWB\Container(new UI\DataGrid(_('Kontakty'),
    new Engine\Contact)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
