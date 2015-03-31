<?php

/**
 * Konfigurace Skupin služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

class IEServicegroup extends IECfg
{

    public $myTable = 'servicegroup';
    public $myKeyColumn = 'servicegroup_id';
    public $nameColumn = 'servicegroup_name';
    public $keyword = 'servicegroup';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords = array(
      'servicegroup_name' => 'VARCHAR(64)',
      'alias' => 'VARCHAR(64)',
      'members' => 'TEXT',
      'servicegroup_members' => 'IDLIST',
      'notes' => 'TEXT',
      'notes_url' => 'VARCHAR(128)',
      'action_url' => 'VARCHAR(128)'
    );
    public $keywordsInfo = array(
      'servicegroup_name' => array(
        'severity' => 'mandatory',
        'title' => 'název skupiny služeb',
        'required' => true
      ),
      'alias' => array(
        'severity' => 'basic',
        'title' => 'alias skupiny služeb',
        'required' => true
      ),
      'members' => array(
        'severity' => 'mandatory',
        'title' => 'členské kontakty (zatím nutno definovat ručně)',
        'refdata' => array(
          'table' => 'service',
          'captioncolumn' => 'service_description',
          'idcolumn' => 'service_id',
          'condition' => array('register' => 1))
      ),
      'servicegroup_members' => array(
        'severity' => 'optional',
        'title' => 'členské skupiny',
        'refdata' => array(
          'table' => 'servicegroup',
          'captioncolumn' => 'servicegroup_name',
          'idcolumn' => 'servicegroup_id',
          'condition' => array('register' => 1))
      ),
      'notes' => array(
        'severity' => 'optional',
        'title' => 'poznámky'),
      'notes_url' => array(
        'severity' => 'advanced',
        'title' => 'externí poznámky'),
      'action_url' => array(
        'severity' => 'advanced',
        'title' => 'externí akce'
      )
    );

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-servicegroup';

    /**
     * Načte výchozí skupinu
     *
     * @param string $name
     */
    public function loadDefault($name)
    {

    }

}
