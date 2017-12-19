<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\CtoCommunicationBundle\InputOutput;

/**
 * Factory for create the codifyengine
 */
class Factory
{

    /**
     * Create the codifyengine.
     *
     * @return InterfaceInputOutput
     */
    public static function getEngine($strEngine)
    {
        // Check if engine is known
        if (!array_key_exists($strEngine, $GLOBALS['CTOCOM_IO'])) {
            throw new \RuntimeException(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_engine'], array($strEngine)));
        }

        $arrEngine  = $GLOBALS['CTOCOM_IO'][$strEngine];
        $strClass   = $arrEngine['classname'];
        $objEnginge = new $strClass();

        // Get engine
        if ($objEnginge instanceof InterfaceInputOutput) {
            return $objEnginge;
        } else {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['not_a_engine']);
        }
    }

    /**
     * Check if a given I/O engine exist
     *
     * @param string $strName Name of the engine
     *
     * @return boolean [True|False]
     */
    public static function engineExist($strName)
    {
        foreach ($GLOBALS['CTOCOM_IO'] as $keyIO => $valueIO) {
            if ($strName == $keyIO) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param string $strAccept
     *
     * @return String
     */
    public static function getEngingenameForAccept($strAccept)
    {
        foreach ($GLOBALS['CTOCOM_IO'] as $keyIO => $valueIO) {
            if (in_array($strAccept, $valueIO['accept'])) {
                return $keyIO;
            }
        }

        return false;
    }

    /**
     *
     * @param string $strAccept
     *
     * @return InterfaceInputOutput
     */
    public static function getEngingeForAccept($strAccept)
    {
        foreach ($GLOBALS['CTOCOM_IO'] as $keyIO => $valueIO) {
            if (in_array($strAccept, $valueIO['accept'])) {
                return self::getEngine($keyIO);
            }
        }

        return false;
    }

    /**
     *
     * @param string $strContentType
     *
     * @return String
     */
    public static function getEngingenameForContentType($strContentType)
    {
        foreach ($GLOBALS['CTOCOM_IO'] as $keyIO => $valueIO) {
            if ($strContentType == $valueIO['contentType']) {
                return $keyIO;
            }
        }

        return false;
    }

    /**
     *
     * @param string $strContentType
     *
     * @return InterfaceInputOutput
     */
    public static function getEngingeForContentType($strContentType)
    {
        foreach ($GLOBALS['CTOCOM_IO'] as $keyIO => $valueIO) {
            if ($strContentType == $valueIO['contentType']) {
                return self::getEngine($keyIO);
            }
        }

        return false;
    }

    /**
     *
     * @param string $strContentType
     *
     * @return InterfaceInputOutput
     */
    public static function getEngingeByName($strName)
    {
        foreach ($GLOBALS['CTOCOM_IO'] as $keyIO => $valueIO) {
            if ($strName == $keyIO) {
                return self::getEngine($keyIO);
            }
        }

        return false;
    }

}
