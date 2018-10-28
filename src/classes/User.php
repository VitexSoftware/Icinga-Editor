<?php

namespace Icinga\Editor;

/**
 * Icinga Editor user
 */
class User extends \Ease\User
{
    /**
     * Tabulka uživatelů
     * @var string
     */
    public $myTable = 'user';

    /**
     * Insert date Column
     * @var string
     */
    public $myCreateColumn = 'DatCreate';

    /**
     * Modify date column
     * @var string
     */
    public $myLastModifiedColumn = 'DatSave';

    /**
     * Serialized settings column
     * @var string
     */
    public $settingsColumn = 'settings';

    /**
     * Keyword
     * @var string
     */
    public $keyword = 'user';

    /**
     * Obtain link to Icon
     *
     * @return string
     */
    public function getIcon()
    {
        $icon = $this->GetSettingValue('icon');
        if (is_null($icon)) {
            return parent::getIcon();
        } else {
            return $icon;
        }
    }

    /**
     * Way how to set Admin's flag using Yes/No switch
     * 
     * @param array $data
     * @return array
     */
    public function takeData($data)
    {
        foreach ($data as $key => $value) {
            if (strstr($key, 'admin-')) {
                $this->setSettingValue('admin',
                    ($value === 'true') ? true : false );
                unset($data[$key]);
            }
        }
        return parent::takeData($data);
    }

    /**
     * Obtain first contact for user
     */
    public function getFirstContact()
    {
        $contact = new Engine\Contact();
        $cn      = $contact->getColumnsFromSQL([$contact->nameColumn, $contact->keyColumn],
            [$contact->userColumn => $this->getUserID(), 'parent_id' => 'IS NOT NULL'],
            $contact->keyColumn, $contact->nameColumn, 1);
        if (count($cn)) {
            $curcnt = current($cn);

            return [$curcnt[$contact->keyColumn] => $curcnt[$contact->nameColumn]];
        }

        return null;
    }

    /**
     * Obtain default user contact
     */
    public function getDefaultContact()
    {
        return new Engine\Contact($this->getDataValue($this->loginColumn).' email');
    }

    /**
     * Obtain actual record ID
     * @return int
     */
    public function getId()
    {
        return (int) $this->getMyKey();
    }

    /**
     * Change user password
     * * In icinga Editor
     * * In icinga cgi
     * * In IcingaWeb
     *
     * @param string $newPassword new password
     * @param int    $userID      User ID
     *
     * @return boolean password change result
     */
    public function passwordChange($newPassword, $userID = null)
    {
        if (parent::passwordChange($newPassword, $userID)) {

            system('sudo htpasswd -b /etc/icinga/htpasswd.users '.$this->getUserLogin().' '.$newPassword);
            if (defined('DB_IW_SERVER_PASSWORD')) {
                $mysqli = new \mysqli(DB_SERVER_HOST, DB_IW_SERVER_USERNAME,
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
                    $this->addStatusMessage(_('Password for Icinga Web also changed'),
                        'success');
                } else {
                    $this->addStatusMessage(_('Password for icinga change error'),
                        'warning');
                }
                $mysqli->close();
            }
            return true;
        }

        return false;
    }

    /**
     * Create user
     * * for Icinga Editor
     * * for Icinga Cgi
     * * for IcingaWeb
     *
     * @param array $data
     * @return int new user id
     */
    function insertToSQL($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        $result = parent::insertToSQL($data);

        if (defined('DB_IW_SERVER_PASSWORD')) {
            $mysqli = new \mysqli(DB_SERVER_HOST, DB_IW_SERVER_USERNAME,
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


                $this->addStatusMessage(_('Icinga Web user also created'),
                    'success');
            } else {
                $this->addStatusMessage(_('Icinga Web user not created'),
                    'warning');
            }
            $mysqli->close();
        }

        return $result;
    }

    /**
     * Obtain Icinga Web principials
     *
     * @param int $pt_principal_id
     * @param resource $mysqli
     * @return array
     */
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
     * Obtain user delete button
     *
     * @param  string                     $name   User Name
     * @param  string                     $urlAdd URL part to add
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $urlAdd = '')
    {

        \Ease\Shared::webPage()->addItem(new UI\ConfirmationDialog('delete'.$this->getId(),
                '?user_id='.$this->getID().'&delete=true'.'&'.$urlAdd,
                _('Delete').' '.$name,
                sprintf(_('Are you sure to delete %s ?'),
                    '<strong>'.$this->getUserName().'</strong>')));
        return new \Ease\Html\ButtonTag(
            [\Ease\TWB\Part::GlyphIcon('remove'), _('Delete').' '.$this->keyword.' '.$this->getUserName()],
            ['style' => 'cursor: default', 'class' => 'btn btn-danger', 'id' => 'triggerdelete'.$this->getId(),
            'data-id' => $this->getId()
        ]);
    }

    /**
     * Delete User from database
     *
     * @param int $id
     * @return boolean
     */
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
                $this->addStatusMessage(sprintf(_('Configuration for %s was deleted'),
                        $this->getUserLogin()), 'success');
            } else {
                $this->addStatusMessage(sprintf(_('Confinguration for %s was not deleted'),
                        $this->getUserLogin()), 'error');
            }
        }

        if ($this->deleteFromSQL($this->getUserID())) {

            $this->addStatusMessage(sprintf(_('User %s was deleted'),
                    $this->getUserLogin()));

            $email = new \Ease\Mailer($this->getDataValue('email'),
                _('Account canceled'));
            $email->setMailHeaders(['From' => EMAIL_FROM]);
            $email->addItem(new \Ease\Html\DivTag(_("You were removed from IcingaEditor:")."\n"));
            $email->addItem(new \Ease\Html\DivTag(' Login: '.$this->GetUserLogin()."\n"));

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
     * Obtain Column type helper
     *
     * @param  string $columnName
     * @return string
     */
    function getColumnType($columnName)
    {
        return 'string';
    }

    /**
     * Is user privileged ?
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->getSettingValue('admin') === true;
    }
}
