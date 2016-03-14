<?php
namespace Icinga\Editor;

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

$hostId = $oPage->getRequestValue('host_id', 'int');
$host = new IEHost($hostId);

$generator = new IENSCPConfigGenerator($host);
$generator->getCfg();
