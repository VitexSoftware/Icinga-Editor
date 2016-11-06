<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - config file browser
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$file = $oPage->getRequestValue('file');
$line = $oPage->getRequestValue('line');

if ($oPage->isPosted()) {
    $cfg = $oPage->getRequestValue('cfg');
    if (strlen(trim($cfg))) {
        if (file_put_contents($file, $cfg) === false) {
            $oPage->addStatusMessage(_('Error saving file').': '.$file, 'error');
        } else {
            $oPage->addStatusMessage(sprintf(_('File %s was saved'), $file),
                'success');
        }
    }
}

$oPage->addItem(new UI\PageTop($file));

$oPage->container->addItem(new \Ease\TWB\Panel($file.':'.$line, 'success',
    new UI\FileEditor($file, $line)));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
