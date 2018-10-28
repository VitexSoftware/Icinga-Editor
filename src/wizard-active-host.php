<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('New Active Host')));
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

/**
 * Obtain Hostname from IPv6 Address
 * 
 * @param string  $host
 * @param boolean $tryA
 * @return boolean
 */
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

    $host->takeData(
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


    if (!is_null($host->saveToSQL())) {

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
    } else {
        $host->addStatusMessage(_('Host Save failed'), 'error');
    }
}

$contact    = new Engine\Contact();
$pocContact = $contact->getMyRecordsCount();
if (!$pocContact) {
    $warning = $oPage->columnIII->addItem(new \Ease\Html\DivTag('Contact',
            _('No contact defined'), ['class' => 'alert alert-info']));
    $warning->addItem(new \Ease\TWB\LinkButton('contact.php?autocreate=default',
            _('Create initial contact').' '.\Ease\TWB\Part::GlyphIcon('edit')));
}

$pocHostu = $host->getMyRecordsCount();
if ($pocHostu) {
    $success = $oPage->columnIII->addItem(new \Ease\Html\DivTag(
            new \Ease\TWB\LinkButton('hosts.php',
                _('<i class="icon-list"></i>').' '.sprintf(_('%s hosts defined'),
                    $pocHostu)),
            ['class' => 'alert alert-success', 'id' => 'Host']));
}

$firstHost = $oPage->columnII->addItem(new \Ease\TWB\Form('firsthost'));
$firstHost->addItem(new \Ease\Html\InputHiddenTag('host_group',
        $oPage->getRequestValue('host_group')));
$firstHost->setTagProperties(['onSubmit' => "$('#preload').css('visibility', 'visible');"]);

$firstHost->addItem(new \Ease\TWB\FormGroup(_('Server hostname'),
        new \Ease\Html\InputTextTag('host_name', $hostName), null,
        _('The guest name, that is what follows http:// in the web address to the first slash, or the question mark.')));
$firstHost->addItem(new \Ease\TWB\FormGroup(_('IPv4 Address'),
        new \Ease\Html\InputTextTag('address', $address), null,
        _('four digits from 0 to 255 separated by a dot')));
$firstHost->addItem(new \Ease\TWB\FormGroup(_('IPv6 Address'),
        new \Ease\Html\InputTextTag('address6', $addressSix), null,
        _('up to eight groups of four hexadecimal digits separated by a colon')));

$firstHost->addItem(new \Ease\TWB\SubmitButton(\Ease\TWB\Part::GlyphIcon('plus').' '._('Add Host'),
        'success'));

$oPage->columnI->addItem(new \Ease\Html\DivTag(
        _('After entering at least one input data, this wizard searches others and scans for some basic services.').'<br>'.
        _('If these are found, their tests will be activated. Status information will be sent to the first contact you entered'),
        ['class' => 'well']));

$oPage->columnI->addItem(new \Ease\Html\DivTag(_(
            'To install a new remote sensor, please first install the package on your computer')
        .'<code>wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key | sudo apt-key add -</code>
<code>echo deb http://v.s.cz/ stable main | sudo tee /etc/apt/sources.list.d/vitexsoftware.list</code>
<code>sudo aptitude update</code>
<code>aptitude install nagios-nrpe-server nagios-check-clamscan</code>',
        ['class' => 'well']));

$oPage->addItem(new \Ease\Html\DivTag(new UI\FXPreloader(),
        ['class' => 'fuelux', 'id' => 'preload']));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
