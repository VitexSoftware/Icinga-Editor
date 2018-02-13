<?php

namespace Icinga\Editor;

/**
 * Watched services presets editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class Stemplate extends Engine\Configurator
{
    public $myTable    = 'stemplate';
    public $keyColumn  = 'stemplate_id';
    public $nameColumn = 'stemplate_name';
    public $keyword    = 'stemplate';

    /**
     * Public ?
     * @var boolean
     */
    public $publicRecords = false;

    /**
     * Used Columns
     * @var array
     */
    public $useKeywords = [
        'stemplate_name' => 'VARCHAR(64)',
        'services' => 'IDLIST',
        'contacts' => 'IDLIST',
        'notes' => 'TEXT'
    ];

    /**
     * Columns info
     * @var array
     */
    public $keywordsInfo = [];

    /**
     * Předloha sledovaných služeb
     *
     * @param int|string $itemID
     */
    public function __construct($itemID = null)
    {

        $this->keywordsInfo = [
            'stemplate_name' => [
                'severity' => 'mandatory',
                'title' => _('Watched services preset name'),
                'required' => true
            ],
            'services' => [
                'severity' => 'mandatory',
                'title' => _('memeber services'),
                'refdata' => [
                    'table' => 'service',
                    'captioncolumn' => 'service_description',
                    'idcolumn' => 'service_id',
                    'condition' => ['register' => 1]]
            ],
            'contacts' => [
                'severity' => 'advanced',
                'title' => _('member contacts'),
                'refdata' => [
                    'table' => 'contact',
                    'captioncolumn' => 'contact_name',
                    'idcolumn' => 'contact_id',
                    'condition' => ['register' => 1]]
            ],
            'notes' => [
                'severity' => 'optional',
                'title' => _('notes')]
        ];

        parent::__construct($itemID);
        unset($this->useKeywords['generate']);
        unset($this->keywordsInfo['generate']);
    }

    /**
     * Zkontroluje všechny záznamy a přeskočí cizí záznamy
     *
     * @param  array $allData všechna vstupní data
     * @return array
     */
    public function controlAllData($allData)
    {
        return $allData;
    }
}
