<?php
/**
 * Icinga Editor - Service Group
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Servicegroup extends Configurator
{
    public $myTable     = 'servicegroup';
    public $myKeyColumn = 'servicegroup_id';
    public $nameColumn  = 'servicegroup_name';
    public $keyword     = 'servicegroup';

    /**
     * Přidat položky register a use ?
     * @var boolean
     */
    public $allowTemplating = false;

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords   = [
        'servicegroup_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'members' => 'IDLIST',
        'servicegroup_members' => 'IDLIST',
        'notes' => 'TEXT',
        'notes_url' => 'VARCHAR(128)',
        'action_url' => 'VARCHAR(128)'
    ];
    public $keywordsInfo  = [
        'servicegroup_name' => [
            'severity' => 'mandatory',
            'title' => 'název skupiny služeb',
            'required' => true
        ],
        'alias' => [
            'severity' => 'basic',
            'title' => 'alias skupiny služeb',
            'required' => true
        ],
        'members' => [
            'severity' => 'mandatory',
            'title' => 'členské služby',
            'refdata' => [
                'table' => 'service',
                'captioncolumn' => 'service_description',
                'idcolumn' => 'service_id',
                'condition' => ['register' => 1]
            ]
        ],
        'servicegroup_members' => [
            'severity' => 'optional',
            'title' => 'členské skupiny služeb',
            'refdata' => [
                'table' => 'servicegroup',
                'captioncolumn' => 'servicegroup_name',
                'idcolumn' => 'servicegroup_id',
            //'condition' => array('register' => 1)
            ]
        ],
        'notes' => [
            'severity' => 'optional',
            'title' => 'poznámky'],
        'notes_url' => [
            'severity' => 'advanced',
            'title' => 'externí poznámky'],
        'action_url' => [
            'severity' => 'advanced',
            'title' => 'externí akce'
        ]
    ];

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
