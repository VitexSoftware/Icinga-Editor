<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IESensorTool
 *
 * @author vitex
 */
class IESensorTool extends EaseContainer
{

    /**
     * Stav senzoru a jeho nastavení
     *
     * @param IEHost $host
     */
    public function __construct($host)
    {
        $commonWell = new EaseTWBWell();

        $commonRow = new EaseTWBRow;
        $hostColumn = $commonRow->addColumn(6, new EaseHtmlH1Tag($host->getName()));
        $hostColumn->addItem($host);
        $hostColumn->addItem($host->sensorStatusLabel());

        $commonWell->addItem($commonRow);

        $commonRow->addColumn(4, new EaseTWBPanel(_('Ruční nastavení stavu senzoru'), 'info', new IESensorConfirmForm($host)));


        $sensorTabs = new EaseTWBTabs('sensorTabs');



        switch ($host->getDataValue('platform')) {
            case 'windows':
                $pltIco = 'logos/base/win40.gif';
                $cfgGenerator = new IENSCPConfigGenerator($host);

                if ($host->getCfgValue('active_checks_enabled')) {
                    $windowsActiveTab = $sensorTabs->addTab(_('Windows NRPE'));
                    $windowsActiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NSC++')));
                    $windowsActiveTab->addItem(new EaseTWBLinkButton('http://www.nsclient.org/download/', ' NSC++ ' . EaseTWBPart::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));
                    $windowsActiveTab->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
                    $windowsActiveTab->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $host->getId(), $host->getName() . '_nscp.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
                    $windowsActiveTab->addItem(new EaseTWBContainer('<pre>' . $cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $windowsPassiveTab = $sensorTabs->addTab(_('Windows NSCA'));
                    $windowsPassiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSC++')));
                    $windowsPassiveTab->addItem(new EaseTWBLinkButton('http://www.nsclient.org/download/', ' NSC++ ' . EaseTWBPart::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));
                    $windowsPassiveTab->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $host->getId(), $host->getName() . '_nscp.bat ' . EaseTWBPart::GlyphIcon('download'), 'success'));
                    $windowsPassiveTab->addItem(new EaseTWBContainer('<pre>' . $cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));
                }

                break;
            case 'linux':
                $pltIco = 'logos/base/linux40.gif';

                $preferences = new IEPreferences;
                $prefs = $preferences->getPrefs();

                if ($host->getCfgValue('active_checks_enabled')) {

                    $nrpe_cfgGenerator = new IENRPEConfigGenerator($host);


                    $linuxActiveTab = $sensorTabs->addTab(_('Linux NRPE'));
                    $linuxActiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NRPE Server')));
                    $linuxActiveTab->addItem(new EaseHtmlPTag(_('Nainstalujte nejprve senzor tímto příkazem') . ':'));
                    $linuxActiveTab->addItem(new EaseHtmlDiv('<pre>sudo aptitude -y install nagios-nrpe-server</pre>', array('class' => 'code')));

                    $linuxActiveTab->addItem(new EaseTWBLinkButton('nrpecfggen.php?host_id=' . $host->getId(), $host->getName() . '_nrpe.sh ' . EaseTWBPart::GlyphIcon('download'), 'success'));

                    $linuxActiveTab->addItem(new EaseTWBContainer('<pre>' . $nrpe_cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));

                    $linuxActiveTab->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $linuxPassiveTab = $sensorTabs->addTab(_('Linux NSCA'));
                    $linuxPassiveTab->addItem(new EaseHtmlH1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSCP Senzor')));
                    $linuxPassiveTab->addItem(new EaseTWBLinkButton('nscpcfggen.php?host_id=' . $host->getId(), $host->getName() . '_nscp.sh ' . EaseTWBPart::GlyphIcon('download'), 'success'));

                    $cfgGenerator = new IENSCPConfigGenerator($host);
                    $linuxPassiveTab->addItem(new EaseTWBContainer('<pre>' . $cfgGenerator->getCfg(false) . '</pre>', array('font-face' => 'fixed')));
                }
                break;
            default:
                $pltIco = 'logos/unknown.gif';
                if ($host->getCfgValue('active_checks_enabled')) {
                    $genericActiveTab = $sensorTabs->addTab(_('Generic Active'));
                    $genericActiveTab->addItem(new EaseTWBLinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $genericPassiveTab = $sensorTabs->addTab(_('Generic Passive'));
                }
                break;
        }
        parent::__construct($commonWell);
        $this->addItem($sensorTabs);
    }

}
