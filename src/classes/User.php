<?php

namespace Icinga\Editor;

/**
 * Uživatel Icinga Editoru
 */
class User extends \Ease\User
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
        $contact = new Engine\Contact();
        $cn      = $contact->getColumnsFromSQL([$contact->nameColumn, $contact->myKeyColumn],
            [$contact->userColumn => $this->getUserID(), 'parent_id' => 'IS NOT NULL'],
            $contact->myKeyColumn, $contact->nameColumn, 1);
        if (count($cn)) {
            $curcnt = current($cn);

            return [$curcnt[$contact->myKeyColumn] => $curcnt[$contact->nameColumn]];
        }

        return null;
    }

    /**
     * Vrací výchozí kontakt uživatele
     */
    public function getDefaultContact()
    {
        return new Engine\Contact($this->getDataValue($this->loginColumn).' email');
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

            system('sudo htpasswd -b /etc/icinga/htpasswd.users '.$this->getUserLogin().' '.$newPassword);
            if (defined('DB_IW_SERVER_PASSWORD')) {
                $mysqli = new mysqli(DB_SERVER, DB_IW_SERVER_USERNAME,
                    DB_IW_SERVER_PASSWORD, DB_IW_DATABASE);
                if ($mysqli->connect_errno) {
                    $this->addStatusMessage("Failed to connect to MySQL: (".$mysqli->connect_errno.") ".$mysqli->connect_error,
                        'error');
                }

                $salt      = hash("sha256",
                    uniqid($this->getUserLogin().'_', mt_rand()));
                $pwhash    = hash_hmac("sha256", $newPassword, $salt);
                $pwchquery = "UPDATE nsm_user SET user_password='".$this->dblink->addSlashes($pwhash)."', user_salt = '".$this->dblink->addSlashes($salt)."', user_modified = NOW() WHERE user_name = '".$this->getUserLogin()."';";

                if ($mysqli->query($pwchquery)) {
                    $this->addStatusMessage(_('Heslo bylo nastaveno i pro Icinga Web'),
                        'success');
                } else {
                    $this->addStatusMessage(_('Heslo bylo nastaveno i pro Icinga Web'),
                        'warning');
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
    function insertToSQL($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        $result = parent::insertToSQL($data);

        if (defined('DB_IW_SERVER_PASSWORD')) {
            $mysqli = new mysqli(DB_SERVER, DB_IW_SERVER_USERNAME,
                DB_IW_SERVER_PASSWORD, DB_IW_DATABASE);
            if ($mysqli->connect_errno) {
                $this->addStatusMessage("Failed to connect to MySQL: (".$mysqli->connect_errno.") ".$mysqli->connect_error,
                    'error');
            }

            $nuquery = "INSERT INTO nsm_user "
                ."(user_account, user_authsrc, user_disabled, user_name,  user_lastname, user_firstname, user_email,       user_password, user_salt, user_description, user_created, user_modified) VALUES "
                ."('0',         'internal',   '0',           '".$data[$this->loginColumn]."', '".$data['firstname']."',    '".$data['lastname']."',     '".$data['email']."', '1',           '1',       '".$data[$this->loginColumn]."',       NOW(),         NOW())";

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

                $targetHostgroup_id     = $this->_targetId('IcingaHostgroup',
                    $mysqli);
                $mysqli->query("INSERT INTO nsm_principal_target (pt_principal_id, pt_target_id) VALUES ('$pt_principal_id', '$targetHostgroup_id')");
                $hostgroup_principal_id = $mysqli->insert_id;
                $mysqli->query("INSERT INTO nsm_target_value (tv_key, tv_val, tv_pt_id) VALUES ('hostgroup', '".$this->getUserLogin()."', '$hostgroup_principal_id')");
                $icingauser_id          = $this->_targetId('icinga.user',
                    $mysqli);
                $mysqli->query("INSERT INTO nsm_principal_target (pt_principal_id, pt_target_id) VALUES ('$pt_principal_id', '$icingauser_id')");
                $appkituserdummy_id     = $this->_targetId('appkit.user.dummy',
                    $mysqli);
                $mysqli->query("INSERT INTO nsm_principal_target (pt_principal_id, pt_target_id) VALUES ('$pt_principal_id', '$appkituserdummy_id')");


                $this->addStatusMessage(_('Uživatel založen i pro Icinga Web'),
                    'success');
            } else {
                $this->addStatusMessage(_('Uživatel nebyl založen i pro Icinga Web'),
                    'warning');
            }
            $mysqli->close();
        }

        return $result;
    }

    private function _getPrincipals($pt_principal_id, $mysqli)
    {
        $principals = [];
        $result     = $mysqli->query("SELECT n.pt_id AS n__pt_id, n.pt_target_id AS n__pt_target_id FROM nsm_principal_target n WHERE (n.pt_principal_id IN ('$pt_principal_id'))");
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
        $result    = $mysqli->query("SELECT n.target_id AS n__target_id FROM nsm_target n WHERE (n.target_name = '$target_name') LIMIT 1");
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
        $result    = $mysqli->query("SELECT n.principal_id AS n__principal_id FROM nsm_principal n WHERE (n.principal_user_id = '$user_id')");
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
//        return new EaseJQConfirmedLinkButton('?user_id=' . $this->getID() . '&delete=true' . '&' . $urlAdd, _('Smazat ') . ' ' . $this->getUserLogin() . ' ' . \Ease\TWB\Part::GlyphIcon('remove-sign'));

        \Ease\Shared::webPage()->addItem(new UI\ConfirmationDialog('delete'.$this->getId(),
            '?user_id='.$this->getID().'&delete=true'.'&'.$urlAdd,
            _('Smazat').' '.$name,
            sprintf(_('Opravdu smazat %s ?'),
                '<strong>'.$this->getUserName().'</strong>')));
        return new \Ease\Html\ButtonTag(
            [\Ease\TWB\Part::GlyphIcon('remove'), _('Smazat').' '.$this->keyword.' '.$this->getUserName()],
            ['style' => 'cursor: default', 'class' => 'btn btn-danger', 'id' => 'triggerdelete'.$this->getId(),
            'data-id' => $this->getId()
        ]);
    }

    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }

        if ($id != $this->getId()) {
            $this->loadFromSQL($id);
        }


        $userGroup = new Engine\UserGroup();
        $userGroup->delUser($id);


        $command   = new Engine\Command();
        $myCommand = $command->getOwned($id);
        if ($myCommand) {
            foreach ($myCommand as $command_id => $cmd) {
                $command->loadFromSQL((int) $command_id);
                $command->delete();
            }
        }

        $contact   = new Engine\Contact();
        $myContact = $contact->getOwned($id);
        if ($myContact) {
            foreach ($myContact as $contact_id => $cmd) {
                if ($contact->loadFromSQL((int) $contact_id)) {
                    $contact->delete();
                }
            }
        }


        $contactgroup   = new Engine\Contactgroup();
        $myContactgroup = $contactgroup->getOwned($id);
        if ($myContactgroup) {
            foreach ($myContactgroup as $contactgroup_id => $cmd) {
                $contactgroup->loadFromSQL((int) $contactgroup_id);
                $contactgroup->delete();
            }
        }


        $hostgroup   = new Engine\Hostgroup();
        $myHostgroup = $hostgroup->getOwned($id);
        if ($myHostgroup) {
            foreach ($myHostgroup as $hostgroup_id => $cmd) {
                $hostgroup->loadFromSQL((int) $hostgroup_id);
                $hostgroup->delete();
            }
        }

        $host   = new Engine\Host();
        $myHost = $host->getOwned($id);
        if ($myHost) {
            foreach ($myHost as $host_id => $cmd) {
                $host->loadFromSQL((int) $host_id);
                $host->delete();
            }
        }

        $servicegroup   = new Engine\Servicegroup();
        $myServicegroup = $servicegroup->getOwned($id);
        if ($myServicegroup) {
            foreach ($myServicegroup as $servicegroup_id => $cmd) {
                $servicegroup->loadFromSQL((int) $servicegroup_id);
                $servicegroup->delete();
            }
        }

        $service   = new Engine\Service();
        $myService = $service->getOwned($id);
        if ($myService) {
            foreach ($myService as $service_id => $cmd) {
                $service->loadFromSQL((int) $service_id);
                $service->delete();
            }
        }

        $timeperiod   = new Engine\Timeperiod();
        $myTimeperiod = $timeperiod->getOwned($id);
        if ($myTimeperiod) {
            foreach ($myTimeperiod as $timeperiod_id => $cmd) {
                $timeperiod->loadFromSQL((int) $timeperiod_id);
                $timeperiod->delete();
            }
        }


        $cfgfile = constant('CFG_GENERATED').'/'.$this->getUserLogin().'.cfg';
        if (file_exists($cfgfile)) {
            if (unlink($cfgfile)) {
                $this->addStatusMessage(sprintf(_('Konfigurace uživatele %s byla smazána'),
                        $this->getUserLogin()), 'success');
            } else {
                $this->addStatusMessage(sprintf(_('Konfigurace uživatele %s nebyla smazána'),
                        $this->getUserLogin()), 'error');
            }
        }

        if ($this->deleteFromSQL($this->getUserID())) {

            $this->addStatusMessage(sprintf(_('Uživatel %s byl smazán'),
                    $this->getUserLogin()));

            $email = new \Ease\Mailer($this->getDataValue('email'),
                _('Oznámení o zrušení účtu'));
            $email->setMailHeaders(['From' => EMAIL_FROM]);
            $email->addItem(new \Ease\Html\Div(_("Právě jste byl/a smazán/a z Aplikace VSMonitoring s těmito přihlašovacími údaji:")."\n"));
            $email->addItem(new \Ease\Html\Div(' Login: '.$this->GetUserLogin()."\n"));

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
        $longname = trim($this->getDataValue('firstname').' '.$this->getDataValue('lastname'));
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

    /**
     * Vrací typ sloupečku
     *
     * @param  string $columnName
     * @return string
     */
    function getColumnType($columnName)
    {
        return 'string';
    }
}