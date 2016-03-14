<?php

namespace Icinga\Editor\UI;

/**
 * Volba služeb patřičných k hostu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class UsedServiceSelector extends EaseContainer
{
    public $myKeyColumn = 'host_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        parent::__construct();
        $fieldName = $this->getmyKeyColumn();

        if ($host->getDataValue('platform') == 'generic') {
            $note = '<small><span class="label label-info">Tip:</span> '._('Další sledovatelné služby budou nabídnuty po nastavení platformy hosta a vzdáleného senzoru.').'</small>';
        } else {
            $note = array();
        }


        $initialContent = new \Ease\TWB\Panel(_('Sledované služby'), 'default',
            null, $note);
        $initialContent->setTagCss(array('width' => '100%'));

        if (is_null($host->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {
            $hostName       = $host->getName();
            $service        = new IEService();
            $parentServUsed = array();
            $host_active    = (boolean) $host->getCfgValue('active_checks_enabled');
            $host_passive   = (boolean) $host->getCfgValue('passive_checks_enabled');

            $servicesAssigned = $service->myDbLink->queryToArray('SELECT '.$service->myKeyColumn.',display_name,'.$service->nameColumn.' FROM '.$service->myTable.' WHERE '.$fieldName.' LIKE \'%"'.$host->getName().'"%\'',
                $service->myKeyColumn);

            $allServices = $service->getListing(
                null, true,
                array(
                'platform', 'parent_id', 'passive_checks_enabled', 'active_checks_enabled',
                'display_name'
                )
            );
            foreach ($allServices as $serviceID => $serviceInfo) {
                $servicePassive = (boolean) $serviceInfo['passive_checks_enabled'];
                $serviceActive  = (boolean) $serviceInfo['active_checks_enabled'];
                if ($serviceInfo['register'] != 1) {
                    unset($allServices[$serviceID]);
                    continue;
                }

                if (($serviceInfo ['platform'] != 'generic' ) && $serviceInfo['platform']
                    != $host->getDataValue('platform')) {
                    unset($allServices[$serviceID]);
                    continue;
                }
                if ((!$host_passive || !$servicePassive) && (!$host_active || !$serviceActive)) {
                    unset($allServices[$serviceID]);
                    continue;
                }
            }

            foreach ($servicesAssigned as $serviceID => $serviceInfo) {
                if (isset($allServices[$serviceID]) && isset($parentServUsed[$allServices[$serviceID]['parent_id']])) {
                    $parentServUsed[$allServices[$serviceID]['parent_id']] = $allServices[$serviceID]['parent_id'];
                }
                unset($allServices[$serviceID]);
            }

            if (count($allServices)) {
                foreach ($allServices as $serviceID => $serviceInfo) {
                    if (isset($parentServUsed[$serviceInfo['parent_id']])) {
                        continue;
                    }
                    $unchMenu = array();

                    if (intval($serviceInfo['parent_id'])) {
                        $unchMenu[] = new \Ease\Html\ATag('servicetweak.php?service_id='.$serviceID,
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace'));
                    }
                    $unchMenu [] = new \Ease\Html\ATag('?addservice='.$serviceInfo[$service->nameColumn].'&amp;service_id='.$serviceID.'&amp;'.$host->getmyKeyColumn().'='.$host->getMyKey().'&amp;'.$host->nameColumn.'='.$host->getName(),
                        \Ease\TWB\Part::GlyphIcon('plus').' '._('Začít sledovat'));


                    if (strlen($serviceInfo['display_name'])) {
                        $serviceName = $serviceInfo['display_name'];
                    } else {
                        $serviceName = $serviceInfo[$service->nameColumn];
                    }

                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown($serviceName, 'inverse',
                        'xs', $unchMenu));
                }
            }

            if (count($servicesAssigned)) {
                $saveAsTemplateButton = new \Ease\TWB\LinkButton('stemplate.php?action=copyhost&host_id='.$host->getId(),
                    _('Uložit jako předlohu'), 'success');
                $initialContent->footer($saveAsTemplateButton);

                $initialContent->addItem('</br>');
                foreach ($servicesAssigned as $serviceID => $serviceInfo) {
                    if (strlen($serviceInfo['display_name'])) {
                        $serviceName = $serviceInfo['display_name'];
                    } else {
                        $serviceName = $serviceInfo[$service->nameColumn];
                    }

                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown($serviceName, 'success',
                        'xs',
                        array(
                        new \Ease\Html\ATag(
                            '?delservice='.$serviceInfo[$service->nameColumn].'&amp;service_id='.$serviceID.'&amp;'.$host->getmyKeyColumn().'='.$host->getMyKey().'&amp;'.$host->nameColumn.'='.$host->getName(),
                            \Ease\TWB\Part::GlyphIcon('remove').' '._('Přestat sledovat'))
                        , new \Ease\Html\ATag('servicetweak.php?service_id='.$serviceID.'&amp;host_id='.$host->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace'))
                        )
                        )
                    );
                }
            } else {
                $presetSelForm = new \Ease\TWB\Form('presetSelForm');
                $presetSelForm->addItem(new \Ease\Html\InputHiddenTag($host->getmyKeyColumn(),
                    $host->getId()));
                $presetSelForm->addItem(new \Ease\Html\InputHiddenTag('action',
                    'applystemplate'));
                $presetSelForm->addItem(new IEStemplateSelect('stemplate_id'));
                $presetSelForm->addItem(new \Ease\TWB\SubmitButton(_('Aplikovat předlohu'),
                    'success'));
                $presetSelForm->setTagClass('form-inline');
                $initialContent->footer($presetSelForm);
            }
        }
        $this->addItem($initialContent);
    }

    /**
     * Uloží položky
     *
     * @param array $request
     */
    public static function saveMembers($request)
    {
        $service = new IEService();
        if (isset($request[$service->myKeyColumn])) {
            if ($service->loadFromMySQL((int) $request[$service->myKeyColumn])) {
                if (isset($request['addservice']) || isset($request['delservice'])) {
                    if (isset($request['addservice'])) {
                        $service->addMember('host_name', $request['host_id'],
                            $request['host_name']);
                        if ($service->saveToMySQL()) {
                            $service->addStatusMessage(sprintf(_('položka %s byla přidána'),
                                    $request['addservice']), 'success');
                            if ($service->getDataValue('autocfg') == '1') {
                                $service->addStatusMessage(sprintf(_('Službu %s je nutné nejprve zkonfigurovat'),
                                        $request['addservice']), 'warning');
                                \Ease\Shared::webPage()->redirect('servicetweak.php?host_id='.$request ['host_id'].'&service_id='.$request[$service->myKeyColumn]);
                                exit();
                            }
                        } else {
                            $service->addStatusMessage(sprintf(_('položka %s nebyla přidána'),
                                    $request['addservice']), 'warning');
                        }
                    }
                    if (isset($request['delservice'])) {
                        $service->delMember('host_name', $request['host_id'],
                            $request['host_name']);
                        if ($service->saveToMySQL()) {
                            $service->addStatusMessage(sprintf(_('položka %s byla odebrána'),
                                    $request['delservice']), 'success');
                        } else {
                            $service->addStatusMessage(sprintf(_('položka %s nebyla odebrána'),
                                    $request['delservice']), 'warning');
                        }
                    }
                }
            }
        }
    }

    public function useSameServicesTo()
    {
// return new \Ease\TWB\Form('', '', 'post', array(new IEHostSelect(), new \Ease\TWB\SubmitButton(_('Přiřadit'), 'success')));
        return null;
    }
}