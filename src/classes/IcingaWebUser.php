<?php

namespace Icinga\Editor;

/**
 * Description of IcingaWebUser
 *
 * @author vitex
 */
class IcingaWebUser
{
    /**
     * Where to get IcingaWeb sources
     * @var string
     */
    public $icingaWebDir = '/usr/share/icinga-web';

    /**
     * IcingaWeb user wrapper
     */
    public function __construct()
    {
        if (file_exists($this->icingaWebDir.'/app/config.php')) {
            require ($this->icingaWebDir.'/lib/agavi/src/agavi.php');
            require ($this->icingaWebDir.'/app/config.php');

            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Exception.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Locator/Injectable.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Access.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Record/Abstract.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Record.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Record/Iterator.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Null.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Core.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Configurable.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Manager/Exception.php';
            require_once $this->icingaWebDir.'/lib/doctrine/lib/Doctrine/Manager.php';

            require_once $this->icingaWebDir.'/app/modules/AppKit/lib/database/models/generated/BaseNsmUser.php';
            require_once $this->icingaWebDir.'/app/modules/AppKit/lib/database/models/NsmUser.php';

            Agavi::bootstrap('production');
            AgaviConfig::set('core.default_context', 'web');
            AgaviConfig::set('core.context_implementation', 'AppKitAgaviContext');
            AgaviContext::getInstance('web')->getController()->dispatch();

            $icingWebUser = new NsmUser('nsm_user');
//                $icingWebUser->
            $icingWebUser->updatePassword($newPassword);
        }
    }
}
