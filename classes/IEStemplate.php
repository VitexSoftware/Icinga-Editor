<?php

/**
 * Konfigurace Předloh sledovaných služeb
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEStemplate extends IEcfg
{

    public $myTable = 'stemplate';
    public $myKeyColumn = 'stemplate_id';
    public $nameColumn = 'stemplate_name';
    public $keyword = 'stemplate';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;

    /**
     * Použité sloupce
     * @var array
     */
    public $useKeywords = array(
      'stemplate_name' => 'VARCHAR(64)',
      'services' => 'IDLIST',
      'contacts' => 'IDLIST',
      'notes' => 'TEXT'
    );

    /**
     * Informace o sloupečcích
     * @var array
     */
    public $keywordsInfo = array(
      'stemplate_name' => array(
        'severity' => 'mandatory',
        'title' => 'název předlohy sledovaných služeb',
        'required' => true
      ),
      'services' => array(
        'severity' => 'mandatory',
        'title' => 'členské služby',
        'refdata' => array(
          'table' => 'service',
          'captioncolumn' => 'service_description',
          'idcolumn' => 'service_id',
          'condition' => array('register' => 1))
      ),
      'contacts' => array(
        'severity' => 'advanced',
        'title' => 'členské kontakty',
        'refdata' => array(
          'table' => 'contact',
          'captioncolumn' => 'contact_name',
          'idcolumn' => 'contact_id',
          'condition' => array('register' => 1))
      ),
      'notes' => array(
        'severity' => 'optional',
        'title' => 'poznámky')
    );

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
