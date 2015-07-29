<?php

/**
 * Icinga Editor - přehled kontactů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContact.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Přehled kontaktů')));


//    $oUser->addStatusMessage(_('Nemáte definovaný žádný contact'), 'warning');
//    $oPage->columnIII->addItem(new EaseTWBLinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt <i class="icon-edit"></i>')));
$oPage->addItem(new EaseTWBContainer(new IEDataGrid(_('Kontakty'), new IEContact)));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
