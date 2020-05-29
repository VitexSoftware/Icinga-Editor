<?php

/**
 * Konfigurace Scriptů
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor;

/**
 * Scripts manager
 */
class Script extends engine\Configurator {

    public $myTable = 'script';
    public $keyColumn = 'script_id';
    public $nameColumn = 'filename';
    public $keyword = 'script';
    public $myCreateColumn = 'DatCreate';
    public $myLastModifiedColumn = 'DatSave';

    /**
     * Add register a use fields ?
     * @var boolean
     */
    public $allowTemplating = false;

    /**
     * Fields
     * @var array
     */
    public $useKeywords = [
        'filename' => 'VARCHAR(128)',
        'body' => 'TEXT',
        'user_id' => 'INT',
        'public' => 'BOOLEAN',
        'platform' => "PLATFORM"
    ];

    /**
     * Info
     * @var array
     */
    public $keywordsInfo = [];

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
    public function __construct($itemID = null) {
        $this->keywordsInfo = [
            'filename' => [
                'severity' => 'mandatory',
                'title' => _('Command Name'), 'required' => true],
            'body' => [
                'severity' => 'mandatory',
                'title' => _('Script body'), 'required' => true],
            'user_id' => [
                'severity' => 'advanced',
                'title' => _('Comand owner'), 'required' => false],
            'public' => [
                'severity' => 'advanced',
                'title' => _('Publicity')],
            'platform' => [
                'severity' => 'basic',
                'title' => _('Platform'), 'mandatory' => true]
        ];
        parent::__construct($itemID);
        unset($this->keywordsInfo['generate']);
        unset($this->useKeywords['generate']);
    }

    /**
     * Get all scripts data for user
     *
     * @return array
     */
    public function getAllUserData() {
        $allData = parent::getAllUserData();
        foreach ($allData as $adkey => $ad) {
            unset($allData[$adkey]['deploy']);
            unset($allData[$adkey]['script_type']);
            unset($allData[$adkey]['script_local']);
            unset($allData[$adkey]['script_remote']);
        }

        return $allData;
    }

    /**
     * Obtain All data
     *
     * @return array
     */
    public function getAllData() {
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
    public function deleteButton($name = null, $addUrl = '') {
        return parent::deleteButton(_('Script'), $addUrl);
    }

    /**
     * Načte data do objektu
     *
     * @param  array  $data
     * @param  string $dataPrefix
     * @return int    počet převzatých řádek
     */
    public function takeData($data, $dataPrefix = null) {
        if (array_key_exists('public', $data)) {
            $data['public'] = 1;
        } else {
            $data['public'] = 0;
        }
        return parent::takeData($data, $dataPrefix);
    }

    /**
     * vrací skript
     */
    public function getCfg($send = TRUE, $templateValue = false) {
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
            default :
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
