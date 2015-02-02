<?php

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Průvodce založením hosta')));

$oPage->addCss('
.btn-xlarge{
  position: relative;
  vertical-align: center;
  margin: 30px;
  font-size: 30px;
  color: white;
  text-align: center;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
  border: 0;
  border-bottom: 3px solid;
  cursor: pointer;
  -webkit-box-shadow: inset 0 -3px;
  box-shadow: inset 0 -3px;
}
.btn-xlarge:active {
  top: 2px;
  outline: none;
  -webkit-box-shadow: none;
  box-shadow: none;
}
.btn-xlarge:hover {

}
    ');

$oPage->columnI->addItem(
    new EaseTWBPanel(_('Aktivní'), 'success', _('Aktivni testy vyžadují aby byla icinga schopná dosáhnout na testovaný stroj.'))
);
$oPage->columnIII->addItem(
    new EaseTWBPanel(_('Pasivní'), 'info', _('Pasivní host zasílá sám na server kde běží icinga výsledky testů'))
);

$oPage->columnII->addItem(new EaseTWBLinkButton('wizard-active-host.php', _('Aktivní'), 'success', array('class' => 'btn-xlarge')));
$oPage->columnII->addItem(new EaseTWBLinkButton('wizard-passive-host.php', _('Pasivní'), 'info', array('class' => 'btn-xlarge')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
