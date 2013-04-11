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
 * Factory for create the codifyengine
 */
class CtoComCodifyengineFactory extends Backend
{

    /**
     * Create the codifyengine.
     * 
     * @return CtoComCodifyengineAbstract 
     */
    public static function getEngine($strEngine = null)
    {
        // Use default codifyengine, if no one is set
        if ($strEngine == "" || $strEngine == null)
            $strEngine = "aes";

        // Check if engeni is known
        if (!key_exists($strEngine, $GLOBALS["CTOCOM_ENGINE"]))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_engine'], array($strEngine)));
        }
        
        $arrEngine = $GLOBALS["CTOCOM_ENGINE"][$strEngine];
                
        // Check if engine exists in filesystem
        if (!file_exists(TL_ROOT . "/" . $arrEngine["folder"] . "/" . $arrEngine["classname"] . ".php")) {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['missing_engine'], array($arrEngine["classname"] . ".php")));
        }
        
        $strClass = $arrEngine["classname"];  
        $objEnginge = new $strClass();
        
        // Get engine
        if ($objEnginge instanceof CtoComCodifyengineAbstract)
        {
            return $objEnginge;
        }
        else
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['not_a_engine']);
        }
    }

}