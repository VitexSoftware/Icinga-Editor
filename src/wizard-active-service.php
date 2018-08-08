<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Service Wizard
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Service Wizard')));
$oPage->addPageColumns();

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
    new \Ease\TWB\Panel(_('Local'), 'success',
        _('Local - only for admin'))
);
$oPage->columnIII->addItem(
    new \Ease\TWB\Panel(_('Remote'), 'info',
        _('Remote'))
);

$oPage->columnII->addItem(new \Ease\TWB\LinkButton('wizard-active-service-local.php',
        _('Local'), 'success', ['class' => 'btn-xlarge']));
$oPage->columnII->addItem(new \Ease\TWB\LinkButton('wizard-passive-service-remote.php',
        _('Remote'), 'info', ['class' => 'btn-xlarge']));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
