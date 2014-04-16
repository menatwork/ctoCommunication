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
 * Engine
 */
$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['empty']              = '-';
$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['mcrypt']             = 'Mcrypt';
$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['phpseclib_aes']      = 'phpseclib (AES)';
$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['phpseclib_aes_old']  = 'phpseclib (AES - Deprecated)';

/**
 * Error
 */
$GLOBALS['TL_LANG']['ERR']['unknown_engine']                        = 'Unknown encryption engine: %s.';
$GLOBALS['TL_LANG']['ERR']['missing_engine']                        = 'Could not find the following encryption engine: %s.';
$GLOBALS['TL_LANG']['ERR']['not_a_engine']                          = 'The selected encryption engine is not derived from the standard engine.';
$GLOBALS['TL_LANG']['ERR']['client_error']                          = 'The client reports the following error';