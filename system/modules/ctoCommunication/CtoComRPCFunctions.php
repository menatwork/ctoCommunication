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
 * @copyright  MEN AT WORK 2011
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Remote Procedure Call Class
 */
class CtoComRPCFunctions extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    //- Singelten pattern --------
    protected static $instance = null;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Construtor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->import("Config");
    }

    /**
     * Singelten pattern
     * 
     * @return CtoComRPCFunctions 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new CtoComRPCFunctions();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * RPC Functions
     */
    
    //- File Functions --------
    
    public function getResponsePart($strFilename, $intFilecount)
    {
        $strFilepath = "/system/tmp/" . $intFilecount . "_" . $strFilename;

        if (!file_exists(TL_ROOT . $strFilepath))
        {
            throw new Exception("Missing partfile $strFilepath");
        }

        $objFile = new File($strFilepath);
        $strReturn = $objFile->getContent();
        $objFile->close();

        return $strReturn;
    }

    //- Referer Functions --------

    /**
     * Disable referer check from contao
     * 
     * @return boolean 
     */
    public function referrer_disable()
    {
        $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", true);
        return true;
    }

    /**
     * Enable referer check from contao
     * 
     * @return boolean 
     */
    public function referrer_enable()
    {
        $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", false);
        return false;
    }

    //- Version Functions --------

    public function getCtoComVersion()
    {
        return $GLOBALS["CTOCOM_VERSION"];
    }
    
    public function getContaoVersion()
    {
        return VERSION;
    }
    
    //- Handshake ---------------
    
    public function generateUUID()
    {        
        $arrUUID = $this->Database->prepare("SELECT uuid() as uid")->execute()->fetchAllAssoc();

        $this->Database->prepare("INSERT INTO tl_ctocom_cache %s")
                ->set(array("uid" => $arrUUID[0]["uid"], "tstamp" => time()))
                ->execute();

        return $arrUUID[0]["uid"];
    }
    
    public function deleteUUID()
    { 
        $this->Database->prepare("DELETE FROM tl_ctocom_cache WHERE uid=?")                
                ->execute($this->Input->get("con"));

        return true;
    }

    public function startHandshake()
    {
        // Imoprt
        require_once TL_ROOT . '/plugins/DiffieHellman/DiffieHellman.php';
        
        // Init
        $intPrimeLength = 128;        
        $strGenerator = "2";
        
        // Generate prime
        $strPrime = rand(1, 9);
        for($i = 0 ; $i < $intPrimeLength ; $i++)
        {
            $strPrime .= rand(0, 9);
        }        
        
        // Build array
        $arrDiffieHellman = array(
            "generator" => $strGenerator,
            "prime" => $strPrime,
        );
        
        // Create random private key.
        $intPrivateLength = rand(strlen($arrDiffieHellman["generator"]), strlen($arrDiffieHellman["prime"]) - 2);
        $strPrivate = rand(1, 9);
        
        for ($i = 0; $i < $intPrivateLength; $i++)
        {
            $strPrivate .= rand(0, 9);
        }

        // Start key gen
        $objDiffieHellman = new Crypt_DiffieHellman($arrDiffieHellman["prime"], $arrDiffieHellman["generator"], $strPrivate);
        $objDiffieHellman->generateKeys();

        $strPublicKey = $objDiffieHellman->getPublicKey();

        $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                ->set(array(
                    "tstamp" => time(),
                    "prime" => $arrDiffieHellman["prime"],
                    "generator" => $arrDiffieHellman["generator"],
                    "public_key" => $strPublicKey,
                    "private_key" => $strPrivate,
                ))
                ->execute($this->Input->get("con"));

        $arrDiffieHellman["public_key"] = $strPublicKey;
        
        return $arrDiffieHellman;
    }

    public function checkHandshake()
    {
        // Imoprt
        require_once TL_ROOT . '/plugins/DiffieHellman/DiffieHellman.php';

        if(strlen($this->Input->get("key")) == 0)
        {
            throw new Exception("Could not find public key for handshake.");
        }
        
        // Load information
        $arrConnections = $this->Database->prepare("SELECT * FROM tl_ctocom_cache WHERE uid=?")
                ->execute($this->Input->get("con"))
                ->fetchAllAssoc();
        
        // Start key gen
        $objDiffieHellman = new Crypt_DiffieHellman($arrConnections[0]["prime"], $arrConnections[0]["generator"], $arrConnections[0]["private_key"]);
        $objDiffieHellman->generateKeys();

        $strSecretKey = $objDiffieHellman->computeSecretKey($this->Input->get("key"))
                ->getSharedSecretKey();

        $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                ->set(array(
                    "tstamp" => time(),
                    "shared_secret_key" => $strSecretKey,
                ))
                ->execute($this->Input->get("con"));

        return $objDiffieHellman->getPublicKey();
    }

}

?>
