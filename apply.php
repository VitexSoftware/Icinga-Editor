<?php

/**
 * Icinga Editor - generování konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

require_once 'classes/IEImporter.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Generování konfigurace')));
$oPage->addPageColumns();

if ($oUser->getSettingValue('admin')) {
    $forceUserID = $oPage->getRequestValue('force_user_id', 'int');
    if (!is_null($forceUserID)) {
        $originalUserID = $oUser->getUserID();
        EaseShared::user(new EaseUser($forceUserID));
    }
}

$fileName = $oUser->getUserLogin() . '.cfg';

$cfg = fopen(constant('CFG_GENERATED') . '/' . $fileName, 'w');
if ($cfg) {
    fclose($cfg);
    $oUser->addStatusMessage(sprintf(_('konfigurační soubor %s byl znovu vytvořen'), $fileName), 'success');
} else {
    $oUser->addStatusMessage(sprintf(_('konfigurační soubor  %s nebyl znovu vytvořen'), $fileName), 'warning');
}

$generator = new IEImporter();
$generator->writeConfigs($fileName);

$testing = popen("sudo /usr/sbin/icinga -v /etc/icinga/icinga.cfg", 'r');
if ($testing) {
    $errorCount = 0;
    $LineNo = 0;
    $WarningCount = null;
    while (!feof($testing)) {
        $line = fgets($testing);
        $LineNo++;

        if (($line === false) && ($LineNo == 1)) {
            $errorLine = $oPage->addItem(new EaseHtmlDivTag(null, '<span class="label label-important">' . _('Chyba:') . '</span>', array('class' => 'alert alert-danger')));
            $oUser->addStatusMessage(_('Kontrola konfigurace nevrátila výsledek.'), 'error');
            $errorLine->addItem(_('Zkontroluj prosím zdlali nechybí potřebný fragment v /etc/sudoers:'));
            $errorLine->addItem(new EaseHtmlDivTag(null, 'User_Alias APACHE = www-data'));
            $errorLine->addItem(new EaseHtmlDivTag(null, 'Cmnd_Alias ICINGA = /usr/sbin/icinga, /etc/init.d/icinga'));
            $errorLine->addItem(new EaseHtmlDivTag(null, 'APACHE ALL = (ALL) NOPASSWD: ICINGA'));
            break;
        }

        if (strstr($line, 'Error:')) {
            $line = str_replace('Error:', '', $line);
            $errorLine = $oPage->addItem(new EaseHtmlDivTag(null, '<span class="label label-important">' . _('Chyba:') . '</span>', array('class' => 'alert alert-danger')));

            $keywords = preg_split("/['(.*)']+/", $line);
            switch (trim($keywords[0])) {
                case 'Service notification period':
                    $errorLine->addItem(' <a href="timeperiods.php">' . _('Notifikační perioda') . '</a> služeb ');
                    $errorLine->addItem(new EaseHtmlATag('timeperiod.php?timeperiod_name=' . $keywords[1], $keywords[1]));
                    break;
                case 'Host notification period':
                    $errorLine->addItem(' <a href="timeperiods.php">' . _('Notifikační perioda') . '</a> hostů');
                    $errorLine->addItem(new EaseHtmlATag('timeperiod.php?timeperiod_name=' . $keywords[1], $keywords[1]));
                    break;

                default:
                    $errorLine->addItem($line);
                    break;
            }

            if (isset($keywords[2])) {
                switch (trim($keywords[2])) {
                    case 'specified for contact':
                        $errorLine->addItem(' specifikovaná pro kontakt ');
                        $contact = new IEContact($keywords[3]);
                        $errorLine->addItem(new EaseHtmlATag('contact.php?contact_id=' . $contact->getMyKey(), $keywords[3]));
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
            $keywords = preg_split("/'|\(|\)| - Line /", $line);
            $errorLine = $oPage->addItem(new EaseHtmlDivTag(null, '<span class="label label-error">' . _('Chyba v konfiguračním souboru'), array('class' => 'alert alert-danger')));
            $errorLine->addItem(new EaseHtmlATag('cfgfile.php?file=' . $keywords[1] . '&line=' . $keywords[3], $keywords[1]));
            $errorLine->addItem($keywords[4]);
            $errorCount++;
        }

        if (strstr($line, 'Warning:')) {

            if (strstr($line, 'has no services associated with it!')) {
                preg_match("/\'(.*)\'/", $line, $keywords);
                $host = & $generator->IEClasses['host'];
                $host->setmyKeyColumn($host->nameColumn);
                $host->loadFromMySql($keywords[1]);
                $host->resetObjectIdentity();
                $line = '<span class="label label-warning">' . _('Varování:') . '</span> Host ' . '<a href="host.php?host_id=' . $host->getMyKey() . '">' . $host->getName() . '</a> ' . _('nemá přiřazené žádné služby');
            } else {
                $line = preg_replace("/\'([a-zA-Z0-9\.]*)\'/", '<a href="search.php?search=$1">$1</a>', $line);
                $line = str_replace('Warning:', '<span class="label label-warning">' . _('Varování:') . '</span>', $line);
            }

            //Duplicate definition found for command 'check_ping' (config file '/etc/icinga/generated/command_check_ping_vitex.cfg', starting on line 1)
            $oPage->addItem(new EaseHtmlDivTag(null, $line, array('class' => 'alert alert-warning')));
        }

        if (strstr($line, 'Total Warnings')) {
            list($Msg, $WarningCount) = explode(':', $line);
            if (intval(trim($WarningCount))) {
                $oUser->addStatusMessage(sprintf(_('celkem %s varování'), $WarningCount), 'warning');
            } else {
                $oUser->addStatusMessage(_('test proběhl bez varování'), 'success');
            }
        }
        if (strstr($line, 'Total Errors')) {
            list($Msg, $errorCount) = explode(':', $line);
            if (intval(trim($errorCount))) {
                $oUser->addStatusMessage(sprintf(_('celkem %s chyb'), $errorCount), 'warning');
            } else {
                $oUser->addStatusMessage(_('test proběhl bez chyb'), 'success');
            }
        }
    }
    fclose($testing);

    if (!intval($errorCount) && !is_null($WarningCount)) {
        if (IECfg::reloadIcinga()) {
            $oPage->columnI->addItem(_('Všechny vaše konfigurační soubory byly přegenerovány'));
            $oPage->columnII->addItem(new EaseTWBLinkButton('main.php', _('Hotovo') . ' ' . EaseTWBPart::GlyphIcon('ok-sign'), 'success'));
            EaseShared::user()->setSettingValue('unsaved', false);
        }
    }
}

if ($oUser->getSettingValue('admin') && isset($originalUserID)) {
    EaseShared::user(new EaseUser($originalUserID));
    EaseShared::user()->loginSuccess();
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
