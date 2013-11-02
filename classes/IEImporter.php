<?php

/**
 * Třída pro import konfigurace
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

/**
 * Description of IEImporter
 *
 * @author vitex
 */
class IEImporter extends IECfg
{

    public $IEClasses = array();

    /**
     * Třída pro hromadné operace s konfigurací
     * 
     * @param null $ItemID
     */
    function __construct($Params = null)
    {
        parent::__construct();
        $this->registerClass('IETimeperiod');
        $this->registerClass('IECommand');
        $this->registerClass('IEService');
        $this->registerClass('IEServicegroup');
        $this->registerClass('IEContact');
        $this->registerClass('IEContactgroup');
        $this->registerClass('IEHost');
        $this->registerClass('IEHostgroup');
        if (is_array($Params)) {
            $this->setData($Params);
        }
    }

    function registerClass($ClassName)
    {
        if (file_exists('classes/' . $ClassName . '.php')) {
            include_once $ClassName . '.php';
        }
        $NewClass = new $ClassName;
        $this->IEClasses[$NewClass->Keyword] = new $ClassName;
    }

    /**
     * Znovu vytvoří struktury tabulek obejktů
     */
    function dbInit()
    {
        foreach ($this->IEClasses as $IEClass) {
            $IEClass->dbInit();
        }
    }

    /**
     * Naimportuje konfiguraci ze souboru
     * 
     * @param string $CfgFile
     * @return int počet uložených konfigurací
     */
    function importCfgFile($CfgFile)
    {
        return $this->importCfg(IECfg::readRawConfigFile($CfgFile));
    }

    /**
     * Naimportuje konfiguraci z textového řetězce
     * 
     * @param string $CfgText text
     * @param array $CommonValues globálně uplatněné hodnoty
     * @return int počet vloženýh konfigurací
     */
    function importCfgText($CfgText, $CommonValues)
    {
        return $this->importCfg(array_map('trim', preg_split('/\r\n|\n|\r/',$CfgText)));
    }




    /**
     * Naimportuje konfiguraci ze souboru
     * 
     * @param string $CfgFile
     * @return int počet uložených konfigurací
     */
    function importCfg($Cfg)
    {
        $DoneCount = 0;
        if (count($Cfg)) {
            $this->addStatusMessage(sprintf(_('Načteno %s řádek konfigurace'), count($Cfg)), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('konfigurace nebyla načtena'), count($Cfg)), 'warning');
        }

        if ($this->UserColumn) {
            $this->setDataValue($this->UserColumn, EaseShared::user()->getUserID());
        }

        if(is_null($this->getDataValue('register'))){
            $this->setDataValue('register', 1);
        }
        
        foreach ($this->IEClasses as $IEClass) {
            $DoneCount += $IEClass->importArray($Cfg, $this->getData());
        }
        if ($DoneCount) {
            $this->addStatusMessage(sprintf(_('Bylo naimportováno %s konfigurací'), $DoneCount), 'success');
        }
        return $DoneCount;
    }

        
    function writeConfigs($FileName)
    {
        foreach ($this->IEClasses as $IEClass) {
            if ($IEClass->writeConfig($FileName)) {
                $this->addStatusMessage( $IEClass->Keyword.': '._('konfigurace byla vygenerována'), 'success');
            } else {
                $this->addStatusMessage($IEClass->Keyword.': '._('konfigurace nebyla vygenerována'), 'warning');
            }
        }
    }

}

?>
