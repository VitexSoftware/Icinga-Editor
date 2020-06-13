<?php

/**
 * Icinga Editor - Phinx Adapter
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2018 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor;

include_once './vendor/autoload.php';

\Ease\Shared::instanced()->loadConfig('config.json', true);


$engine = new \Ease\SQL\Engine(null);

$cfg = [
    'paths' => [
        'migrations' => ['db/migrations'],
        'seeds' => ['db/seeds']
    ],
    'environments' =>
    [
        'default_database' => 'development',
        'development' => [
            'adapter' => \Ease\Shared::instanced()->getConfigValue('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo()
        ],
        'default_database' => 'production',
        'production' => [
            'adapter' => \Ease\Shared::instanced()->getConfigValue('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo()
        ],
    ]
];

return $cfg;
