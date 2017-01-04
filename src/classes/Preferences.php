<?php

namespace Icinga\Editor;

/**
 * Description of IEPreferences
 *
 * @author vitex
 */
class Preferences extends \Ease\Brick
{
    public $myTable     = 'preferences';
    public $myKeyColumn = 'key';

    /**
     * Pole předvoleb
     * @var array
     */
    public $preferences = [];

    /**
     * Objekt předvoleb
     */
    function __construct()
    {
        parent::__construct();
        $this->loadPrefs();
    }

    /**
     * How to print object as string
     */
    public function __toString()
    {
        echo $this->getDataValue('value');
    }

    /**
     * Uloží jednu předvolbu
     *
     * @param type $key
     * @param type $value
     *
     * @return type
     */
    function saveOnePreference($key, $value)
    {
        $this->preferences[$key] = $value;
        $this->setMyKey($key);
        $this->setDataValue('value', $value);
        return $this->saveToSQL(null, true);
    }

    /**
     * Uloží pole předvoleb
     *
     * @param array $data
     *
     * @return int
     */
    public function savePrefs($data)
    {
        $ok = 0;
        foreach ($data as $key => $value) {
            if ($this->saveOnePreference($key, $value)) {
                $ok++;
            }
        }
        return $ok;
    }

    /**
     * Načte předvolby
     */
    public function loadPrefs()
    {
        $prefs = $this->getAllFromSQL();
        foreach ($prefs as $pref) {
            $this->preferences[$pref['key']] = $pref['value'];
        }
        return count($this->preferences);
    }

    /**
     * Vrací nastavení
     *
     * @return array pole nastavení
     */
    function getPrefs()
    {
        return $this->preferences;
    }

}
