<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - network map dasource
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$hostgroupID = $oPage->getRequestValue('hostgroup_id', 'int');

$format = $oPage->getRequestValue('format');
if (!$format) {
    $format = 'dot';
}


$oPage->onlyForLogged();

if (is_null($hostgroupID) || ($hostgroupID == 'null')) {
    $gv = new UI\HostMap();
} else {
    $gv = new UI\HostgroupMap($hostgroupID);
}
error_reporting(E_ALL ^ E_STRICT);

$gv->image($format);


