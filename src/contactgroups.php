<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - přehled kontaktů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Kontaktní skupiny')));

$oPage->container->addItem(new UI\DataGrid(_('Kontaktní skupiny'), new Engine\IEContactgroup));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
