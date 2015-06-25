<?php

/**
 * Konfigurace Kontaktů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

/**
 * Spráce kontaktů
 */
class IECommand extends IECfg
{

    public $myTable = 'command';
    public $myKeyColumn = 'command_id';
    public $nameColumn = 'command_name';
    public $keyword = 'command';

    /**
     * Přidat položky register a use ?
     * @var boolean
     */
    public $allowTemplating = false;

    /**
     * Položky
     * @var array
     */
    public $useKeywords = array(
      'command_name' => 'VARCHAR(128)',
      'command_line' => 'TEXT',
      'command_type' => "ENUM('check','notify','handler')",
      'command_local' => 'BOOL',
      'command_remote' => 'BOOL',
      'script_id' => 'SELECTID',
      'platform' => "PLATFORM"
    );

    /**
     * Info
     * @var array
     */
    public $keywordsInfo = array(
      'command_name' => array(
        'severity' => 'mandatory',
        'title' => 'název příkazu', 'required' => true),
      'command_line' => array(
        'severity' => 'mandatory',
        'title' => 'příkaz', 'required' => true),
      'command_type' => array(
        'severity' => 'mandatory',
        'title' => 'druh příkazu', 'required' => true),
      'command_local' => array(
        'severity' => 'basic',
        'title' => 'lokální příkaz'),
      'command_remote' => array(
        'severity' => 'basic',
        'title' => 'vzdálený příkaz NRPE/Nsc++'),
      'script_id' => array(
        'severity' => 'basic',
        'title' => 'Instalace',
        'refdata' => array(
          'table' => 'script',
          'captioncolumn' => 'filename',
          'idcolumn' => 'script_id',
          'public' => true
        )
      ),
      'platform' => array(
        'severity' => 'basic',
        'title' => 'Platforma', 'mandatory' => true)
    );

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-command';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * Vrací všechna data uživatele
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
     * Vrací všechna data
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
        }

        return $AllData;
    }

    /**
     * Vrací mazací tlačítko
     *
     * @param  string                     $name
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('příkaz'), $addUrl);
    }

    /**
     * Načte data do objektu
     *
     * @param  array  $data
     * @param  string $dataPrefix
     * @return int    počet převzatých řádek
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
