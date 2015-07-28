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
class IEScript extends IECfg
{

    public $myTable = 'script';
    public $myKeyColumn = 'script_id';
    public $nameColumn = 'filename';
    public $keyword = 'script';

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
      'filename' => 'VARCHAR(128)',
      'body' => 'TEXT',
      'user_id' => 'INT',
      'public' => 'BOOLEAN',
      'platform' => "PLATFORM"
    );

    /**
     * Info
     * @var array
     */
    public $keywordsInfo = array(
      'filename' => array(
        'severity' => 'mandatory',
        'title' => 'název příkazu', 'required' => true),
      'body' => array(
        'severity' => 'mandatory',
        'title' => 'tělo skriptu', 'required' => true),
      'user_id' => array(
        'severity' => 'advanced',
        'title' => 'vlastník příkazu', 'required' => false),
      'public' => array(
        'severity' => 'advanced',
        'title' => 'přístupnost'),
      'platform' => array(
        'severity' => 'basic',
        'title' => 'Platforma', 'mandatory' => true)
    );

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * Třída skriptu
     *
     * @param type $itemID
     */
    public function __construct($itemID = null)
    {
        parent::__construct($itemID);
        unset($this->keywordsInfo['generate']);
        unset($this->useKeywords['generate']);
    }

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
            unset($AllData[$ADkey]['script_type']);
            unset($AllData[$ADkey]['script_local']);
            unset($AllData[$ADkey]['script_remote']);
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
            unset($AllData[$ADkey]['script_local']);
            unset($AllData[$ADkey]['script_remote']);
            unset($AllData[$ADkey]['script_type']);
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
        return parent::deleteButton(_('skript'), $addUrl);
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
        return parent::takeData($data, $dataPrefix);
    }

    /**
     * vrací skript
     */
    public function getCfg($send = TRUE)
    {
        $script = $this->getDataValue('body');
        if ($send) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        switch ($this->getDataValue('platform')) {
            case 'windows':
                if ($send) {
                    header('Content-Disposition: attachment; filename=' . $this->getName());
                }
                $script = str_replace("\n", "\r\n", $script);
                break;
            case 'linux':
                if ($send) {
                    header('Content-Disposition: attachment; filename=' . $this->getName());
                }
                break;
            default:
                break;
        }
        if ($send) {
            header('Content-Length: ' . strlen($script));
            echo $script;
        } else {
            return $script;
        }
    }

}
