<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace CtoCommunication\Codifyengine;

/**
 * Factory for create the codifyengine
 */
class Factory
{

    /**
     * Create the codifyengine.
     *
     * @return Base
     */
    public static function getEngine($engine = null)
    {
        // Use default codifyengine, if no one is set
        if ($engine == '' || $engine == null) {
            $engine = 'aes';
        }

        // Check if engine is known.
        if (!array_key_exists($engine, $GLOBALS['CTOCOM_ENGINE'])) {
            throw new \RuntimeException(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_engine'], array($engine)));
        }

        // Init the class.
        $arrEngine = $GLOBALS['CTOCOM_ENGINE'][$engine];
        $strClass  = $arrEngine['classname'];
        $objEngine = new $strClass();

        // Get engine
        if ($objEngine instanceof Base) {
            return $objEngine;
        } else {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['not_a_engine']);
        }
    }
}
