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
class IEContact extends IECfg
{

    public $myTable = 'contact';
    /**
     * Klíčový sloupeček
     * @var string 
     */
    public $MyKeyColumn = 'contact_id';
    public $NameColumn = 'contact_name';
    public $Keyword = 'contact';

    /**
     * Přidat položky register a use ?
     * @var boolean 
     */
    public $AllowTemplating = true;

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean 
     */
    public $PublicRecords = false;
    public $UseKeywords = array(
        'contact_name' => 'VARCHAR(128)',
        'alias' => 'VARCHAR()',
        'contactgroups' => 'VARCHAR(255)',
        'host_notifications_enabled' => 'BOOL',
        'service_notifications_enabled' => 'BOOL',
        'host_notification_period' => 'SELECT',
        'service_notification_period' => 'SELECT',
        'host_notification_options' => "FLAGS('d','u','r','f','s','n')",
        'service_notification_options' => "FLAGS('w','u','c','r','f','s','n')",
        'host_notification_commands' => 'IDLIST',
        'service_notification_commands' => 'IDLIST',
        'email' => 'VARCHAR(128)',
        'pager' => 'VARCHAR(64)',
        'address1' => 'VARCHAR(255)',
        'address2' => 'VARCHAR(255)',
        'can_submit_commands' => 'BOOL',
        'retain_status_information' => 'BOOL',
        'retain_nonstatus_information' => 'BOOL'
    );
    public $KeywordsInfo = array(
        'contact_name' => array('title' => 'název kontaktu', 'required' => true),
        'alias' => array(
            'title' => 'alias'
        ),
        'contactgroups' => array(
            'title' => 'kontaktní skupiny',
            'hidden' => true
        ),
        'host_notifications_enabled' => array(
            'title' => 'oznamovat zprávy hostů',
        ),
        'service_notifications_enabled' => array(
            'title' => 'oznamovat zprávy služeb',
        ),
        'host_notification_period' => array(
            'title' => 'notifikační perioda hostů',
            'required' => true,
            'refdata' => array(
                'table' => 'timeperiods',
                'captioncolumn' => 'timeperiod_name',
                'public' => true,
                'idcolumn' => 'timeperiod_id')
        ),
        'service_notification_period' => array(
            'title' => 'notifikační perioda služeb',
            'required' => true,
            'refdata' => array(
                'table' => 'timeperiods',
                'captioncolumn' => 'timeperiod_name',
                'public' => true,
                'idcolumn' => 'timeperiod_id')
        ),
        'host_notification_options' => array(
            'title' => 'možnosti oznamování hostů',
            'required' => true,
            'd' => 'notify on DOWN host states',
            'u' => 'notify on UNREACHABLE host states',
            'r' => 'notify on host recoveries (UP states)',
            'f' => 'notify when the host starts and stops flapping',
            's' => 'send notifications when host or service scheduled downtime starts and ends',
            'n' => 'nic neoznamovat'
        ),
        'service_notification_options' => array(
            'title' => 'možnosti oznamování služeb',
            'required' => true,
            'w' => 'notify on WARNING service states',
            'u' => 'notify on UNKNOWN service states',
            'c' => 'notify on CRITICAL service states',
            'r' => 'notify on service recoveries (OK states)',
            'f' => 'notify when the service starts and stops flapping',
            's' => 'send notifications when host or service scheduled downtime starts and ends',
            'n' => 'nic neoznamovat'
        ),
        'host_notification_commands' => array(
            'title' => 'způsob oznamování událostí hosta',
            'required' => true,
            'refdata' => array(
                'table' => 'command',
                'captioncolumn' => 'command_name',
                'idcolumn' => 'command_id',
                'public' => true,
                'condition' => array('command_type' => 'notify')
            )
        ),
        'service_notification_commands' => array(
            'title' => 'způsob oznamování událostí služby',
            'required' => true,
            'refdata' => array(
                'table' => 'command',
                'captioncolumn' => 'command_name',
                'idcolumn' => 'command_id',
                'public' => true,
                'condition' => array('command_type' => 'notify')
            )
        ),
        'email' => array(
            'title' => 'mailová adresa',
            'mandatory' => true
        ),
        'pager' => array(
            'title' => 'číslo pro příjem SMS',
            'mandatory' => true
        ),
        'address1' => array(
            'title' => 'jabberová adresa',
            'mandatory' => true
        ),
        'address2' => array(
            'title' => '@Twitter',
            'mandatory' => true
        ),
        'can_submit_commands' => array(
            'title' => 'Smí tento přihlášený kontakt zasílat externí příkazy ?'
        ),
        'retain_status_information' => array(
            'title' => ''
        ),
        'retain_nonstatus_information' => array(
            'title' => ''
        )
    );

    /**
     * URL dokumentace objektu
     * @var string 
     */
    public $DocumentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-contact';

    /**
     * Vrací mazací tlačítko
     * 
     * @param string $Name
     * @return \EaseJQConfirmedLinkButton 
     */
    function deleteButton($Name = null)
    {
        return parent::deleteButton(_('Kontakt'));
    }

    /**
     * vrací nastavení pro výchozí kontakt uživatele
     * 
     * @return  array Pole nastavení
     */
    public static function ownContactData()
    {
        $OUser = EaseShared::user();
        return array(
            'use' => 'generic-contact',
            'contact_name' => $OUser->getUserLogin(),
            'alias' => $OUser->getUserName(),
            'email' => $OUser->getUserEmail(),
            'host_notification_commands' => array('notify-host-by-email'),
            'service_notification_commands' => array('notify-service-by-email'),
            'generate' => TRUE,
            'user_id' => $OUser->getUserID()
        );
    }

}

?>
