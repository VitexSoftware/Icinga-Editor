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
     * Klíčové slovo
     * @var string
     */
    public $keyword = 'user';

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
     * Vrací výchozí kontakt uživatele
     */
    public function getDefaultContact()
    {
        return new IEContact($this->getDataValue($this->loginColumn) . ' email');
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
            if (defined('DB_IW_SERVER_PASSWORD')) {
                $mysqli = new mysqli(DB_SERVER, DB_IW_SERVER_USERNAME, DB_IW_SERVER_PASSWORD, DB_IW_DATABASE);
                if ($mysqli->connect_errno) {
                    $this->addStatusMessage("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error, 'error');
                }

                $salt = hash("sha256", uniqid($this->getUserLogin() . '_', mt_rand()));
                $pwhash = hash_hmac("sha256", $newPassword, $salt);
                $pwchquery = "UPDATE nsm_user SET user_password='" . $this->myDbLink->addSlashes($pwhash) . "', user_salt = '" . $this->myDbLink->addSlashes($salt) . "', user_modified = NOW() WHERE user_name = '" . $this->getUserLogin() . "';";

                if ($mysqli->query($pwchquery)) {
                    $this->addStatusMessage(_('Heslo bylo nastaveno i pro Icinga Web'), 'success');
                } else {
                    $this->addStatusMessage(_('Heslo bylo nastaveno i pro Icinga Web'), 'warning');
                }
                $mysqli->close();
            }
            return true;
        }

        return false;
    }

    /**
     * Založí uživatele i pro icinga-web
     * @param array $data
     * @return type
     */
    function insertToMySQL($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        $result = parent::insertToMySQL($data);

        if (defined('DB_IW_SERVER_PASSWORD')) {
            $mysqli = new mysqli(DB_SERVER, DB_IW_SERVER_USERNAME, DB_IW_SERVER_PASSWORD, DB_IW_DATABASE);
            if ($mysqli->connect_errno) {
                $this->addStatusMessage("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error, 'error');
            }

            $nuquery = "INSERT INTO nsm_user "
                . "(user_account, user_authsrc, user_disabled, user_name,  user_lastname, user_firstname, user_email,       user_password, user_salt, user_description, user_created, user_modified) VALUES "
                . "('0',         'internal',   '0',           '" . $data[$this->loginColumn] . "', '" . $data['firstname'] . "',    '" . $data['lastname'] . "',     '" . $data['email'] . "', '1',           '1',       '" . $data[$this->loginColumn] . "',       NOW(),         NOW())";

            if ($mysqli->query($nuquery)) {
                $iewuser_id = $mysqli->insert_id;

                $mysqli->query("INSERT INTO nsm_principal (principal_disabled, principal_type, principal_user_id) VALUES ('0', 'user', '$iewuser_id')");
                $mysqli->query("DELETE FROM nsm_user_role WHERE (usro_user_id = '$iewuser_id')");

                $pt_principal_id = $this->_user_principalId($iewuser_id, $mysqli);

                $mysqli->query("DELETE FROM nsm_user_role WHERE (usro_user_id = '$iewuser_id')");
                $mysqli->query("INSERT INTO nsm_user_role (usro_role_id, usro_user_id) VALUES ('1', '$iewuser_id')");
                $nsm_user_role_id = $mysqli->insert_id;



                /*
                  $principals = $this->_getPrincipals($pt_principal_id, $mysqli);
                  SELECT n.tv_pt_id AS n__tv_pt_id, n.tv_key AS n__tv_key, n.tv_val AS n__tv_val FROM nsm_target_value n WHERE (n.tv_pt_id IN ('221'))
                  DELETE FROM nsm_target_value WHERE (tv_pt_id = '221' AND tv_key = 'hostgroup')
                  DELETE FROM nsm_principal_target WHERE pt_id = '221'
                  SELECT n.tv_pt_id AS n__tv_pt_id, n.tv_key AS n__tv_key, n.tv_val AS n__tv_val FROM nsm_target_value n WHERE (n.tv_pt_id IN ('222'))
                  DELETE FROM nsm_principal_target WHERE pt_id = '222'
                  SELECT n.tv_pt_id AS n__tv_pt_id, n.tv_key AS n__tv_key, n.tv_val AS n__tv_val FROM nsm_target_value n WHERE (n.tv_pt_id IN ('223'))
                  DELETE FROM nsm_principal_target WHERE pt_id = '223'
                 */

                $targetHostgroup_id = $this->_targetId('IcingaHostgroup', $mysqli);
                $mysqli->query("INSERT INTO nsm_principal_target (pt_principal_id, pt_target_id) VALUES ('$pt_principal_id', '$targetHostgroup_id')");
                $hostgroup_principal_id = $mysqli->insert_id;
                $mysqli->query("INSERT INTO nsm_target_value (tv_key, tv_val, tv_pt_id) VALUES ('hostgroup', '" . $this->getUserLogin() . "', '$hostgroup_principal_id')");
                $icingauser_id = $this->_targetId('icinga.user', $mysqli);
                $mysqli->query("INSERT INTO nsm_principal_target (pt_principal_id, pt_target_id) VALUES ('$pt_principal_id', '$icingauser_id')");
                $appkituserdummy_id = $this->_targetId('appkit.user.dummy', $mysqli);
                $mysqli->query("INSERT INTO nsm_principal_target (pt_principal_id, pt_target_id) VALUES ('$pt_principal_id', '$appkituserdummy_id')");


                $this->addStatusMessage(_('Uživatel založen i pro Icinga Web'), 'success');
            } else {
                $this->addStatusMessage(_('Uživatel nebyl založen i pro Icinga Web'), 'warning');
            }
            $mysqli->close();
        }

        return $result;
    }

    private function _getPrincipals($pt_principal_id, $mysqli)
    {
        $principals = array();
        $result = $mysqli->query("SELECT n.pt_id AS n__pt_id, n.pt_target_id AS n__pt_target_id FROM nsm_principal_target n WHERE (n.pt_principal_id IN ('$pt_principal_id'))");
        if ($result) {
            while ($row = $result->fetch_object()) {
                $principals[$row['n__pt_id']] = $row['n__pt_target_id'];
            }
        }
        return $principals;
    }

    private function _targetId($target_name, $mysqli)
    {
        $target_id = null;
        $result = $mysqli->query("SELECT n.target_id AS n__target_id FROM nsm_target n WHERE (n.target_name = '$target_name') LIMIT 1");
        if ($result) {
            $row = $result->fetch_object();
            if ($row) {
                $target_id = current($row);
            }
        }
        return $target_id;
    }

    private function _user_principalId($user_id, $mysqli)
    {
        $target_id = null;
        $result = $mysqli->query("SELECT n.principal_id AS n__principal_id FROM nsm_principal n WHERE (n.principal_user_id = '$user_id')");
        if ($result) {
            $row = $result->fetch_object();
            if ($row) {
                $target_id = current($row);
            }
        }
        return $target_id;
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
//        return new EaseJQConfirmedLinkButton('?user_id=' . $this->getID() . '&delete=true' . '&' . $urlAdd, _('Smazat ') . ' ' . $this->getUserLogin() . ' ' . EaseTWBPart::GlyphIcon('remove-sign'));

        EaseShared::webPage()->addItem(new IEConfirmationDialog('delete' . $this->getId(), '?user_id=' . $this->getID() . '&delete=true' . '&' . $urlAdd, _('Smazat') . ' ' . $name, sprintf(_('Opravdu smazat %s ?'), '<strong>' . $this->getUserName() . '</strong>')));
        return new EaseHtmlButtonTag(
            array(EaseTWBPart::GlyphIcon('remove'), _('Smazat') . ' ' . $this->keyword . ' ' . $this->getUserName()), array('style' => 'cursor: default', 'class' => 'btn btn-danger', 'id' => 'triggerdelete' . $this->getId(), 'data-id' => $this->getId()
        ));
    }

    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }

        if ($id != $this->getId()) {
            $this->loadFromMySQL($id);
        }


        $userGroup = new IEUserGroup;
        $userGroup->delUser($id);


        $command = new IECommand;
        $myCommand = $command->getOwned($id);
        if ($myCommand) {
            foreach ($myCommand as $command_id => $cmd) {
                $command->loadFromMySQL((int) $command_id);
                $command->delete();
            }
        }

        $contact = new IEContact;
        $myContact = $contact->getOwned($id);
        if ($myContact) {
            foreach ($myContact as $contact_id => $cmd) {
                if ($contact->loadFromMySQL((int) $contact_id)) {
                    $contact->delete();
                }
            }
        }


        $contactgroup = new IEContactgroup;
        $myContactgroup = $contactgroup->getOwned($id);
        if ($myContactgroup) {
            foreach ($myContactgroup as $contactgroup_id => $cmd) {
                $contactgroup->loadFromMySQL((int) $contactgroup_id);
                $contactgroup->delete();
            }
        }


        $hostgroup = new IEHostgroup;
        $myHostgroup = $hostgroup->getOwned($id);
        if ($myHostgroup) {
            foreach ($myHostgroup as $hostgroup_id => $cmd) {
                $hostgroup->loadFromMySQL((int) $hostgroup_id);
                $hostgroup->delete();
            }
        }

        $host = new IEHost;
        $myHost = $host->getOwned($id);
        if ($myHost) {
            foreach ($myHost as $host_id => $cmd) {
                $host->loadFromMySQL((int) $host_id);
                $host->delete();
            }
        }

        $servicegroup = new IEServicegroup;
        $myServicegroup = $servicegroup->getOwned($id);
        if ($myServicegroup) {
            foreach ($myServicegroup as $servicegroup_id => $cmd) {
                $servicegroup->loadFromMySQL((int) $servicegroup_id);
                $servicegroup->delete();
            }
        }

        $service = new IEService;
        $myService = $service->getOwned($id);
        if ($myService) {
            foreach ($myService as $service_id => $cmd) {
                $service->loadFromMySQL((int) $service_id);
                $service->delete();
            }
        }

        $timeperiod = new IETimeperiod;
        $myTimeperiod = $timeperiod->getOwned($id);
        if ($myTimeperiod) {
            foreach ($myTimeperiod as $timeperiod_id => $cmd) {
                $timeperiod->loadFromMySQL((int) $timeperiod_id);
                $timeperiod->delete();
            }
        }


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
