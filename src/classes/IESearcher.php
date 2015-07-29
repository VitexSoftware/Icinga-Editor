<?php

/**
 * Třída pro import konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IESearcher extends IECfg
{

    /**
     * Prohledávaná tabulka
     * @var string
     */
    public $table = null;

    /**
     * Prohledávaný sloupeček
     * @var string
     */
    public $column = null;

    /**
     * Pole prohledávacích obejktů
     * @var array
     */
    public $IEClasses = array();

    /**
     * Třída pro hromadné operace s konfigurací
     *
     * @param null $ItemID
     */
    public function __construct($table = null, $column = null)
    {
        $this->table = $table;
        $this->column = $column;
        parent::__construct();

        $this->registerClass('IETimeperiod');
        $this->registerClass('IECommand');
        $this->registerClass('IEService');
        $this->registerClass('IEServicegroup');
        $this->registerClass('IEContact');
        $this->registerClass('IEContactgroup');
        $this->registerClass('IEHost');
        $this->registerClass('IEHostgroup');
    }

    public function registerClass($className)
    {
        if (file_exists('classes/' . $className . '.php')) {
            include_once $className . '.php';
        }
        $newClass = new $className;
        $this->IEClasses[$newClass->keyword] = new $className;
    }

    public function searchAll($term)
    {
        $results = array();
        foreach ($this->IEClasses as $ieClass) {
            if (!is_null($this->table) && ($ieClass->getMyTable() != $this->table)) {
                continue;
            }
            if (!is_null($this->column)) {
                if (isset($ieClass->useKeywords[$this->column])) {
                    $ieClass->useKeywords = array($this->column => $ieClass->useKeywords[$this->column]);
                }
            }
            $found = $ieClass->searchString($term);
            if ($found) {
                $results[$ieClass->keyword] = $found;
            }
        }
        return $results;
    }

}
