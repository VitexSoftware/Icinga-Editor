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

$oPage->addItem(new IEPageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$hostName = trim($oPage->getRequestValue('host_name'));
$address = trim($oPage->getRequestValue('address'));
$addressSix = trim($oPage->getRequestValue('address6'));
$host_group = $oPage->getRequestValue('host_group', 'int');
$platform = $oPage->getRequestValue('platform');

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

    $dnsSix = @dns_get_record($host, DNS_AAAA);
    if ($dnsSix === FALSE) {
        return FALSE;
    }
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
          'platform' => $platform,
          'register' => true,
          'generate' => TRUE,
          'alias' => $hostName,
          'active_checks_enabled' => 1,
          'passive_checks_enabled' => 0
        )
    );

    if ($host_group) {
        $hostgroup = new IEHostgroup($host_group);
        $host->addMember('hostgroups', $hostgroup->getId(), $hostgroup->getName());
        $hostgroup->addMember('members', $host->getId(), $host->getName());
        $hostgroup->saveToMySQL();
    }


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
            $host->addMember('hostgroups', $hostGroup->getId(), $hostGroup->getName());
            $host->saveToMysql();
        }

        $oPage->redirect('host.php?host_id=' . $host->getId());
        exit();
    }
}

$contact = new IEContact();
$pocContact = $contact->getMyRecordsCount();
if (!$pocContact) {
    $warning = $oPage->columnIII->addItem(new EaseHtmlDivTag('Contact', _('Nemáte definovaný kontakt'), array('class' => 'alert alert-info')));
    $warning->addItem(new EaseTWBLinkButton('contact.php?autocreate=default', _('Založit výchozí kontakt') . ' ' . EaseTWBPart::GlyphIcon('edit')));
}

$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnIII->addItem(new EaseHtmlDivTag('Host', new EaseTWBLinkButton('hosts.php', _('<i class="icon-list"></i>') . ' ' . sprintf(_('Definováno %s hostů'), $pocHostu)), array('class' => 'alert alert-success')));
}

$firstHost = $oPage->columnII->addItem(new EaseTWBForm('firsthost'));
$firstHost->addItem(new EaseHtmlInputHiddenTag('host_group', $oPage->getRequestValue('host_group')));
$firstHost->setTagProperties(array('onSubmit' => "$('#preload').css('visibility', 'visible');"));

$firstHost->addItem(new EaseTWBFormGroup(_('Hostname serveru'), new EaseHtmlInputTextTag('host_name', $hostName), null, _('Název hostu, tedy to co následuje po http:// ve webové adrese až k prvnímu lomítku, nebo otazníku.')));
$firstHost->addItem(new EaseTWBFormGroup(_('IPv4 Adresa'), new EaseHtmlInputTextTag('address', $address), null, _('čtyři číslice od 0 do 255 oddělené tečkou')));
$firstHost->addItem(new EaseTWBFormGroup(_('IPv6 Adresa'), new EaseHtmlInputTextTag('address6', $addressSix), null, _('nejvíce osm skupin čtyř hexadecimálních číslic oddělených dvojtečkou')));

$firstHost->addItem(new EaseTWSubmitButton(EaseTWBPart::GlyphIcon('plus') . ' ' . _('Přidej host'), 'success'));

$oPage->columnI->addItem(new EaseHtmlDivTag(null, _('Po zadání alespoň jednoho vstupního údaje si tento '
        . 'průvodce dohledá ostatní a provede sken na některé základní služby.'
        . '<br>Pokud budou tyto nalezeny aktivují se jejich testy. Informace o stavu bude odesílána na první zadaný kontakt'), array('class' => 'well')));

$oPage->columnI->addItem(new EaseHtmlDivTag(null, _('Pro instalaci nového vzdáleného senzoru prosím nejprve na sledovaném počítači nainstalujte balík'
        . ' a poté '
        . '<code>wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key | sudo apt-key add -</code>
<code>echo deb http://v.s.cz/ stable main | sudo tee /etc/apt/sources.list.d/vitexsoftware.list</code>
<code>sudo aptitude update</code>
<code>aptitude install nagios-nrpe-server nagios-check-clamscan</code>'), array('class' => 'well')));

$oPage->addItem(new EaseHtmlDivTag('preload', new IEFXPreloader(), array('class' => 'fuelux')));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
