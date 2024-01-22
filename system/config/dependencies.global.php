<?php

return [
    'dependencies' => [
        'factories' => [
            Johncms\Api\ConfigInterface::class      => Johncms\ConfigFactory::class,
            Johncms\Api\EnvironmentInterface::class => Johncms\Environment::class,
            Johncms\Api\ToolsInterface::class       => Johncms\Tools::class,
            Johncms\Api\UserInterface::class        => Johncms\UserFactory::class,
            PDO::class                              => Johncms\PdoFactory::class,

            'counters' => Johncms\Counters::class,
        ],
    ],
];
