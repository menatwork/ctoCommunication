<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
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
$GLOBALS['TL_LANG']['CTOCOM']['codifyengine']['phpseclib_aes_old']  = 'phpseclib (AES - Veraltet)';

/**
 * Error
 */
$GLOBALS['TL_LANG']['ERR']['unknown_engine']                        = 'Unbekannte Verschlüsselungs-Engine: %s.';
$GLOBALS['TL_LANG']['ERR']['missing_engine']                        = 'Folgende Verschlüsselungs-Engine konnte nicht gefunden werden: %s.';
$GLOBALS['TL_LANG']['ERR']['not_a_engine']                          = 'Die ausgewählte Verschlüsselungs-Engine ist nicht von der Standard-Engine abgeleitet.';
$GLOBALS['TL_LANG']['ERR']['client_error']                          = 'Der Client meldet folgenden Fehler';

?>