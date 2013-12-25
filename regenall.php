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

$oPage->onlyForAdmin();

$oPage->addItem(new IEPageTop(_('Přegenerování veškeré konfigurace')));

system('rm '.constant('CFG_GENERATED').'/*');

$OriginalUserID = $oUser->getUserID();
$Users = EaseShared::user()->getAllFromMySQL();
foreach ($Users as $UserData) {
    EaseShared::user(new EaseUser(intval($UserData['id'])));
    EaseShared::user()->loginSuccess();


    $FileName = $oUser->getUserLogin() . '.cfg';

    $Cfg = fopen(constant('CFG_GENERATED') . '/' . $FileName, 'w');
    if ($Cfg) {
        fclose($Cfg);
        $oUser->addStatusMessage(sprintf(_('konfigurační soubor %s byl znovu vytvořen'), $FileName), 'success');
    } else {
        $oUser->addStatusMessage(sprintf(_('konfigurační soubor  %s nebyl znovu vytvořen'), $FileName), 'warning');
    }

    $Generator = new IEImporter();
    $Generator->writeConfigs($FileName);

}
EaseShared::user(new EaseUser($OriginalUserID));
EaseShared::user()->loginSuccess();
$oUser->setSettingValue('admin',TRUE);

    $Testing = popen("sudo /usr/sbin/icinga -v /etc/icinga/icinga.cfg", 'r');
    if ($Testing) {
        $ErrorCount = 0;
        $LineNo = 0;
        $WarningCount = null;
        while (!feof($Testing)) {
            $Line = fgets($Testing);
            $LineNo++;

            if (($Line === false) && ($LineNo == 1)) {
                $ErrorLine = $oPage->addItem(new EaseHtmlDivTag(null, '<span class="label label-important">' . _('Chyba:') . '</span>', array('class' => 'alert alert-error')));
                $oUser->addStatusMessage(_('Kontrola konfigurace nevrátila výsledek.'), 'error');
                $ErrorLine->addItem(_('Zkontroluj prosím zdlali nechybí potřebný fragment v /etc/sudoers:'));
                $ErrorLine->addItem(new EaseHtmlDivTag(null, 'User_Alias APACHE = www-data'));
                $ErrorLine->addItem(new EaseHtmlDivTag(null, 'Cmnd_Alias ICINGA = /usr/sbin/icinga, /etc/init.d/icinga'));
                $ErrorLine->addItem(new EaseHtmlDivTag(null, 'APACHE ALL = (ALL) NOPASSWD: ICINGA'));
                break;
            }

            if (strstr($Line, 'Error:')) {
                $Line = str_replace('Error:', '', $Line);
                $ErrorLine = $oPage->addItem(new EaseHtmlDivTag(null, '<span class="label label-important">' . _('Chyba:') . '</span>', array('class' => 'alert alert-error')));

                $keywords = preg_split("/['(.*)']+/", $Line);
                switch (trim($keywords[0])) {
                    case 'Service notification period':
                        $ErrorLine->addItem(' <a href="timeperiods.php">' . _('Notifikační perioda') . '</a> služeb ');
                        $ErrorLine->addItem(new EaseHtmlATag('timeperiod.php?timeperiod_name=' . $keywords[1], $keywords[1]));
                        break;
                    case 'Host notification period':
                        $ErrorLine->addItem(' <a href="timeperiods.php">' . _('Notifikační perioda') . '</a> hostů');
                        $ErrorLine->addItem(new EaseHtmlATag('timeperiod.php?timeperiod_name=' . $keywords[1], $keywords[1]));
                        break;

                    default:
                        $ErrorLine->addItem($Line);
                        break;
                }

                if (isset($keywords[2])) {
                    switch (trim($keywords[2])) {
                        case 'specified for contact':
                            $ErrorLine->addItem(' specifikovaná pro kontakt ');
                            $Contact = new IEContact($keywords[3]);
                            $ErrorLine->addItem(new EaseHtmlATag('contact.php?contact_id=' . $Contact->getMyKey(), $keywords[3]));
                            break;

                        default:
                            break;
                    }
                }
                if (isset($keywords[4])) {
                    switch (trim($keywords[4])) {
                        case 'is not defined anywhere!':
                            $ErrorLine->addItem(' není nikde definován/a ');
                            break;
                    }
                }
                //$OPage->addItem('<pre>' . EaseBrick::printPreBasic($keywords) . '</pre>');
            }

            if (strstr($Line, 'Error in configuration file')) {
                $keywords = preg_split("/'|\(|\)| - Line /", $Line);
                $ErrorLine = $oPage->addItem(new EaseHtmlDivTag(null, '<span class="label label-error">' . _('Chyba v konfiguračním souboru'), array('class' => 'alert alert-error')));
                $ErrorLine->addItem(new EaseHtmlATag('cfgfile.php?file=' . $keywords[1] . '&line=' . $keywords[3], $keywords[1]));
                $ErrorLine->addItem($keywords[4]);
                $ErrorCount++;
            }


            if (strstr($Line, 'Warning:')) {

                if (strstr($Line, 'has no services associated with it!')) {
                    preg_match("/\'(.*)\'/", $Line, $keywords);
                    $Host = & $Generator->IEClasses['host'];
                    $Host->setMyKeyColumn($Host->NameColumn);
                    $Host->loadFromMySql($keywords[1]);
                    $Host->resetObjectIdentity();
                    $Line = '<span class="label label-warning">' . _('Varování:') . '</span> Host ' . '<a href="host.php?host_id=' . $Host->getMyKey() . '">' . $Host->getName() . '</a> ' . _('nemá přiřazené žádné služby');
                } else {
                    $Line = str_replace('Warning:', '<span class="label label-warning">' . _('Varování:') . '</span>', $Line);
                }

                //Duplicate definition found for command 'check_ping' (config file '/etc/icinga/generated/command_check_ping_vitex.cfg', starting on line 1) 
                $oPage->addItem(new EaseHtmlDivTag(null, $Line, array('class' => 'alert alert-warning')));
            }

            if (strstr($Line, 'Total Warnings')) {
                list($Msg, $WarningCount) = explode(':', $Line);
                if (intval(trim($WarningCount))) {
                    $oUser->addStatusMessage(sprintf(_('celkem %s varování'), $WarningCount), 'warning');
                } else {
                    $oUser->addStatusMessage(_('test proběhl bez varování'), 'success');
                }
            }
            if (strstr($Line, 'Total Errors')) {
                list($Msg, $ErrorCount) = explode(':', $Line);
                if (intval(trim($ErrorCount))) {
                    $oUser->addStatusMessage(sprintf(_('celkem %s chyb'), $ErrorCount), 'warning');
                } else {
                    $oUser->addStatusMessage(_('test proběhl bez chyb'), 'success');
                }
            }
        }
        fclose($Testing);

        if (!intval($ErrorCount) && !is_null($WarningCount)) {
            $oPage->column3->addItem(new EaseTWBLinkButton('reload.php', _('Reload icingy') . ' <i class="icon-refresh"></i>'));
        }
    }



$oPage->addItem(new IEPageBottom());

$oPage->draw();
?>
