<?php

namespace Icinga\Editor;

/**
 * Sensor Tool
 *
 * @author vitex
 */
class SensorTool extends \Ease\Container
{

    /**
     * Sensor state & settings
     *
     * @param Engine\Host $host
     */
    public function __construct($host)
    {
        $commonWell = new \Ease\TWB\Well();

        $commonRow  = new \Ease\TWB\Row;
        $hostColumn = $commonRow->addColumn(6,
            new \Ease\Html\ATag('host.php?host_id='.$host->getId(),
                new \Ease\Html\H1Tag($host->getName())));
        $hostColumn->addItem($host);
        $hostColumn->addItem($host->sensorStatusLabel());

        $commonWell->addItem($commonRow);

        $commonRow->addColumn(4,
            new \Ease\TWB\Panel(_('Set sensor state manually'), 'info',
                new UI\SensorConfirmForm($host)));


        $sensorTabs = new \Ease\TWB\Tabs('sensorTabs');



        switch ($host->getDataValue('platform')) {
            case 'windows':
                $pltIco          = 'logos/base/win40.gif';
                $cfgBatGenerator = new NSCPConfigBatGenerator($host);
                $cfgPS1Generator = new NSCPConfigPS1Generator($host);

                if ($host->getCfgValue('active_checks_enabled')) {
                    $windowsActiveTab = $sensorTabs->addTab(_('Windows NRPE'));
                    $windowsActiveTab->addItem(new \Ease\Html\H1Tag('<img src="'.$pltIco.'">'._('Active NRPE for NSClient++')));
                    $windowsActiveTab->addItem(new \Ease\TWB\LinkButton('http://www.nsclient.org/download/',
                            ' NSC++ '.\Ease\TWB\Part::GlyphIcon('download'),
                            'success',
                            ['target'=>'blank', 'style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ",
                            'title' => 'Download']));
                    $windowsActiveTab->addItem(new \Ease\TWB\LinkButton('host.php?action=populate&host_id='.$host->getID(),
                            _('Scan & Wartch services'), null,
                            ['onClick' => "$('#preload').css('visibility', 'visible');"]));
                    $windowsActiveTab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?host_id='.$host->getId(),
                            $host->getName().'_nscp.bat '.\Ease\TWB\Part::GlyphIcon('download'),
                            'success'));
                    $windowsActiveTab->addItem(new \Ease\TWB\Container('<pre>'.htmlspecialchars($cfgBatGenerator->getCfg(false)).'</pre>',
                            ['font-face' => 'fixed']));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $windowsPassiveTab = $sensorTabs->addTab(_('Windows NSCA'));
                    $windowsPassiveTab->addItem(new \Ease\Html\H1Tag('<img src="'.$pltIco.'">'._('Passive NSCA for NSClient++')));
                    $windowsPassiveTab->addItem($this->nsclientDownload());

                    $winNscaTabs = $windowsPassiveTab->addItem(new \Ease\TWB\Tabs('WinNSCA'));

                    $ps1Tab = $winNscaTabs->addTab('Windows 10');

                    $ps1Tab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?format=ps1&host_id='.$host->getId(),
                            $host->getName().'_nscp.ps1 '.\Ease\TWB\Part::GlyphIcon('download'),
                            'success'));
                    $ps1Tab->addItem(new \Ease\TWB\Well('<pre>'.htmlspecialchars($cfgPS1Generator->getCfg(false),
                                ENT_QUOTES).'</pre>', ['font-face' => 'fixed']));

                    $batTab = $winNscaTabs->addTab(_('Old Windows Versions'));

                    $batTab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?host_id='.$host->getId(),
                            $host->getName().'_nscp.bat '.\Ease\TWB\Part::GlyphIcon('download'),
                            'success'));
                    $batTab->addItem(new \Ease\TWB\Well('<pre>'.htmlspecialchars($cfgBatGenerator->getCfg(false),
                                ENT_QUOTES).'</pre>', ['font-face' => 'fixed']));
                }

                break;
            case 'linux':
                $pltIco = 'logos/base/linux40.gif';

                $preferences = new Preferences;
                $prefs       = $preferences->getPrefs();

                if ($host->getCfgValue('active_checks_enabled')) {

                    $nrpe_cfgGenerator = new NRPEConfigGenerator($host);

                    $linuxActiveTab = $sensorTabs->addTab(_('Linux NRPE'));
                    $linuxActiveTab->addItem(new \Ease\Html\H1Tag('<img src="'.$pltIco.'">'._('Active NRPE for NRPE Server')));
                    $linuxActiveTab->addItem(new \Ease\Html\PTag(_('Please install sensor first by this command').':'));
                    $linuxActiveTab->addItem(new \Ease\Html\DivTag('<pre>
ssh '.$host->getName().'
sudo apt install nagios-nrpe-server
curl "'.Engine\Configurator::getBaseURL().'nrpecfggen.php?host_id='.$host->getId().'" | sh
</pre>', ['class' => 'code']));

                    $linuxActiveTab->addItem(new \Ease\TWB\LinkButton('nrpecfggen.php?host_id='.$host->getId(),
                            $host->getName().'_nrpe.sh '.\Ease\TWB\Part::GlyphIcon('download'),
                            'success'));

                    $linuxActiveTab->addItem(new \Ease\TWB\Container('<pre>'.htmlspecialchars($nrpe_cfgGenerator->getCfg(false)).'</pre>',
                            ['font-face' => 'fixed']));

                    $linuxActiveTab->addItem(new \Ease\TWB\LinkButton('host.php?action=populate&host_id='.$host->getID(),
                            _('Scan & watch services'), null,
                            ['onClick' => "$('#preload').css('visibility', 'visible');"]));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $linuxPassiveTab = $sensorTabs->addTab(_('Linux NSCA'));
                    $linuxPassiveTab->addItem(new \Ease\Html\H1Tag('<img src="'.$pltIco.'">'._('Passive NSCA for NSCP Sensor')));
                    $linuxPassiveTab->addItem(new \Ease\TWB\LinkButton('nscpcfggen.php?host_id='.$host->getId(),
                            $host->getName().'_nscp.sh '.\Ease\TWB\Part::GlyphIcon('download'),
                            'success'));

                    $cfgBatGenerator = new NSCPConfigBatGenerator($host);
                    $linuxPassiveTab->addItem(new \Ease\TWB\Container('<pre>'.htmlspecialchars($cfgBatGenerator->getCfg(false)).'</pre>',
                            ['font-face' => 'fixed']));
                }
                break;
            default :
                $pltIco = 'logos/unknown.gif';
                if ($host->getCfgValue('active_checks_enabled')) {
                    $genericActiveTab = $sensorTabs->addTab(_('Generic Active'));
                    $genericActiveTab->addItem(new \Ease\TWB\LinkButton('host.php?action=populate&host_id='.$host->getID(),
                            _('Scan & Watch services'), null,
                            ['onClick' => "$('#preload').css('visibility', 'visible');"]));
                }
                if ($host->getCfgValue('passive_checks_enabled')) {
                    $genericPassiveTab = $sensorTabs->addTab(_('Generic Passive'));
                }
                break;
        }
        parent::__construct($commonWell);
        $this->addItem($sensorTabs);
    }

    /**
     * Give You button link to NSClient+ download page
     *
     * @return \Ease\TWB\LinkButton
     */
    public function nsclientDownload()
    {
        return new \Ease\TWB\LinkButton('http://www.nsclient.org/download/',
            ' NSC++ '.\Ease\TWB\Part::GlyphIcon('download'), 'success',
            ['style' => "background-image:url('img/nscpp.png'); width: 212px; height: 60px; ",
            'title' => _('Download NSClient++')]);
    }
}
