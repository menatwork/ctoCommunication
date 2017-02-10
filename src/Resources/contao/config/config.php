<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * ctoCommunication version
 */
$GLOBALS['CTOCOM_VERSION'] = '2.0.0';

/**
 * Maintenance
 */
$GLOBALS['TL_CACHE']['ctoCom'] = 'tl_ctocom_cache';

/**
 * Blacklists tables for syncCto
 */
$GLOBALS['SYC_CONFIG']['table_hidden'] = array_merge((array)$GLOBALS['SYC_CONFIG']['table_hidden'], array
(
    'tl_ctocom_cache',
    'tl_requestcache',
));

/**
 * ctoCommunication engines
 */
$GLOBALS['CTOCOM_ENGINE'] = array
(
    'empty'  => array
    (
        'name'      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['empty'],
        'classname' => '\CtoCommunication\Codifyengine\NoneCrypt',
        'invisible' => false
    ),
    'mcrypt' => array
    (
        'name'      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['mcrypt'],
        'classname' => '\CtoCommunication\Codifyengine\Mcrypt',
        'invisible' => false
    ),
    'aes'    => array
    (
        'name'      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['phpseclib_aes'],
        'classname' => '\CtoCommunication\Codifyengine\AES',
        'invisible' => false
    ),
);

$GLOBALS['CTOCOM_IO'] = array
(
    'default' => array
    (
        'accept'      => array('text/html', 'text/plain', '*/*'),
        'contentType' => 'text/html',
        'classname'   => '\CtoCommunication\InputOutput\Base',
    )
);

/**
 * Register for RPC-Call functions
 * Base configuration and ctoCommunication RPC Calls
 */
$GLOBALS['CTOCOM_FUNCTIONS'] = array
(
    //- Referer Functions --------
    'CTOCOM_REFERRER_DISABLE'  => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'referrer_disable',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CTOCOM_REFERRER_ENABLE'   => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'referrer_enable',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    //- Version Functions --------
    'CTOCOM_VERSION'           => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'getCtoComVersion',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CONTAO_VERSION'           => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'getContaoVersion',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CONTAO_FULL_VERSION'      => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'getContaoFullVersion',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CTOCOM_GET_RESPONSE_PART' => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'getResponsePart',
        'typ'       => 'POST',
        'parameter' => array('splitname', 'splitcount'),
    ),
    'CTOCOM_HELLO'             => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'generateUUID',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CTOCOM_START_HANDSHAKE'   => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'startHandshake',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CTOCOM_CHECK_HANDSHAKE'   => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'checkHandshake',
        'typ'       => 'GET',
        'parameter' => false,
    ),
    'CTOCOM_BYE'               => array
    (
        'class'     => '\CtoCommunication\RPC\CoreFunctions',
        'function'  => 'deleteUUID',
        'typ'       => 'GET',
        'parameter' => false,
    ),
);
