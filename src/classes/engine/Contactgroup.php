<?php
/**
 * Konfigurace Skupin contactů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Contactgroup extends Configurator
{
    public $myTable     = 'contactgroup';
    public $keyColumn = 'contactgroup_id';
    public $nameColumn  = 'contactgroup_name';
    public $keyword     = 'contactgroup';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords   = [
        'contactgroup_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'members' => 'IDLIST',
        'contactgroup_members' => 'IDLIST'
    ];
    public $keywordsInfo  = [
        'contactgroup_name' => [
            'severity' => 'mandatory',
            'title' => 'název skupiny kontaktů',
            'required' => true
        ],
        'alias' => [
            'severity' => 'basic',
            'title' => 'alias skupiny kontaktů',
            'required' => true
        ],
        'members' => [
            'title' => 'členské kontakty',
            'severity' => 'mandatory',
            'required' => true,
            'refdata' => [
                'table' => 'contact',
                'captioncolumn' => 'contact_name',
                'idcolumn' => 'contact_id']
        ],
        'contactgroup_members' => [
            'severity' => 'optional',
            'title' => 'členské skupiny',
            'refdata' => [
                'table' => 'contactgroup',
                'captioncolumn' => 'contactgroup_name',
                'idcolumn' => 'contactgroup_id']
        ]
    ];

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-contactgroup';

}
