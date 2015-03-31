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
    public function __construct($params = null)
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
        if (is_array($params)) {
            $this->setData($params);
        }
    }

    public function registerClass($className)
    {
        if (file_exists('classes/' . $className . '.php')) {
            include_once $className . '.php';
        }
        $NewClass = new $className;
        $this->IEClasses[$NewClass->keyword] = new $className;
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
     * @param  string $cfgFile
     * @return int    počet uložených konfigurací
     */
    public function importCfgFile($cfgFile)
    {
        return $this->importCfg(IECfg::readRawConfigFile($cfgFile));
    }

    /**
     * Naimportuje konfiguraci z textového řetězce
     *
     * @param  string $cfgText      text
     * @param  array  $commonValues globálně uplatněné hodnoty
     * @return int    počet vloženýh konfigurací
     */
    public function importCfgText($cfgText, $commonValues)
    {
        return $this->importCfg(array_map('trim', preg_split('/\r\n|\n|\r/', $cfgText)), $commonValues);
    }

    /**
     * Naimportuje konfiguraci ze souboru
     *
     * @param  string $cfg
     * @return int    počet uložených konfigurací
     */
    public function importCfg($cfg)
    {
        $doneCount = 0;
        if (count($cfg)) {
            $this->addStatusMessage(sprintf(_('Načteno %s řádek konfigurace'), count($cfg)), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('konfigurace nebyla načtena'), count($cfg)), 'warning');
        }

        if ($this->userColumn) {
            $this->setDataValue($this->userColumn, EaseShared::user()->getUserID());
        }

        if (is_null($this->getDataValue('register'))) {
            $this->setDataValue('register', 1);
        }

        foreach ($this->IEClasses as $IEClass) {
            $doneCount += $IEClass->importArray($cfg, $this->getData());
        }
        if ($doneCount) {
            $this->addStatusMessage(sprintf(_('Bylo naimportováno %s konfigurací'), $doneCount), 'success');
        }

        return $doneCount;
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
                $this->addStatusMessage($ieClass->keyword . ': ' . _('konfigurace byla vygenerována'), 'success');
            } else {
                $this->addStatusMessage($ieClass->keyword . ': ' . _('konfigurace nebyla vygenerována'), 'warning');
            }
        }
    }

}
