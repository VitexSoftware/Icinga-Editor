<?php

/**
 * Icinga Editor - vlastnosti nodu v topologii
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();
$hostID = intval(str_replace('host_', '', $oPage->getRequestValue('host_id')));
$hostgroupID = $oPage->getRequestValue('hostgroup_id', 'int');

if (is_null($hostID)) {
    die('host_id ?');
}

$oPage->addCss('.panel-body { padding: 0px; } ');

$host = new IEHost($hostID);

if ($oPage->isPosted()) {
    $x = $oPage->getRequestValue('x');
    $y = $oPage->getRequestValue('y');
    $z = $oPage->getRequestValue('z');

    if ($z && !$x && !$y) {
        $coords = $host->getDataValue('3d_coords');
        if (strstr($coords, ',')) {
            list($x, $y, ) = explode(',', $coords);
        } else {
            $x = $y = 0;
        }
    }

    if (is_null($z)) {
        $coords = $host->getDataValue('3d_coords');
        if (strstr($coords, ',')) {
            list($xx, $yy, $z ) = explode(',', $coords);
        } else {
            $z = 1;
        }
    }

    $host->setDataValue('3d_coords', "$x,$y,$z");
    $host->saveToSQL();
} else {
    $coords = $host->getDataValue('3d_coords');
    if (strstr($coords, ',')) {
        list($x, $y, $z) = explode(',', $coords);
    } else {
        $x = $y = 0;
        $z = 1;
    }
    $posForm = new EaseTWBForm('posForm');
    $posForm->addItem(new EaseHtmlDiv('<strong>' . $host->getName() . '</strong>'));

    if (!is_null($hostgroupID)) {
        $hostgroup = new IEHostgroup($hostgroupID);
        $levels = $hostgroup->getLevels();

        $radios = $posForm->addItem(new EaseHtmlRadiobuttonGroup('level', $levels));
        $radios->checked = $z - 1;
        $radios->finalize();


        foreach ($radios->pageParts as $radioID => $radio) {

            if (is_object($radio) && ($radio->getTagType() == 'input')) {
                $radios->pageParts[$radioID]->setTagProperties(array(
                  'onClick' => "switchNodeLevel(this)",
                  'data-level' => str_replace('level', '', $radio->getTagID()),
                  'data-host_id' => $hostID,
                  'data-hostgroup_id' => $hostgroupID
                ));
            }
        }
    }


//    $posForm->addInput($ix = new EaseHtmlInputNumberTag('X', $x), _('X'), _('Osa X'), _('Celé číslo 0 a větší'));
//    $ix->addTagClass('input-xs');
//    $posForm->addInput($iy = new EaseHtmlInputNumberTag('Y', $y), _('Y'), _('Osa Y'), _('Celé číslo 0 a větší'));
//    $iy->addTagClass('input-xs');
//    $posForm->addInput($iz = new EaseHtmlInputNumberTag('Z', $z), _('Z'), _('Patro/Vrstva'), _('Celé číslo 0 a větší'));
//    $iz->addTagClass('input-xs');


    $posForm->setTagID('nodepopup');

    $posForm->draw();
    exit();
}
$oPage->draw();
