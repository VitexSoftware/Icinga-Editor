<?php

require_once 'Ease/EaseUser.php';

/**
 * Uživatel Icinga Editoru
 */
class IEUser extends EaseUser
{

    /**
     * Tabulka uživatelů
     * @var string
     */
    public $myTable = 'user';

    /**
     * Sloupeček obsahující datum vložení záznamu do shopu
     * @var string
     */
    public $myCreateColumn = 'DatCreate';

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do shopu
     * @var string
     */
    public $myLastModifiedColumn = 'DatSave';

    /**
     * Budeme používat serializovaná nastavení uložená ve sloupečku
     * @var string
     */
    public $settingsColumn = 'settings';

    /**
     * Vrací odkaz na ikonu
     *
     * @return string
     */
    public function getIcon()
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
    public function getFirstContact()
    {
        $contact = new IEContact();
        $cn = $contact->getColumnsFromMySQL(array($contact->nameColumn, $contact->myKeyColumn), array($contact->userColumn => $this->getUserID(), 'parent_id' => 'IS NOT NULL'), $contact->myKeyColumn, $contact->nameColumn, 1);
        if (count($cn)) {
            $curcnt = current($cn);

            return array($curcnt[$contact->myKeyColumn] => $curcnt[$contact->nameColumn]);
        }

        return null;
    }

    /**
     * Vrací ID aktuálního záznamu
     * @return int
     */
    public function getId()
    {
        return (int) $this->getMyKey();
    }

    /**
     * Změní uživateli uložené heslo
     *
     * @param string $newPassword nové heslo
     * @param int    $userID      id uživatele
     *
     * @return boolean password výsledek změny hesla
     */
    public function passwordChange($newPassword, $userID = null)
    {
        if (parent::passwordChange($newPassword, $userID)) {
            system('sudo htpasswd -b /etc/icinga/htpasswd.users ' . $this->getUserLogin() . ' ' . $newPassword);

            return true;
        }

        return false;
    }

    /**
     * Vrací mazací tlačítko
     *
     * @param  string                     $name   jméno objektu
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $urlAdd = '')
    {
        return new EaseJQConfirmedLinkButton('?user_id=' . $this->getID() . '&delete=true' . '&' . $urlAdd, _('Smazat ') . ' ' . $this->getUserLogin() . ' ' . EaseTWBPart::GlyphIcon('remove-sign'));
    }

    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }

        if ($id != $this->getId()) {
            $this->loadFromMySQL($id);
        }

        $this->myDbLink->exeQuery('DELETE from command WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from contact WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from contactgroup WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from hostgroup WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from hosts WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from servicegroup WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from service WHERE user_id=' . $id);
        $this->myDbLink->exeQuery('DELETE from timeperiod WHERE user_id=' . $id);

        $cfgfile = constant('CFG_GENERATED') . '/' . $this->getUserLogin() . '.cfg';
        if (file_exists($cfgfile)) {
            if (unlink($cfgfile)) {
                $this->addStatusMessage(sprintf(_('Konfigurace uživatele %s byla smazána'), $this->getUserLogin()), 'success');
            } else {
                $this->addStatusMessage(sprintf(_('Konfigurace uživatele %s nebyla smazána'), $this->getUserLogin()), 'error');
            }
        }

        if ($this->deleteFromMySQL()) {

            $this->addStatusMessage(sprintf(_('Uživatel %s byl smazán'), $this->getUserLogin()));

            require_once 'Ease/EaseMail.php';

            $email = new EaseMail($this->getDataValue('email'), _('Oznámení o zrušení účtu'));
            $email->setMailHeaders(array('From' => EMAIL_FROM));
            $email->addItem(new EaseHtmlDivTag(null, "Právě jste byl/a smazán/a z Aplikace VSMonitoring s těmito přihlašovacími údaji:\n"));
            $email->addItem(new EaseHtmlDivTag(null, ' Login: ' . $this->GetUserLogin() . "\n"));

            $email->send();


            return true;
        } else {
            return FALSE;
        }
    }

    /**
     * Give you user name
     *
     * @return string
     */
    public function getUserName()
    {
        $longname = trim($this->getDataValue('firstname') . ' ' . $this->getDataValue('lastname'));
        if (strlen($longname)) {
            return $longname;
        } else {
            return parent::getUserName();
        }
    }

    function getEmail()
    {
        return $this->getDataValue('email');
    }

}

class IETwitterUser extends IEUser
{

    /**
     * data z Twitteru
     * @var stdClass
     */
    public $Twitter = null;

    /**
     * Uživatel autentifikující se vůči twitteru
     *
     * @param arrat  $Twitter     id uživatele
     * @param string $TwitterName jméno uživatele
     */
    public function __construct($Twitter = null)
    {
        parent::__construct();
        if (!is_null($Twitter)) {
            $this->Twitter = $Twitter;
            $this->setmyKeyColumn('twitter_id');
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
