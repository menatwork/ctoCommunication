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
$GLOBALS["CTOCOM_VERSION"] = "1.2.3";

/**
 * Maintenance 
 */
$GLOBALS['TL_CACHE']['ctoCom'] = 'tl_ctocom_cache';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('CtoCommunication', 'checkExtensions');

/**
 * Blacklists tables for syncCto
 */
$GLOBALS['SYC_CONFIG']['table_hidden'] = array_merge( (array) $GLOBALS['SYC_CONFIG']['table_hidden'], array
(
    'tl_ctocom_cache',
    'tl_requestcache',
));

/**
 * ctoCommunication engines
 */
$GLOBALS["CTOCOM_ENGINE"] = array
(
    "empty" => array
    (
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["empty"],
        "classname" => "CtoComCodifyengineImpl_Empty",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => FALSE
    ),
    "mcrypt" => array
    (
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["mcrypt"],
        "classname" => "CtoComCodifyengineImpl_Mcrypt",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => FALSE
    ),
    "aeso" => array
    (
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["phpseclib_aes_old"],
        "classname" => "CtoComCodifyengineImpl_AESO",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => TRUE
    ),
    "aes" => array
    (
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["phpseclib_aes"],
        "classname" => "CtoComCodifyengineImpl_AES",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => FALSE
    ),
);

$GLOBALS["CTOCOM_IO"] = array
(
    "default" => array
    (
        "accept"        => array("text/html", "text/plain", "*/*"),
        "contentType"   => "text/html",
        "classname"     => "CtoComIOImpl_Default",
        "folder"        => "system/modules/ctoCommunication",
    )
);

/**
 * Register for RPC-Call functions
 * Base configuration and ctoCommunication RPC Calls
 */
$GLOBALS["CTOCOM_FUNCTIONS"] = array
(
    //- Referer Functions --------
    "CTOCOM_REFERRER_DISABLE" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "referrer_disable",
        "typ"              => "GET",
        "parameter"        => false,
    ),
    "CTOCOM_REFERRER_ENABLE" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "referrer_enable",
        "typ"              => "GET",
        "parameter"        => false,
    ),
    //- Version Functions --------
    "CTOCOM_VERSION" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "getCtoComVersion",
        "typ"              => "GET",
        "parameter"        => false,
    ),
    "CONTAO_VERSION" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "getContaoVersion",
        "typ"              => "GET",
        "parameter"        => false,
    ),
    "CONTAO_FULL_VERSION" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "getContaoFullVersion",
        "typ"              => "GET",
        "parameter"        => false,
    ),
    "CTOCOM_GET_RESPONSE_PART" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "getResponsePart",
        "typ"              => "POST",
        "parameter"        => array("splitname", "splitcount"),
    ),
    "CTOCOM_HELLO" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "generateUUID",
        "typ"              => "GET",
        "parameter"        => FALSE,
    ),
    "CTOCOM_START_HANDSHAKE" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "startHandshake",
        "typ"              => "GET",
        "parameter"        => FALSE,
    ),
    "CTOCOM_CHECK_HANDSHAKE" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "checkHandshake",
        "typ"              => "GET",
        "parameter"        => FALSE,
    ),
    "CTOCOM_BYE" => array
    (
        "class"            => "CtoComRPCFunctions",
        "function"         => "deleteUUID",
        "typ"              => "GET",
        "parameter"        => FALSE,
    ),
);