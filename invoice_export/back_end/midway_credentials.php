<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
$config = $INVEXP_CONFIG;
return [
    'db' => [
        'name' => 'M3',
        'user' => 'tmwin',
        'pass' => 'S0rdf1sh'
    ],
    'sftp' => [
        'test' => [
            'host' => 'b2b.sit.veraction.com',
            'port' => 8022,
            'user' => 'mdyt_ff_t',
            'key_path' => 'C:/midway keys/midway_private_test.key'
        ],
        'prod' => [
            'host' => 'b2b.veraction.com',
            'port' => 8022,
            'user' => 'mdyt_ff_p',
            'key_path' => 'C:/midway keys/midway_private_prod.key'
        ]
    ]
];
