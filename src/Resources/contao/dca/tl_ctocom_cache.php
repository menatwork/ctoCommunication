<?php


/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ctocom_cache'] = array
(
    'config'     => [
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'     => 'primary',
                'tstamp' => 'index',
                'uid'    => 'index',
            ]
        ]
    ],
    'list'       => [],
    'operations' => [],
    'fields'     => [
        'id'                => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp'            => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\''
        ],
        'uid'               => [
            'sql' => 'varchar(255) NOT NULL default \'\''
        ],
        'prime'             => [
            'sql' => 'text NULL'
        ],
        'generator'         => [
            'sql' => 'text NULL'
        ],
        'public_key'        => [
            'sql' => 'text NULL'
        ],
        'private_key'       => [
            'sql' => 'text NULL'
        ],
        'shared_secret_key' => [
            'sql' => 'text NULL'
        ],
    ]
);
