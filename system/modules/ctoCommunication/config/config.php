<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2012 
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */
/**
 * ctoCommunication Version
 */
$GLOBALS["CTOCOM_VERSION"] = "0.5.0";

/**
 * Maintenance 
 */
$GLOBALS['TL_CACHE']['ctoCom']='tl_ctocom_cache';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('CtoCommunication', 'checkExtensions');

/**
 * ctoCommunication engines
 */
$GLOBALS["CTOCOM_ENGINE"] = array(
    "empty"     => array(
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["empty"],
        "classname" => "CtoComCodifyengineImpl_Empty",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => FALSE
    ),
    "mcrypt"    => array(
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["mcrypt"],
        "classname" => "CtoComCodifyengineImpl_Mcrypt",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => FALSE
    ),
    "aeso"      => array(
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["phpseclib_aes_old"],
        "classname" => "CtoComCodifyengineImpl_AESO",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => TRUE
    ),
    "aes"       => array(
        "name"      => &$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']["phpseclib_aes"],
        "classname" => "CtoComCodifyengineImpl_AES",
        "folder"    => "system/modules/ctoCommunication",
        "invisible" => FALSE
    ),
);

/**
 * Register for RPC-Call functions
 * Base configuration and ctoCommunication RPC Calls
 */
$GLOBALS["CTOCOM_FUNCTIONS"] = array(
    //- Referer Functions --------
    "CTOCOM_REFERRER_DISABLE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "referrer_disable",
        "typ" => "GET",
        "parameter" => false,
    ),
    "CTOCOM_REFERRER_ENABLE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "referrer_enable",
        "typ" => "GET",
        "parameter" => false,
    ),
    //- Version Functions --------
    "CTOCOM_VERSION" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "getCtoComVersion",
        "typ" => "GET",
        "parameter" => false,
    ),
    "CONTAO_VERSION" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "getContaoVersion",
        "typ" => "GET",
        "parameter" => false,
    ),
    "CONTAO_FULL_VERSION" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "getContaoFullVersion",
        "typ" => "GET",
        "parameter" => false,
    ),
    "CTOCOM_GET_RESPONSE_PART" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "getResponsePart",
        "typ" => "POST",
        "parameter" => array("splitname", "splitcount"),
    ),
    "CTOCOM_HELLO" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "generateUUID",
        "typ" => "GET",
        "parameter" => FALSE,
    ),
    "CTOCOM_START_HANDSHAKE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "startHandshake",
        "typ" => "GET",
        "parameter" => FALSE,
    ),
    "CTOCOM_CHECK_HANDSHAKE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "checkHandshake",
        "typ" => "GET",
        "parameter" => FALSE,
    ),
    "CTOCOM_BYE" => array(
        "class" => "CtoComRPCFunctions",
        "function" => "deleteUUID",
        "typ" => "GET",
        "parameter" => FALSE,
    ),
);
?>