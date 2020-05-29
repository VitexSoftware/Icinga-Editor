<?php

namespace Icinga\Editor;

/**
 * Searcher Class
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
class Searcher extends engine\Configurator {

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
    public $ieClasses = [];

    /**
     * Třída pro hromadné operace s konfigurací
     *
     * @param null $ItemID
     */
    public function __construct($table = null, $column = null) {
        $this->table = $table;
        $this->column = $column;
        parent::__construct();

        $this->registerClass('Icinga\Editor\Engine\Host');
        $this->registerClass('Icinga\Editor\Engine\Hostgroup');
        $this->registerClass('Icinga\Editor\Engine\Command');
        $this->registerClass('Icinga\Editor\Engine\Service');
        $this->registerClass('Icinga\Editor\Engine\Servicegroup');
        $this->registerClass('Icinga\Editor\Engine\Contact');
        $this->registerClass('Icinga\Editor\Engine\Contactgroup');
        $this->registerClass('Icinga\Editor\Engine\Timeperiod');
    }

    public function registerClass($className) {
        $newClass = new $className;
        $this->ieClasses[$newClass->keyword] = new $className;
    }

    public function searchAll($term) {
        $results = [];
        foreach ($this->ieClasses as $ieClass) {
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
