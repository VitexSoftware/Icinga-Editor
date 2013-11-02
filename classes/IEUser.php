<?php

require_once 'Ease/EaseUser.php';

/**
 * Uživatel Icinga Editoru
 */
class IEUser extends EaseUser
{

    /**
     * Budeme používat serializovaná nastavení uložená ve sloupečku
     * @var string 
     */
    public $SettingsColumn = 'settings';

    /**
     * Vrací odkaz na ikonu
     * 
     * @return string 
     */
    function getIcon()
    {
        $Icon = $this->GetSettingValue('icon');
        if (is_null($Icon)) {
            return parent::getIcon();
        } else {
            return $Icon;
        }
    }

    /**
     * Vrací jméno prvního kontaktu uživatele
     */
    function getFirstContactName(){
        $Contact = new IEContact();
        $Cn = $Contact->getColumnsFromMySQL($Contact->NameColumn, array($Contact->UserColumn => $this->getUserID()), $Contact->MyKeyColumn, $Contact->NameColumn, 1);
        if(count($Cn)){
            return current(current($Cn));
        }
        return null;
    }
    
}


class IETwitterUser extends IEUser
{
    /**
     * Data z Twitteru
     * @var stdClass 
     */
    public $Twitter = null;
    /**
     * Uživatel autentifikující se vůči twitteru
     * 
     * @param arrat    $Twitter   id uživatele
     * @param string $TwitterName jméno uživatele
     */
    function __construct($Twitter = null)
    {
        parent::__construct();
        if (!is_null($Twitter)) {
            $this->Twitter = $Twitter;
            $this->setMyKeyColumn('twitter_id');
            $this->setMyKey($Twitter->id);
            if (!$this->loadFromMySQL()) {
                $this->restoreObjectIdentity();
                $this->setDataValue($this->LoginColumn, $Twitter->screen_name);
                $this->setSettingValue('icon', $Twitter->profile_image_url);
                if ($this->saveToMySQL()) {
                    $this->addStatusMessage(_(sprintf('Vytvořeno spojení s Twitterem', $Twitter->screen_name), 'success'));
                    $this->loginSuccess();
                }
            } else {
                $this->restoreObjectIdentity();
            }
            $this->setObjectName();
        }
    }

}
?>
