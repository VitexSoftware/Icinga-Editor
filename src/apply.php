<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - generování konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';


$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Generování konfigurace')));


if ($oUser->getSettingValue('admin')) {
    $forceUserID = $oPage->getRequestValue('force_user_id', 'int');
    if (!is_null($forceUserID)) {
        $originalUserID = $oUser->getUserID();
        \Ease\Shared::user(new \Ease\User($forceUserID));
    }
}

$fileName = $oUser->getUserLogin().'.cfg';

$cfg = fopen(constant('CFG_GENERATED').'/'.$fileName, 'w');
if ($cfg) {
    fclose($cfg);
    $oUser->addStatusMessage(sprintf(_('konfigurační soubor %s byl znovu vytvořen'),
            $fileName), 'success');
} else {
    $oUser->addStatusMessage(sprintf(_('konfigurační soubor  %s nebyl znovu vytvořen'),
            $fileName), 'warning');
}

$generator = new Engine\Importer();
$generator->writeConfigs($fileName);

$testing = popen("sudo /usr/sbin/icinga -v /etc/icinga/icinga.cfg", 'r');
if ($testing) {
    $errorCount   = 0;
    $line_num     = 0;
    $WarningCount = null;
    while (!feof($testing)) {
        $line = fgets($testing);
        $line = preg_replace("/\'([a-zA-Z0-9\.]*)\'/",
            '<a href="search.php?search=$1">$1</a>', $line);

        $line_num++;

        if (($line === false) && ($line_num == 1)) {
            $errorLine = $oPage->container->addItem(new \Ease\Html\Div('<span class="label label-important">'._('Chyba:').'</span>',
                ['class' => 'alert alert-danger']));
            $oUser->addStatusMessage(_('Kontrola konfigurace nevrátila výsledek.'),
                'error');
            $errorLine->addItem(_('Zkontroluj prosím zdlali nechybí potřebný fragment v /etc/sudoers:'));
            $errorLine->addItem(new \Ease\Html\Div('User_Alias APACHE = www-data'));
            $errorLine->addItem(new \Ease\Html\Div('Cmnd_Alias ICINGA = /usr/sbin/icinga, /etc/init.d/icinga'));
            $errorLine->addItem(new \Ease\Html\Div('APACHE ALL = (ALL) NOPASSWD: ICINGA'));
            break;
        }

        if (strstr($line, 'Error:')) {
            $line      = str_replace('Error:', '', $line);
            $errorLine = $oPage->container->addItem(new \Ease\Html\Div('<span class="label label-important">'._('Chyba:').'</span>',
                ['class' => 'alert alert-danger']));

            $keywords = preg_split("/['(.*)']+/", $line);
            switch (trim($keywords[0])) {
                case 'Service notification period':
                    $errorLine->addItem(' <a href="timeperiods.php">'._('Notifikační perioda').'</a> služeb ');
                    $errorLine->addItem(new \Ease\Html\ATag('timeperiod.php?timeperiod_name='.$keywords[1],
                        $keywords[1]));
                    break;
                case 'Host notification period':
                    $errorLine->addItem(' <a href="timeperiods.php">'._('Notifikační perioda').'</a> hostů');
                    $errorLine->addItem(new \Ease\Html\ATag('timeperiod.php?timeperiod_name='.$keywords[1],
                        $keywords[1]));
                    break;

                default:
                    $errorLine->addItem($line);
                    break;
            }

            if (isset($keywords[2])) {
                switch (trim($keywords[2])) {
                    case 'specified for contact':
                        $errorLine->addItem(' specifikovaná pro kontakt ');
                        $contact = new Engine\Contact($keywords[3]);
                        $errorLine->addItem(new \Ease\Html\ATag('contact.php?contact_id='.$contact->getMyKey(),
                            $keywords[3]));
                        break;

                    default:
                        break;
                }
            }
            if (isset($keywords[4])) {
                switch (trim($keywords[4])) {
                    case 'is not defined anywhere!':
                        $errorLine->addItem(' není nikde definován/a ');
                        break;
                }
            }
            //$OPage->addItem('<pre>' . EaseBrick::printPreBasic($keywords) . '</pre>');
        }

        if (strstr($line, 'Error in configuration file')) {

            $line = str_replace('Warning:',
                '<span class="label label-error">'._('Chyba v konfiguračním souboru').'</span>',
                $line);

            $oPage->container->addItem(new \Ease\Html\Div($line,
                ['class' => 'alert alert-danger']));
            $errorCount++;
        }

        if (strstr($line, 'Warning:')) {

            if (strstr($line, 'has no services associated with it!')) {
                preg_match("/\'(.*)\'/", $line, $keywords);
                $host = & $generator->Classes['host'];
                $host->setmyKeyColumn($host->nameColumn);
                $host->loadFromSQL($keywords[1]);
                $host->resetObjectIdentity();
                $line = '<span class="label label-warning">'._('Varování:').'</span> Host '.'<a href="host.php?host_id='.$host->getMyKey().'">'.$host->getName().'</a> '._('nemá přiřazené žádné služby');
            } else {
                $line = str_replace('Warning:',
                    '<span class="label label-warning">'._('Varování:').'</span>',
                    $line);
            }

            //Duplicate definition found for command 'check_ping' (config file '/etc/icinga/generated/command_check_ping_vitex.cfg', starting on line 1)
            $oPage->container->addItem(new \Ease\Html\Div($line,
                ['class' => 'alert alert-warning']));
        }

        if (strstr($line, 'Total Warnings')) {
            list($msg, $WarningCount) = explode(':', $line);
            if (intval(trim($WarningCount))) {
                $oUser->addStatusMessage(sprintf(_('celkem %s varování'),
                        $WarningCount), 'warning');
            } else {
                $oUser->addStatusMessage(_('test proběhl bez varování'),
                    'success');
            }
        }
        if (strstr($line, 'Total Errors')) {
            list($msg, $errorCount) = explode(':', $line);
            if (intval(trim($errorCount))) {
                $oUser->addStatusMessage(sprintf(_('celkem %s chyb'),
                        $errorCount), 'warning');
            } else {
                $oUser->addStatusMessage(_('test proběhl bez chyb'), 'success');
            }
        }
    }
    fclose($testing);

    if (!intval($errorCount) && !is_null($WarningCount)) {
        if (Vitexus/icinga_configurator::reloadIcinga()) {
            $oPage->container->addItem(_('Všechny vaše konfigurační soubory byly přegenerovány'));
            $oPage->container->addItem(new \Ease\TWB\LinkButton('main.php',
                _('Hotovo').' '.\Ease\TWB\Part::GlyphIcon('ok-sign'), 'success'));
            \Ease\Shared::user()->setSettingValue('unsaved', false);
        }
    }
}

if ($oUser->getSettingValue('admin') && isset($originalUserID)) {
    \Ease\Shared::user(new \Ease\User($originalUserID));
    \Ease\Shared::user()->loginSuccess();
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
