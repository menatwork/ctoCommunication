<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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

?>