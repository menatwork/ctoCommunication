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
 * Initialize the system
 */
define('TL_MODE', 'BE');
require('system/initialize.php');

$communication = new \CtoCommunication\Serivces\Client();
$communication->run();