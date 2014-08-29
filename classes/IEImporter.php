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
    public function __construct($Params = null)
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

    public function registerClass($ClassName)
    {
        if (file_exists('classes/' . $ClassName . '.php')) {
            include_once $ClassName . '.php';
        }
        $NewClass = new $ClassName;
        $this->IEClasses[$NewClass->keyword] = new $ClassName;
    }

    /**
     * Znovu vytvoří struktury tabulek obejktů
     */
    public function dbInit()
    {
        foreach ($this->IEClasses as $IEClass) {
            $IEClass->dbInit();
        }
    }

    /**
     * Naimportuje konfiguraci ze souboru
     *
     * @param  string $CfgFile
     * @return int    počet uložených konfigurací
     */
    public function importCfgFile($CfgFile)
    {
        return $this->importCfg(IECfg::readRawConfigFile($CfgFile));
    }

    /**
     * Naimportuje konfiguraci z textového řetězce
     *
     * @param  string $CfgText      text
     * @param  array  $CommonValues globálně uplatněné hodnoty
     * @return int    počet vloženýh konfigurací
     */
    public function importCfgText($CfgText, $CommonValues)
    {
        return $this->importCfg(array_map('trim', preg_split('/\r\n|\n|\r/',$CfgText)));
    }

    /**
     * Naimportuje konfiguraci ze souboru
     *
     * @param  string $CfgFile
     * @return int    počet uložených konfigurací
     */
    public function importCfg($Cfg)
    {
        $DoneCount = 0;
        if (count($Cfg)) {
            $this->addStatusMessage(sprintf(_('Načteno %s řádek konfigurace'), count($Cfg)), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('konfigurace nebyla načtena'), count($Cfg)), 'warning');
        }

        if ($this->userColumn) {
            $this->setDataValue($this->userColumn, EaseShared::user()->getUserID());
        }

        if (is_null($this->getDataValue('register'))) {
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

    /**
     * Vygeneruje do souboru konfiguraci icingy aktuálního uživatele
     *
     * @param string $fileName soubor
     */
    public function writeConfigs($fileName)
    {
        foreach ($this->IEClasses as $ieClass) {
            if ($ieClass->writeConfig($fileName)) {
                $this->addStatusMessage( $ieClass->keyword.': '._('konfigurace byla vygenerována'), 'success');
            } else {
                $this->addStatusMessage($ieClass->keyword.': '._('konfigurace nebyla vygenerována'), 'warning');
            }
        }
    }

}
