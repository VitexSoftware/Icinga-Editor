<?php
/**
 * Třída pro import konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2015 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class IEImporter extends IEcfg
{
    /**
     * Pole zpracovaných souboru
     * @var array
     */
    public $files = null;

    /**
     * Pole parsovacích tříd
     * @var array
     */
    public $IEClasses = [];

    /**
     * Třída pro hromadné operace s konfigurací
     *
     * @param null $ItemID
     */
    public function __construct($params = null)
    {
        parent::__construct();
        $this->registerClass('\Icinga\Editor\Engine\IETimeperiod');
        $this->registerClass('\Icinga\Editor\Engine\IECommand');
        $this->registerClass('\Icinga\Editor\Engine\IEService');
        $this->registerClass('\Icinga\Editor\Engine\IEServicegroup');
        $this->registerClass('\Icinga\Editor\Engine\IEContact');
        $this->registerClass('\Icinga\Editor\Engine\IEContactgroup');
        $this->registerClass('\Icinga\Editor\Engine\IEHost');
        $this->registerClass('\Icinga\Editor\Engine\IEHostgroup');
        if (is_array($params)) {
            $this->setData($params);
        }
    }

    /**
     * Zaregistruje třídu pro parsování konfiguráků
     *
     * @param strung $className
     */
    public function registerClass($className)
    {
        $newClass                            = new $className;
        $this->IEClasses[$newClass->keyword] = new $className;
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
        return $this->importCfg(IEcfg::readRawConfigFile($cfgFile, $this));
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
        return $this->importCfg(array_map('trim',
                    preg_split('/\r\n|\n|\r/', $cfgText)), $commonValues);
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
            $this->addStatusMessage(sprintf(_('Načteno %s řádek konfigurace'),
                    count($cfg)), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('konfigurace nebyla načtena'),
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

        foreach ($this->IEClasses as $IEClass) {
            $doneCount += $IEClass->importArray($cfg, $this->getData());
        }
        if ($doneCount) {
            $this->addStatusMessage(sprintf(_('Bylo naimportováno %s konfigurací'),
                    $doneCount), 'success');
        } else {
            $this->addStatusMessage(_('Nic se nenaimportovalo'), 'warning');
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
                $this->addStatusMessage($ieClass->keyword.': '._('konfigurace byla vygenerována'),
                    'success');
            } else {
                $this->addStatusMessage($ieClass->keyword.': '._('konfigurace nebyla vygenerována'),
                    'warning');
            }
        }
    }
}