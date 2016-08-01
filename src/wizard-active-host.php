<?php

namespace Icinga\Editor;

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

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$hostName   = trim($oPage->getRequestValue('host_name'));
$address    = trim($oPage->getRequestValue('address'));
$addressSix = trim($oPage->getRequestValue('address6'));
$host_group = $oPage->getRequestValue('host_group', 'int');
$platform   = $oPage->getRequestValue('platform');

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
        $dns     = array_merge($dnsFour, $dnsSix);
    } else {
        $dns = $dnsSix;
    }
    $ipSix  = [];
    $ipFour = [];
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

$host        = new Engine\Host();
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

    $oUser->addStatusMessage('HostName: '.$hostName);
    $oUser->addStatusMessage('Address: '.$address);
    $oUser->addStatusMessage('Address6: '.$addressSix);

    $host->setData(
        [
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
        ]
    );

    if ($host_group) {
        $hostgroup = new Engine\Hostgroup($host_group);
        $host->addMember('hostgroups', $hostgroup->getId(),
            $hostgroup->getName());
        $hostgroup->addMember('members', $host->getId(), $host->getName());
        $hostgroup->saveToSQL();
    }


    if ($host->saveToSQL()) {

        $service = new Engine\Service('PING');
        $service->addMember('host_name', $host->getId(), $host->getName());
        $service->saveToSQL();

        $host->autoPopulateServices();

        $hostGroup = new Engine\Hostgroup;
        if ($hostGroup->loadDefault()) {
            $hostGroup->setDataValue($hostGroup->nameColumn,
                \Ease\Shared::user()->getUserLogin());
            $hostGroup->addMember('members', $host->getId(), $host->getName());
            $hostGroup->saveToSQL();
            $host->addMember('hostgroups', $hostGroup->getId(),
                $hostGroup->getName());
            $host->saveToSQL();
        }

        $oPage->redirect('host.php?host_id='.$host->getId());
        exit();
    }
}

$contact    = new Engine\Contact();
$pocContact = $contact->getMyRecordsCount();
if (!$pocContact) {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Contact',
        _('Nemáte definovaný kontakt'), ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('contact.php?autocreate=default',
        _('Založit výchozí kontakt').' '.\Ease\TWB\Part::GlyphIcon('edit')));
}

$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\Div(
        new \Ease\TWB\LinkButton('hosts.php',
        _('<i class="icon-list"></i>').' '.sprintf(_('Definováno %s hostů'),
            $pocHostu)), ['class' => 'alert alert-success', 'id' => 'Host']));
}

$firstHost = $oPage->columnII->addItem(new \Ease\TWB\Form('firsthost'));
$firstHost->addItem(new \Ease\Html\InputHiddenTag('host_group',
    $oPage->getRequestValue('host_group')));
$firstHost->setTagProperties(['onSubmit' => "$('#preload').css('visibility', 'visible');"]);

$firstHost->addItem(new \Ease\TWB\FormGroup(_('Hostname serveru'),
    new \Ease\Html\InputTextTag('host_name', $hostName), null,
    _('Název hostu, tedy to co následuje po http:// ve webové adrese až k prvnímu lomítku, nebo otazníku.')));
$firstHost->addItem(new \Ease\TWB\FormGroup(_('IPv4 Adresa'),
    new \Ease\Html\InputTextTag('address', $address), null,
    _('čtyři číslice od 0 do 255 oddělené tečkou')));
$firstHost->addItem(new \Ease\TWB\FormGroup(_('IPv6 Adresa'),
    new \Ease\Html\InputTextTag('address6', $addressSix), null,
    _('nejvíce osm skupin čtyř hexadecimálních číslic oddělených dvojtečkou')));

$firstHost->addItem(new \Ease\TWB\SubmitButton(\Ease\TWB\Part::GlyphIcon('plus').' '._('Přidej host'),
    'success'));

$oPage->columnI->addItem(new \Ease\Html\Div(_('Po zadání alespoň jednoho vstupního údaje si tento '
        .'průvodce dohledá ostatní a provede sken na některé základní služby.'
        .'<br>Pokud budou tyto nalezeny aktivují se jejich testy. Informace o stavu bude odesílána na první zadaný kontakt'),
    ['class' => 'well']));

$oPage->columnI->addItem(new \Ease\Html\Div(_('Pro instalaci nového vzdáleného senzoru prosím nejprve na sledovaném počítači nainstalujte balík'
        .' a poté '
        .'<code>wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key | sudo apt-key add -</code>
<code>echo deb http://v.s.cz/ stable main | sudo tee /etc/apt/sources.list.d/vitexsoftware.list</code>
<code>sudo aptitude update</code>
<code>aptitude install nagios-nrpe-server nagios-check-clamscan</code>'),
    ['class' => 'well']));

$oPage->addItem(new \Ease\Html\Div(new UI\FXPreloader(),
    ['class' => 'fuelux', 'id' => 'preload']));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
