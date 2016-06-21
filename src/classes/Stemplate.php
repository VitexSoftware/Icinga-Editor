<?php
namespace Icinga\Editor;

/**
 * Konfigurace Předloh sledovaných služeb
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class Stemplate extends Engine\Configurator
{
    public $myTable     = 'stemplate';
    public $myKeyColumn = 'stemplate_id';
    public $nameColumn  = 'stemplate_name';
    public $keyword     = 'stemplate';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;

    /**
     * Použité sloupce
     * @var array
     */
    public $useKeywords = [
        'stemplate_name' => 'VARCHAR(64)',
        'services' => 'IDLIST',
        'contacts' => 'IDLIST',
        'notes' => 'TEXT'
    ];

    /**
     * Informace o sloupečcích
     * @var array
     */
    public $keywordsInfo = [
        'stemplate_name' => [
            'severity' => 'mandatory',
            'title' => 'název předlohy sledovaných služeb',
            'required' => true
        ],
        'services' => [
            'severity' => 'mandatory',
            'title' => 'členské služby',
            'refdata' => [
                'table' => 'service',
                'captioncolumn' => 'service_description',
                'idcolumn' => 'service_id',
                'condition' => ['register' => 1]]
        ],
        'contacts' => [
            'severity' => 'advanced',
            'title' => 'členské kontakty',
            'refdata' => [
                'table' => 'contact',
                'captioncolumn' => 'contact_name',
                'idcolumn' => 'contact_id',
                'condition' => ['register' => 1]]
        ],
        'notes' => [
            'severity' => 'optional',
            'title' => 'poznámky']
    ];

    /**
     * Předloha sledovaných služeb
     *
     * @param int|string $itemID
     */
    public function __construct($itemID = null)
    {
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