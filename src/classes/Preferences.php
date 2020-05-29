<?php

namespace Icinga\Editor;

/**
 * Description of Preferences
 *
 * @author vitex
 */
class Preferences extends \Ease\Brick {

    public $myTable = 'preferences';
    public $keyColumn = 'key';

    /**
     * Preferences array
     * @var array
     */
    public $preferences = [];

    /**
     * Preferences class
     */
    function __construct() {
        parent::__construct();
        $this->loadPrefs();
    }

    /**
     * How to print object as string
     */
    public function __toString() {
        echo $this->getDataValue('value');
    }

    /**
     * Save one preference
     *
     * @param string $key
     * @param string $value
     *
     * @return int|null record id or null
     */
    function saveOnePreference($key, $value) {
        $this->preferences[$key] = $value;
        $this->setMyKey($key);
        $this->setDataValue('value', $value);
        return $this->saveToSQL(null, true);
    }

    /**
     * Save preferences array
     *
     * @param array $data
     *
     * @return int
     */
    public function savePrefs($data) {
        $ok = 0;
        foreach ($data as $key => $value) {
            if ($this->saveOnePreference($key, $value)) {
                $ok++;
            }
        }
        return $ok;
    }

    /**
     * Read Presets
     */
    public function loadPrefs() {
        $prefs = $this->getAllFromSQL();
        foreach ($prefs as $pref) {
            $this->preferences[$pref['key']] = $pref['value'];
        }
        return count($this->preferences);
    }

    /**
     * Obtain Preferences
     *
     * @return array of preferences
     */
    function getPrefs() {
        return $this->preferences;
    }

}
