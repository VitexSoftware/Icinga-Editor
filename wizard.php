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
require_once 'classes/IEContact.php';
require_once 'classes/IEContactgroup.php';
require_once 'classes/IEHost.php';
require_once 'classes/IEHostgroup.php';
require_once 'classes/IETimeperiod.php';
require_once 'classes/IECommand.php';
require_once 'classes/IEServicegroup.php';
require_once 'classes/IEPortScanner.php';


$oPage->onlyForLogged();


$oPage->addItem(new IEPageTop(_('Icinga Editor')));

$HostName = $oPage->getRequestValue('host_name');
$Address = $oPage->getRequestValue('address');
$Address6 = $oPage->getRequestValue('address6');

function gethostbyname6($host, $try_a = false)
{
    // get AAAA record for $host
    // if $try_a is true, if AAAA fails, it tries for A
    // the first match found is returned
    // otherwise returns false

    $dns = gethostbynamel6($host, $try_a);
    if ($dns == false) {
        return false;
    } else {
        return $dns[0];
    }
}

function gethostbynamel6($host, $try_a = false)
{
    // get AAAA records for $host,
    // if $try_a is true, if AAAA fails, it tries for A
    // results are returned in an array of ips found matching type
    // otherwise returns false

    $dns6 = dns_get_record($host, DNS_AAAA);
    if ($try_a == true) {
        $dns4 = dns_get_record($host, DNS_A);
        $dns = array_merge($dns4, $dns6);
    } else {
        $dns = $dns6;
    }
    $ip6 = array();
    $ip4 = array();
    foreach ($dns as $record) {
        if ($record["type"] == "A") {
            $ip4[] = $record["ip"];
        }
        if ($record["type"] == "AAAA") {
            $ip6[] = $record["ipv6"];
        }
    }
    if (count($ip6) < 1) {
        if ($try_a == true) {
            if (count($ip4) < 1) {
                return false;
            } else {
                return $ip4;
            }
        } else {
            return false;
        }
    } else {
        return $ip6;
    }
}

$Host = new IEHost();


if ($HostName || $Address || $Address6) {
    if (!$HostName) {
        if ($Address) {
            $HostName = gethostbyaddr($Address);
        } else {
            if ($Address6) {
                $HostName = gethostbyaddr6($Address6);
            }
        }
    }

    if (!$Address) {
        if ($HostName) {
            $Address = gethostbyname($HostName);
        }
        if (!$HostName) {
            if ($Address) {
                $HostName = gethostbyaddr($Address);
            } else {
                if ($Address6) {
                    $HostName = gethostbyaddr6($Address6);
                }
            }
        }
    }

    if (!$Address6) {
        $Address6 = gethostbyname6($HostName);
    }

    $oUser->addStatusMessage('HostName: ' . $HostName);
    $oUser->addStatusMessage('Address: ' . $Address);
    $oUser->addStatusMessage('Address6: ' . $Address6);

    $Host->setData(array(
        $Host->UserColumn => $oUser->getUserID(),
//        'check_command'=>'check-host-alive',
        'host_name' => $HostName,
        'address' => $Address,
        'address6' => $Address6,
        'use' => 'generic-host',
        'register' => true,
        'generate' => TRUE,
        'alias' => $HostName,
        'contacts' => array($oUser->getFirstContactName()))
    );

    if ($Host->saveToMysql()) {
        
        $service = new IEService('PING');
        $service->addMember('host_name', $Host->getId(), $Host->getName());
        $service->saveToMySQL();
        
        $Host->autoPopulateServices();
        
        $HostGroup = new IEHostgroup;
        if($HostGroup->loadDefault()){
            $HostGroup->setDataValue($HostGroup->NameColumn, EaseShared::user()->getUserLogin());
        }
        $HostGroup->addMember('members', $Host->getId(), $Host->getName());
        $HostGroup->saveToMySQL();
        
        $oPage->redirect('apply.php');
        exit();
    }
}

$Contact = new IEContact();
$PocContact = $Contact->getMyRecordsCount();
if (!$PocContact) {
    $Warning = $oPage->column3->addItem(new EaseHtmlDivTag('Contact', _('Nemáte definovaný kontakt'), array('class' => 'alert alert-info')));
    $Warning->addItem(new EaseTWBLinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt <i class="icon-edit"></i>')));
}


$PocHostu = $Host->getMyRecordsCount();
if ($PocHostu) {
    $Success = $oPage->column3->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s hostů'), $PocHostu)), array('class' => 'alert alert-success')));
}


$Warning = $oPage->column2->addItem(new EaseHtmlDivTag('Host', _('Vyplńte prosím alespoň jednu položku:'), array('class' => 'alert')));

$FirstHost = $Warning->addItem(new EaseHtmlForm('firsthost'));
$FirstHost->addItem(new EaseLabeledTextInput('host_name', $HostName, _('Hostname serveru')));
$FirstHost->addItem(new EaseLabeledTextInput('address', $Address, _('IPv4 Adresa')));
$FirstHost->addItem(new EaseLabeledTextInput('address6', $Address6, _('IPv6 Adresa')));
$FirstHost->addItem($Submit = new EaseHtmlInputSubmitTag('Ok'));
$Submit->setTagClass('btn');



if ($oUser->getSettingValue('admin')) {
    $oPage->column3->addItem(new EaseJQConfirmedLinkButton('install.php', _('Reinicializace z konfiguračních souborů') . ' <i class="icon-refresh"></i>'));
}

$oPage->addItem(new IEPageBottom());


$oPage->draw();
?>
