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

$hostgroupID = $oPage->getRequestValue('hostgroup_id', 'int');
$level = $oPage->getRequestValue('level', 'int');

if (is_null($hostgroupID)) {
    $oPage->addStatusMessage(_('Chybné volání mapy skupiny'), 'warning');
    $oPage->redirect('hostgroups.php');
}



$hostgroup = new IEHostgroup($hostgroupID);


if ($oPage->isPosted()) {
    if (isset($_FILES) && count($_FILES)) {
        $tmpfilename = $_FILES['bgimage']['tmp_name'];
    } else {
        if ($oPage->isPosted()) {
            $oPage->addStatusMessage(_('Nebyl vybrán soubor s ikonou hosta'), 'warning');
        }
    }

    if (isset($tmpfilename) || ($_FILES['bgimage']['error'] = 0)) {
        if ($tmpfilename && IEIconSelector::imageTypeOK($tmpfilename)) {
            $newbackground = $hostgroup->saveBackground($tmpfilename, $level);
            if ($newbackground) {
                $hostgroup->saveToMySQL();
            }
        } else {
            if (file_exists($tmpfilename)) {
                unlink($tmpfilename);
            }
            $oPage->addStatusMessage(_('toto není obrázek požadovaného typu'), 'warning');
        }
    }
} else {

    switch ($oPage->getRequestValue('action')) {
        case 'arrange':
            $members = $hostgroup->getMembers();
            if ($members) {
                $x = 50;
                $y = 50;
                $node = new IEHost;
                foreach ($members as $member_id => $member) {
                    $node->updateToMySQL(array($node->myKeyColumn => $member_id, '3d_coords' => "$x,$y,0"));
                    $y = $y + 50;
                    if ($y > 100) {
                        $y = 50;
                        $x = $x + 50;
                    }
                }
                $hostgroup->addStatusMessage(sprintf(_('%s Nodů rozprostřeno'), count($members)), 'success');
                $oPage->redirect('?hostgroup_id=' . $hostgroupID);
                exit();
            }
            break;
        case 'delete':
            $backs = $hostgroup->getDataValue('bgimages');
            unset($backs[$level]);
            $hostgroup->setDataValue('bgimages', $backs);
            if ($hostgroup->saveToMySQL()) {
                $hostgroup->addStatusMessage(_('Vrstva byla odstraněna'), 'success');
            }
            $level = 0;
            $oPage->redirect('?hostgroup_id=' . $hostgroupID);
            exit();
            break;

        default:
            break;
    }
}

$oPage->addItem(new IEPageTop(_('Mapa skupiny hostů') . ' ' . $hostgroup->getName()));

$oPage->addCss('

#netmap { border: 1px solid gray; margin-left: auto; margin-right: auto;}
.node {
  cursor: move;
  fill: #ccc;
  stroke: #000;
  stroke-width: 1.5px;
}

    .link {
        fill: none;
        stroke: #0f0;
        stroke-width: 3px;
        opacity: 0.7;
        marker-end: url(#end-arrow);
    }

    .label {
        text-anchor: middle;
        cursor: pointer;
    }

    .passive {
        fill: blue;
    }

    .active {
        fill: green;
    }

.node.fixed {
  fill: #f00;
}
    ');


$oPage->includeJavascript('/javascript/d3.js');

$oPage->addJavascript("maximizeDiv('#netmap');
$( window ).resize(function() {
    maximizeDiv('#netmap');
});
", null, true);




$levelTabs = new EaseTWBTabs('leveltabs', null);
$zeroLevel = array(_('Zobrazit všechny vrstvy / patra'), new EaseTWBLinkButton('?hostgroup_id=' . $hostgroupID . '&action=arrange', _('Rozprostřít'), 'warning btn-xs'));
$levelTabs->addTab('*', $zeroLevel, (0 == $level));

//$('#" . $levelTabs->partName . " li:eq(" . $level - 1 . ") a').tab('show')

$oPage->addJavaScript("

$('#" . $levelTabs->partName . " a').click(function (e) {
    e.preventDefault();
    var level = $(this).html();
    $(this).tab('show');
    showLevelNodes( level );
});

", null, true);




$bgimages = $hostgroup->getDataValue('bgimages');
$levels = $hostgroup->getLevels();

foreach ($levels as $currentLevel) {
    $levelTab = $levelTabs->addTab($currentLevel, null, ($currentLevel == $level));

    $bgImgUplForm = new EaseTWBForm('bgImgUplForm' . $currentLevel, null, 'POST', null, array('enctype' => 'multipart/form-data', 'class' => 'form-inline'));
    $bgImgUplForm->addInput(new EaseHtmlInputFileTag('bgimage'), _('Obrázek'));
    $bgImgUplForm->addItem(new EaseHtmlInputHiddenTag('level', $currentLevel));
    $bgImgUplForm->addItem(new EaseHtmlInputHiddenTag('hostgroup_id', $hostgroupID));
    $bgImgUplForm->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

    $bgImgUplForm->addItem(new EaseTWBLinkButton('?hostgroup_id=' . $hostgroupID . '&level=' . $currentLevel . '&action=delete', _('Smazat'), 'danger'));

    $levelTab->addItem(new EaseTWBPanel(sprintf(_('Obrázek pozadí pro úroveň %s'), $currentLevel), 'info', $bgImgUplForm));
    if (isset($bgimages[$currentLevel])) {
        $oPage->addCss('.levelbg' . $currentLevel . ' { background-image: url("' . $bgimages[$currentLevel] . '"); } ');
    }
}

$levelTab = $levelTabs->addTab( ++$currentLevel);
$bgImgUplForm = new EaseTWBForm('bgImgUplForm' . $currentLevel, null, 'POST', null, array('enctype' => 'multipart/form-data', 'class' => 'form-inline'));
$bgImgUplForm->addInput(new EaseHtmlInputFileTag('bgimage'), _('Obrázek'));
$bgImgUplForm->addItem(new EaseHtmlInputHiddenTag('level', $currentLevel));
$bgImgUplForm->addItem(new EaseHtmlInputHiddenTag('hostgroup_id', $hostgroupID));
$bgImgUplForm->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$levelTab->addItem(new EaseTWBPanel(sprintf(_('Obrázek pozadí pro úroveň %s'), $currentLevel), 'info', $bgImgUplForm));

$oPage->container->addItem(new EaseHtmlDiv(NULL, array('id' => 'nodeinfo')));

$oPage->container->addItem(new EaseJavaScript(NULL, array('src' => 'js/layouteditor.js')));

$oPage->container->addItem($levelTabs);


$oPage->addItem(new IEPageBottom());

$oPage->draw();

