<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'CtoCommunication',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Runonce
	'runonce'                                           => 'system/modules/ctoCommunication/runonce/runonce.php',

	// Src
	'CtoCommunication\Container\Connection'             => 'system/modules/ctoCommunication/src/CtoCommunication/Container/Connection.php',
	'CtoCommunication\Container\IO'                     => 'system/modules/ctoCommunication/src/CtoCommunication/Container/IO.php',
	'CtoCommunication\Container\Error'                  => 'system/modules/ctoCommunication/src/CtoCommunication/Container/Error.php',
	'CtoCommunication\Codifyengine\Mcrypt'              => 'system/modules/ctoCommunication/src/CtoCommunication/Codifyengine/Mcrypt.php',
	'CtoCommunication\Codifyengine\NoneCrypt'           => 'system/modules/ctoCommunication/src/CtoCommunication/Codifyengine/NoneCrypt.php',
	'CtoCommunication\Codifyengine\Base'                => 'system/modules/ctoCommunication/src/CtoCommunication/Codifyengine/Base.php',
	'CtoCommunication\Codifyengine\AES'                 => 'system/modules/ctoCommunication/src/CtoCommunication/Codifyengine/AES.php',
	'CtoCommunication\Codifyengine\Factory'             => 'system/modules/ctoCommunication/src/CtoCommunication/Codifyengine/Factory.php',
	'CtoCommunication\InputOutput\InterfaceInputOutput' => 'system/modules/ctoCommunication/src/CtoCommunication/InputOutput/InterfaceInputOutput.php',
	'CtoCommunication\InputOutput\Base'                 => 'system/modules/ctoCommunication/src/CtoCommunication/InputOutput/Base.php',
	'CtoCommunication\InputOutput\Factory'              => 'system/modules/ctoCommunication/src/CtoCommunication/InputOutput/Factory.php',
	'CtoCommunication\RPC\CoreFunctions'                => 'system/modules/ctoCommunication/src/CtoCommunication/RPC/CoreFunctions.php',
	'CtoCommunication\Helper\Config'                    => 'system/modules/ctoCommunication/src/CtoCommunication/Helper/Config.php',
	'CtoCommunication\Helper\Debug'                     => 'system/modules/ctoCommunication/src/CtoCommunication/Helper/Debug.php',
	'CtoCommunication\Serivces\Client'                  => 'system/modules/ctoCommunication/src/CtoCommunication/Serivces/Client.php',
	'CtoCommunication\Serivces\Base'                    => 'system/modules/ctoCommunication/src/CtoCommunication/Serivces/Base.php',
	'CtoCommunication\Serivces\Server'                  => 'system/modules/ctoCommunication/src/CtoCommunication/Serivces/Server.php',
));
