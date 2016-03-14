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
        $commonWell = new \Ease\TWB\Well();

        $commonRow = new \Ease\TWB\Row;
        $hostColumn = $commonRow->addColumn(6, new \Ease\Html\ATag('host.php?host_id=' . $host->getId(), new \Ease\Html\H1Tag($host->getName())));
        $hostColumn->addItem($host);
        $hostColumn->addItem($host->sensorStatusLabel());

        $commonWell->addItem($commonRow);

        $commonRow->addColumn(4, new \Ease\TWB\Panel(_('Ruční nastavení stavu senzoru'), 'info', new IESensorConfirmForm($host)));


        $sensorTabs = new \Ease\TWB\Tabs('sensorTabs');



        switch ($host->getDataValue('platform')) {
            case 'windows':
                $pltIco = 'logos/base/win40.gif';
                $cfgGenerator = new IENSCPConfigGenerator($host);

                if ($host->getCfgValue('active_checks_enabled')) {
                    $windowsActiveTab = $sensorTabs->addTab(_('Windows NRPE'));
                    $windowsActiveTab->addItem(new \Ease\Html\H1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NSC++')));
                    $windowsActiveTab->addItem(new \Ease\TWB\LinkButton('http://www.nsclient.org/download/', ' NSC++ ' . \Ease\TWB\Part::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));
                    $windowsActiveTab->addItem(new \Ease\TWB\LinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
                    $windowsActiveTab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?host_id=' . $host->getId(), $host->getName() . '_nscp.bat ' . \Ease\TWB\Part::GlyphIcon('download'), 'success'));
                    $windowsActiveTab->addItem(new \Ease\TWB\Container('<pre>' . htmlspecialchars($cfgGenerator->getCfg(false)) . '</pre>', array('font-face' => 'fixed')));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $windowsPassiveTab = $sensorTabs->addTab(_('Windows NSCA'));
                    $windowsPassiveTab->addItem(new \Ease\Html\H1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSC++')));
                    $windowsPassiveTab->addItem(new \Ease\TWB\LinkButton('http://www.nsclient.org/download/', ' NSC++ ' . \Ease\TWB\Part::GlyphIcon('download'), 'success', array('style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ", 'title' => 'Download')));
                    $windowsPassiveTab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?host_id=' . $host->getId(), $host->getName() . '_nscp.bat ' . \Ease\TWB\Part::GlyphIcon('download'), 'success'));
                    $windowsPassiveTab->addItem(new \Ease\TWB\Well('<pre>' . htmlspecialchars($cfgGenerator->getCfg(false), ENT_QUOTES) . '</pre>', array('font-face' => 'fixed')));
                }

                break;
            case 'linux':
                $pltIco = 'logos/base/linux40.gif';

                $preferences = new IEPreferences;
                $prefs = $preferences->getPrefs();

                if ($host->getCfgValue('active_checks_enabled')) {

                    $nrpe_cfgGenerator = new IENRPEConfigGenerator($host);

                    $linuxActiveTab = $sensorTabs->addTab(_('Linux NRPE'));
                    $linuxActiveTab->addItem(new \Ease\Html\H1Tag('<img src="' . $pltIco . '">' . _('aktivní NRPE pro NRPE Server')));
                    $linuxActiveTab->addItem(new \Ease\Html\PTag(_('Nainstalujte nejprve senzor tímto příkazem') . ':'));
                    $linuxActiveTab->addItem(new \Ease\Html\Div('<pre>sudo aptitude -y install nagios-nrpe-server</pre>', array('class' => 'code')));

                    $linuxActiveTab->addItem(new \Ease\TWB\LinkButton('nrpecfggen.php?host_id=' . $host->getId(), $host->getName() . '_nrpe.sh ' . \Ease\TWB\Part::GlyphIcon('download'), 'success'));

                    $linuxActiveTab->addItem(new \Ease\TWB\Container('<pre>' . htmlspecialchars($nrpe_cfgGenerator->getCfg(false)) . '</pre>', array('font-face' => 'fixed')));

                    $linuxActiveTab->addItem(new \Ease\TWB\LinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $linuxPassiveTab = $sensorTabs->addTab(_('Linux NSCA'));
                    $linuxPassiveTab->addItem(new \Ease\Html\H1Tag('<img src="' . $pltIco . '">' . _('pasivní NSCA pro NSCP Senzor')));
                    $linuxPassiveTab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?host_id=' . $host->getId(), $host->getName() . '_nscp.sh ' . \Ease\TWB\Part::GlyphIcon('download'), 'success'));

                    $cfgGenerator = new IENSCPConfigGenerator($host);
                    $linuxPassiveTab->addItem(new \Ease\TWB\Container('<pre>' . htmlspecialchars($cfgGenerator->getCfg(false)) . '</pre>', array('font-face' => 'fixed')));
                }
                break;
            default:
                $pltIco = 'logos/unknown.gif';
                if ($host->getCfgValue('active_checks_enabled')) {
                    $genericActiveTab = $sensorTabs->addTab(_('Generic Active'));
                    $genericActiveTab->addItem(new \Ease\TWB\LinkButton('host.php?action=populate&host_id=' . $host->getID(), _('Oskenovat a sledovat služby'), null, array('onClick' => "$('#preload').css('visibility', 'visible');")));
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
