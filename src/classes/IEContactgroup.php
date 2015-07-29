<?php

/**
 * Konfigurace Skupin contactů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEContactgroup extends IECfg
{

    public $myTable = 'contactgroup';
    public $myKeyColumn = 'contactgroup_id';
    public $nameColumn = 'contactgroup_name';
    public $keyword = 'contactgroup';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords = array(
      'contactgroup_name' => 'VARCHAR(64)',
      'alias' => 'VARCHAR(64)',
      'members' => 'IDLIST',
      'contactgroup_members' => 'IDLIST'
    );
    public $keywordsInfo = array(
      'contactgroup_name' => array(
        'severity' => 'mandatory',
        'title' => 'název skupiny kontaktů',
        'required' => true
      ),
      'alias' => array(
        'severity' => 'basic',
        'title' => 'alias skupiny kontaktů',
        'required' => true
      ),
      'members' => array(
        'title' => 'členské kontakty',
        'severity' => 'mandatory',
        'required' => true,
        'refdata' => array(
          'table' => 'contact',
          'captioncolumn' => 'contact_name',
          'idcolumn' => 'contact_id')
      ),
      'contactgroup_members' => array(
        'severity' => 'optional',
        'title' => 'členské skupiny',
        'refdata' => array(
          'table' => 'contactgroup',
          'captioncolumn' => 'contactgroup_name',
          'idcolumn' => 'contactgroup_id')
      )
    );

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-contactgroup';

}
