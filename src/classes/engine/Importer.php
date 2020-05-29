<?php

/**
 * Configuration importer
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2015 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Importer extends Configurator {

    /**
     * Files to process
     * @var array
     */
    public $files = null;

    /**
     * Parser classes
     * @var array
     */
    public $parseClasses = [];

    /**
     * Třída pro hromadné operace s konfigurací
     *
     * @param null $ItemID
     */
    public function __construct($params = null) {
        parent::__construct();
        $this->registerClass('\Icinga\Editor\Engine\Timeperiod');
        $this->registerClass('\Icinga\Editor\Engine\Command');
        $this->registerClass('\Icinga\Editor\Engine\Service');
        $this->registerClass('\Icinga\Editor\Engine\Servicegroup');
        $this->registerClass('\Icinga\Editor\Engine\Contact');
        $this->registerClass('\Icinga\Editor\Engine\Contactgroup');
        $this->registerClass('\Icinga\Editor\Engine\Host');
        $this->registerClass('\Icinga\Editor\Engine\Hostgroup');
        if (is_array($params)) {
            $this->setData($params);
        }
    }

    /**
     * Zaregistruje třídu pro parsování konfiguráků
     *
     * @param strung $className
     */
    public function registerClass($className) {
        $newClass = new $className;
        $this->parseClasses[$newClass->keyword] = new $className;
    }

    /**
     * Create table structure again
     * @deprecated since version 1.2.1
     */
    public function dbInit() {
        foreach ($this->parseClasses as $ieClass) {
            $ieClass->dbInit();
        }
    }

    /**
     * Import Configuration from path File or directory contents
     * 
     * @param string $path import target
     * @return array import results
     */
    function importCfgPath($path) {
        $imported = [];
        if (is_dir($path)) {
            $imported = $this->importCfg(Configurator::readRawConfigDir($path, $this));
        } else {
            $imported = $this->importCfgFile($path);
        }
        return $imported;
    }

    /**
     * Naimportuje konfiguraci ze souboru
     *
     * @param  string $cfgFile
     * @return int    počet uložených konfigurací
     */
    public function importCfgFile($cfgFile) {
        return $this->importCfg(Configurator::readRawConfigFile($cfgFile, $this));
    }

    /**
     * Naimportuje konfiguraci z textového řetězce
     *
     * @param  string $cfgText      text
     * @param  array  $commonValues globálně uplatněné hodnoty
     * @return int    počet vloženýh konfigurací
     */
    public function importCfgText($cfgText, $commonValues) {
        return $this->importCfg(array_map('trim',
                                preg_split('/\r\n|\n|\r/', $cfgText)), $commonValues);
    }

    /**
     * Naimportuje konfiguraci ze souboru
     *
     * @param  string $cfg
     * @return int    počet uložených konfigurací
     */
    public function importCfg($cfg) {
        $doneCount = 0;
        if (count($cfg)) {
            $this->addStatusMessage(sprintf(_('%s lines of configuration read'),
                            count($cfg)), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('configuration parsing failed'),
                            count($cfg)), 'warning');
            return 0;
        }

        if ($this->userColumn) {
            $this->setDataValue($this->userColumn,
                    \Ease\Shared::user()->getUserID());
        }

        if (is_null($this->getDataValue('register'))) {
            $this->setDataValue('register', 1);
        }

        foreach ($this->parseClasses as $IEClass) {
            $doneCount += $IEClass->importArray($cfg, $this->getData());
        }
        if ($doneCount) {
            $this->addStatusMessage(sprintf(_('%s configurations imported'),
                            $doneCount), 'success');
        } else {
            $this->addStatusMessage(_('None imported'), 'warning');
        }

        return $doneCount;
    }

    /**
     * Vygeneruje do souboru konfiguraci icingy aktuálního uživatele
     *
     * @param string $fileName soubor
     */
    public function writeConfigs($fileName) {
        foreach ($this->parseClasses as $ieClass) {
            if ($ieClass->writeConfig($fileName)) {
                $this->addStatusMessage($ieClass->keyword . ': ' . _('Configuration was generated'),
                        'success');
            } else {
                $this->addStatusMessage($ieClass->keyword . ': ' . _('Configuration was not generated'),
                        'warning');
            }
        }
    }

}
