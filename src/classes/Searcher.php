<?php

namespace Icinga\Editor;

/**
 * Třída pro import konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class Searcher extends engine\Configurator
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
    public $IEClasses = [];

    /**
     * Třída pro hromadné operace s konfigurací
     *
     * @param null $ItemID
     */
    public function __construct($table = null, $column = null)
    {
        $this->table  = $table;
        $this->column = $column;
        parent::__construct();

        $this->registerClass('Icinga\Editor\Engine\IEHost');
        $this->registerClass('Icinga\Editor\Engine\IEHostgroup');
        $this->registerClass('Icinga\Editor\Engine\IECommand');
        $this->registerClass('Icinga\Editor\Engine\IEService');
        $this->registerClass('Icinga\Editor\Engine\IEServicegroup');
        $this->registerClass('Icinga\Editor\Engine\IEContact');
        $this->registerClass('Icinga\Editor\Engine\IEContactgroup');
        $this->registerClass('Icinga\Editor\Engine\IETimeperiod');
    }

    public function registerClass($className)
    {
        $newClass                            = new $className;
        $this->IEClasses[$newClass->keyword] = new $className;
    }

    public function searchAll($term)
    {
        $results = [];
        foreach ($this->IEClasses as $ieClass) {
            if (!is_null($this->table) && ($ieClass->getMyTable() != $this->table)) {
                continue;
            }
            if (!is_null($this->column)) {
                if (isset($ieClass->useKeywords[$this->column])) {
                    $ieClass->useKeywords = [$this->column => $ieClass->useKeywords[$this->column]];
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