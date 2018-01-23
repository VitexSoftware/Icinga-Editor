<?php
/**
 * Command configurator
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

/**
 * Command Object
 */
class Command extends Configurator
{
    public $myTable     = 'command';
    public $KeyColumn = 'command_id';
    public $nameColumn  = 'command_name';
    public $keyword     = 'command';

    /**
     * Add register and use columns ?
     * @var boolean
     */
    public $allowTemplating = false;

    /**
     * Items
     * @var array
     */
    public $useKeywords = [
        'command_name' => 'VARCHAR(128)',
        'command_line' => 'TEXT',
        'command_type' => "ENUM('check','notify','handler')",
        'command_local' => 'BOOL',
        'command_remote' => 'BOOL',
        'script_id' => 'SELECTID',
        'platform' => "PLATFORM"
    ];

    /**
     * Info
     * @var array
     */
    public $keywordsInfo = [];

    /**
     * Command class
     * @param int $itemID
     */
    public function __construct($itemID = null)
    {
        $this->keywordsInfo = [
            'command_name' => [
                'severity' => 'mandatory',
                'title' => _('Command name'), 'required' => true],
            'command_line' => [
                'severity' => 'mandatory',
                'title' => _('Command'), 'required' => true],
            'command_type' => [
                'severity' => 'mandatory',
                'title' => _('Command type'), 'required' => true],
            'command_local' => [
                'severity' => 'basic',
                'title' => _('local command')],
            'command_remote' => [
                'severity' => 'basic',
                'title' => _('remote command NRPE/Nsc++')],
            'script_id' => [
                'severity' => 'basic',
                'title' => _('Deploy'),
                'refdata' => [
                    'table' => 'script',
                    'captioncolumn' => 'filename',
                    'idcolumn' => 'script_id',
                    'public' => true
                ]
            ],
            'platform' => [
                'severity' => 'basic',
                'title' => _('Platform'), 'mandatory' => true]
        ];

        parent::__construct($itemID);
    }
    /**
     * Object documentation URL
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-command';

    /**
     * Can be record public ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * Obtain data for user
     *
     * @return array
     */
    public function getAllUserData()
    {
        $AllData = parent::getAllUserData();
        foreach ($AllData as $ADkey => $AD) {
            unset($AllData[$ADkey]['deploy']);
            unset($AllData[$ADkey]['command_type']);
            unset($AllData[$ADkey]['command_local']);
            unset($AllData[$ADkey]['command_remote']);
        }

        return $AllData;
    }

    /**
     * Obtain all data
     *
     * @return array
     */
    public function getAllData()
    {
        $AllData = parent::getAllData();
        foreach ($AllData as $ADkey => $AD) {
            unset($AllData[$ADkey]['deploy']);
            unset($AllData[$ADkey]['command_local']);
            unset($AllData[$ADkey]['command_remote']);
            unset($AllData[$ADkey]['command_type']);
            unset($AllData[$ADkey]['script_id']);
        }

        return $AllData;
    }

    /**
     * Delete Button
     *
     * @param  string                     $name
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('Command'), $addUrl);
    }

    /**
     * Take data to object
     *
     * @param  array  $data
     * @param  string $dataPrefix
     * @return int    taken items count
     */
    public function takeData($data, $dataPrefix = null)
    {
        if (!isset($data['command_type'])) {
            if (strstr($data[$this->nameColumn], 'notify')) {
                $data['command_type'] = 'notify';
            } else {
                $data['command_type'] = 'check';
            }
        }
        return parent::takeData($data, $dataPrefix);
    }
}
