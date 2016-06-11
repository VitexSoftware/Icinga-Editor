<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEServiceTweaker
 *
 * @author vitex
 */
class ServiceTweaker extends \Ease\Html\Div
{
    /**
     * Objekt příkazu
     * @var IECommand
     */
    public $command = null;

    /**
     * Objekt služby
     * @var IEService
     */
    public $service = null;

    /**
     * Objekt Hosta
     * @var IEHost
     */
    public $host = null;

    /**
     *
     * @var type
     */
    public $configurator = null;

    /**
     * Umožňuje měnit parametry služeb
     *
     * @param IEService $service
     * @param IEHost    $host    ObjektHostu
     */
    public function __construct($service, $host)
    {
        parent::__construct();

        $this->service = $service;
        $this->host    = $host;

        $this->command = new \Icinga\Editor\Engine\IECommand();
        $this->command->setmyKeyColumn($this->command->nameColumn);

        $checkLocal = $this->service->getDataValue('check_command');
//        $checkRemote = $this->service->getDataValue('check_command-remote');

        if (isset($checkLocal)) {
            $this->command->loadFromSQL($checkLocal);
        }

        $configurator = $this->service->getDataValue('configurator');
        if ($configurator) {
            $module = 'modules/'.$configurator.'.inc.php';
            if (file_exists($module)) {
                require_once $module;
                $configurator       = '\\Icinga\Editor\\modules\\'.$configurator;
                $this->configurator = $this->addItem(new $configurator($this));
            } else {
                $this->addStatusMessage(sprintf(_('Modul %s nebyl nalezen'),
                        $module), 'error');
            }
        } else {
            $this->configurator = $this->addItem(new IEServiceConfigurator($this));
        }
    }

    /**
     * Pokus o zjistenu parametru prikazu
     */
    public function discoveryParams()
    {
        $cmdline = $this->command->getDataValue('command_line');

        $this->addItem('<br>'.$cmdline);

        $params = [];

        $parts    = array_reverse(explode(' ', $cmdline));
        $checkCmd = end($parts);
        foreach ($parts as $cmdPartID => $cmdPart) {
            if (strstr($cmdPart, 'ARG')) {
                $params[substr($cmdPart, 5, 1)] = $parts[$cmdPartID + 1][1];
            }
        }

        $handle = popen($checkCmd.' --help 2>&1', 'r');
        $help   = '';
        while (!feof($handle)) {
            $help .= fread($handle, 2096);
        }
        pclose($handle);

        $helplines = explode("\n", $help);
        $options   = [];
        foreach ($helplines as $hip => $helpline) {
            $helpline = trim($helpline);
            if (strlen($helpline) && ($helpline[0] == '-')) {
                $options[$helpline[1]] = trim($helplines[$hip + 1]);
            }
        }

        $this->addItem($this->printPre($params));
        $this->addItem($this->printPre($options));

        $twform = new \Ease\TWB\Form('servtweak');

        foreach ($params as $id => $key) {
            if (isset($options[$key])) {
                $twform->addItem(new \Ease\TWB\FormGroup('arg'.$id,
                    new \Ease\Html\InputTextTag($options[$key], ''), null,
                    $options[$key]));
            }
        }
        $this->addItem($twform);
    }
}