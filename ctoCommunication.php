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
define('TL_MODE', 'CTO_BE');
require('system/initialize.php');
require_once 'system/modules/ctoCommunication/CtoCommunication.php';

$communication = CtoCommunication::getInstance();
$communication->runClient();

?>