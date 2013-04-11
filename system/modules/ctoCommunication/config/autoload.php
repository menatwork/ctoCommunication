<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package CtoCommunication
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'CtoComCodifyengineImpl_Mcrypt' => 'system/modules/ctoCommunication/CtoComCodifyengineImpl_Mcrypt.php',
	'CtoComContainerIO'             => 'system/modules/ctoCommunication/CtoComContainerIO.php',
	'CtoComCodifyengineImpl_AESO'   => 'system/modules/ctoCommunication/CtoComCodifyengineImpl_AESO.php',
	'CtoComCodifyengineFactory'     => 'system/modules/ctoCommunication/CtoComCodifyengineFactory.php',
	'CtoComDebug'                   => 'system/modules/ctoCommunication/CtoComDebug.php',
	'CtoComCodifyengineImpl_Empty'  => 'system/modules/ctoCommunication/CtoComCodifyengineImpl_Empty.php',
	'CtoComIOFactory'               => 'system/modules/ctoCommunication/CtoComIOFactory.php',
	'CtoComCodifyengineAbstract'    => 'system/modules/ctoCommunication/CtoComCodifyengineAbstract.php',
	'CtoComIOImpl_Default'          => 'system/modules/ctoCommunication/CtoComIOImpl_Default.php',
	'CtoComContainerError'          => 'system/modules/ctoCommunication/CtoComContainerError.php',
	'CtoComRPCFunctions'            => 'system/modules/ctoCommunication/CtoComRPCFunctions.php',
	'CtoComCodifyengineImpl_AES'    => 'system/modules/ctoCommunication/CtoComCodifyengineImpl_AES.php',
	'CtoComIOInterface'             => 'system/modules/ctoCommunication/CtoComIOInterface.php',
	'CtoCommunication'              => 'system/modules/ctoCommunication/CtoCommunication.php',
));