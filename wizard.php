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
require_once 'classes/IEFXPreloader.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Icinga Editor')));

$hostName = $oPage->getRequestValue('host_name');
$address = $oPage->getRequestValue('address');
$addressSix = $oPage->getRequestValue('address6');

function gethostbyname6($host, $tryA = false)
{
    // get AAAA record for $host
    // if $try_a is true, if AAAA fails, it tries for A
    // the first match found is returned
    // otherwise returns false

    $dns = gethostbynamel6($host, $tryA);
    if ($dns == false) {
        return false;
    } else {
        return $dns[0];
    }
}

function gethostbynamel6($host, $tryA = false)
{
    // get AAAA records for $host,
    // if $try_a is true, if AAAA fails, it tries for A
    // results are returned in an array of ips found matching type
    // otherwise returns false

    $dnsSix = dns_get_record($host, DNS_AAAA);
    if ($tryA == true) {
        $dnsFour = dns_get_record($host, DNS_A);
        $dns = array_merge($dnsFour, $dnsSix);
    } else {
        $dns = $dnsSix;
    }
    $ipSix = array();
    $ipFour = array();
    foreach ($dns as $record) {
        if ($record["type"] == "A") {
            $ipFour[] = $record["ip"];
        }
        if ($record["type"] == "AAAA") {
            $ipSix[] = $record["ipv6"];
        }
    }
    if (count($ipSix) < 1) {
        if ($tryA == true) {
            if (count($ipFour) < 1) {
                return false;
            } else {
                return $ipFour;
            }
        } else {
            return false;
        }
    } else {
        return $ipSix;
    }
}

$host = new IEHost();
$host->owner = &$oUser;


if ($hostName || $address || $addressSix) {
    if (!$hostName) {
        if ($address) {
            $hostName = gethostbyaddr($address);
        } else {
            if ($addressSix) {
                $hostName = gethostbyaddr6($addressSix);
            }
        }
    }

    if (!$address) {
        if ($hostName) {
            $address = gethostbyname($hostName);
        }
        if (!$hostName) {
            if ($address) {
                $hostName = gethostbyaddr($address);
            } else {
                if ($addressSix) {
                    $hostName = gethostbyaddr6($addressSix);
                }
            }
        }
    }

    if (!$addressSix) {
        $addressSix = gethostbyname6($hostName);
    }

    $oUser->addStatusMessage('HostName: ' . $hostName);
    $oUser->addStatusMessage('Address: ' . $address);
    $oUser->addStatusMessage('Address6: ' . $addressSix);

    $host->setData(
        array(
        $host->userColumn => $oUser->getUserID(),
//        'check_command'=>'check-host-alive',
        'host_name' => $hostName,
        'address' => $address,
        'address6' => $addressSix,
        'use' => 'generic-host',
        'platform' => 'generic',
        'register' => true,
        'generate' => TRUE,
        'alias' => $hostName,
        'contacts' => array($oUser->getFirstContactName())
                )
    );

    if ($host->saveToMysql()) {

        $service = new IEService('PING');
        $service->addMember('host_name', $host->getId(), $host->getName());
        $service->saveToMySQL();

        $host->autoPopulateServices();

        $hostGroup = new IEHostgroup;
        if ($hostGroup->loadDefault()) {
            $hostGroup->setDataValue($hostGroup->nameColumn, EaseShared::user()->getUserLogin());
            $hostGroup->addMember('members', $host->getId(), $host->getName());
            $hostGroup->saveToMySQL();
        }

        $oPage->redirect('apply.php');
        exit();
    }
}

$contact = new IEContact();
$pocContact = $contact->getMyRecordsCount();
if (!$pocContact) {
    $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Contact', _('Nemáte definovaný kontakt'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt').' '.EaseTWBPart::GlyphIcon('edit')));
}

$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s hostů'), $pocHostu)), array('class' => 'alert alert-success')));
}

$warning = $oPage->columnII->addItem(new EaseHtmlDivTag('Host', _('Vyplňte prosím alespoň jednu položku:'), array('class' => 'alert')));

$firstHost = $warning->addItem(new EaseHtmlForm('firsthost'));
$firstHost->setTagProperties(array('onSubmit'=>"$('#preload').css('visibility', 'visible');"));
$firstHost->addItem(new EaseLabeledTextInput('host_name', $hostName, _('Hostname serveru')));
$firstHost->addItem(new EaseLabeledTextInput('address', $address, _('IPv4 Adresa')));
$firstHost->addItem(new EaseLabeledTextInput('address6', $addressSix, _('IPv6 Adresa')));
$firstHost->addItem($submit = new EaseHtmlInputSubmitTag('Ok'));
$submit->setTagClass('btn');

if ($oUser->getSettingValue('admin')) {
    $oPage->columnIII->addItem(new EaseJQConfirmedLinkButton('install.php', _('Reinicializace z konfiguračních souborů') . ' <i class="icon-refresh"></i>'));
}

$oPage->columnI->addItem( new EaseHtmlDivTag(null, _('Po zadání alespoň jednoho vstupního údaje si tento '
        . 'průvodce dohledá ostatní a provede sken na některé základní služby.'
        . 'Pokud budou tyto nalezeny aktivují se jejich testy.'), array('class'=>'well')  )  );


$oPage->addItem( new EaseHtmlDivTag('preload', new IEFXPreloader(),array('class'=>'fuelux')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
