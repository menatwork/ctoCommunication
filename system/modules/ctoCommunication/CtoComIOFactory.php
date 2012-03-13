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
 * Factory for create the codifyengine
 */
class CtoComIOFactory extends Backend
{

    /**
     * Create the codifyengine.
     * 
     * @return CtoComIOInterface 
     */
    public static function getEngine($strEngine)
    {
        // Check if engeni is known
        if (!key_exists($strEngine, $GLOBALS["CTOCOM_IO"]))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_engine'], array($strEngine)));
        }

        $arrEngine = $GLOBALS["CTOCOM_IO"][$strEngine];

        // Check if engine exists in filesystem
        if (!file_exists(TL_ROOT . "/" . $arrEngine["folder"] . "/" . $arrEngine["classname"] . ".php"))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['missing_engine'], array($arrEngine["classname"] . ".php")));
        }

        $strClass = $arrEngine["classname"];
        $objEnginge = new $strClass();

        // Get engine
        if ($objEnginge instanceof CtoComIOInterface)
        {
            return $objEnginge;
        }
        else
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['not_a_engine']);
        }
    }

    /**
     *
     * @param string $strAccept
     * @return String 
     */
    public static function getEngingenameForAccept($strAccept)
    {
        foreach ($GLOBALS["CTOCOM_IO"] as $keyIO => $valueIO)
        {
            if (in_array($strAccept, $valueIO["accept"]))
            {
                return $keyIO;
            }
        }

        return false;
    }

    /**
     *
     * @param string $strAccept
     * @return CtoComIOInterface 
     */
    public static function getEngingeForAccept($strAccept)
    {
        foreach ($GLOBALS["CTOCOM_IO"] as $keyIO => $valueIO)
        {
            if (in_array($strAccept, $valueIO["accept"]))
            {
                return self::getEngine($keyIO);
            }
        }

        return false;
    }

    /**
     *
     * @param string $strContentType
     * @return String 
     */
    public static function getEngingenameForContentType($strContentType)
    {
        foreach ($GLOBALS["CTOCOM_IO"] as $keyIO => $valueIO)
        {
            if ($strContentType == $valueIO["contentType"])
            {
                return $keyIO;
            }
        }

        return false;
    }

    /**
     *
     * @param string $strContentType
     * @return CtoComIOInterface 
     */
    public static function getEngingeForContentType($strContentType)
    {
        foreach ($GLOBALS["CTOCOM_IO"] as $keyIO => $valueIO)
        {
            if ($strContentType == $valueIO["contentType"])
            {
                return self::getEngine($keyIO);
            }
        }

        return false;
    }

    /**
     *
     * @param string $strContentType
     * @return CtoComIOInterface 
     */
    public static function getEngingeByName($strName)
    {
        foreach ($GLOBALS["CTOCOM_IO"] as $keyIO => $valueIO)
        {
            if ($strName == $keyIO)
            {
                return self::getEngine($keyIO);
            }
        }

        return false;
    }

    /**
     * Check if a given I/O engine exist
     * 
     * @param string $strName Name of the engine
     * @return boolean [True|False] 
     */
    public static function engineExist($strName)
    {
        foreach ($GLOBALS["CTOCOM_IO"] as $keyIO => $valueIO)
        {
            if ($strName == $keyIO)
            {
                return true;
            }
        }

        return false;
    }

}

?>