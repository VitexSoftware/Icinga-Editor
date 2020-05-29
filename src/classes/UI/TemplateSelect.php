<?php

namespace Icinga\Editor\UI;

/**
 * Volba služeb patřičných k hostu
 *
 * @todo dodělat
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class TemplateSelect extends \Ease\Html\Select {

    /**
     * Zdroj Dat
     * @var \Icinga\Editor\Engine\Configurator
     */
    public $dataSource = null;

    /**
     * Volba predlohy
     *
     * @param string $name
     * @param \Icinga\Editor\Engine\Configurator $dataSource
     * @param int $defaultValue
     * @param array $properties
     */
    public function __construct($name, $dataSource, $defaultValue = null,
            $properties = null) {
        $this->dataSource = $dataSource;
        parent::__construct($name, null, $defaultValue, null, $properties);
    }

    /**
     * Load templates from database
     * 
     * @return array
     */
    public function loadItems() {
        $templates = $this->dataSource->getColumnsFromSQL(['name'],
                ['register' => 0, 'generate' => 1], 'name', 'name');
        return array_merge($templates, ['' => _('Without template')]);
    }

}
