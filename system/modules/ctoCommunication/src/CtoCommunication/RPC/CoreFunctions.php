<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace CtoCommunication\RPC;

/**
 * Remote Procedure Call Class
 */
class CoreFunctions extends \Backend
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
     * @return CoreFunctions
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self();

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
            throw new \RuntimeException("Missing partfile $strFilepath");
        }

        $objFile   = new \File($strFilepath);
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
        if ($GLOBALS['TL_CONFIG']['disableRefererCheck'] == false)
        {
            if (array_key_exists("ctoCom_disableRefererCheck", $GLOBALS['TL_CONFIG']))
            {
                $this->Config->update("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", false);
            }
            else
            {
                $this->Config->add("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", false);
            }

            $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", true);
        }
        else
        {
            if (array_key_exists("ctoCom_disableRefererCheck", $GLOBALS['TL_CONFIG']))
            {
                $this->Config->update("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", true);
            }
            else
            {
                $this->Config->add("\$GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck']", true);
            }
        }

        return true;
    }

    /**
     * Enable referer check from contao
     *
     * @return boolean
     */
    public function referrer_enable()
    {
        if ($GLOBALS['TL_CONFIG']['ctoCom_disableRefererCheck'] == true)
        {
            $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", true);
        }
        else
        {
            $this->Config->update("\$GLOBALS['TL_CONFIG']['disableRefererCheck']", false);
        }

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
                ->set(array("uid"    => $arrUUID[0]["uid"], "tstamp" => time()))
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
        if ($this->Input->get("useAPIK") == true)
        {
            return true;
        }
        else
        {
            // Imoprt
            require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman.php';

            // Init
            $intPrimeLength = 32;
            $strGenerator   = 2;

            $objLastException = null;

            for ($i = 0; $i < 100; $i++)
            {
                // Generate prime
                $strPrime = rand(1, 9);
                for ($ii       = 0; $ii < $intPrimeLength; $ii++)
                {
                    $strPrime .= rand(0, 9);
                }

                // Build array
                $arrDiffieHellman = array(
                    "generator" => $strGenerator,
                    "prime"     => $strPrime,
                );

                // Create random private key.
                $intPrivateLength = rand(strlen($arrDiffieHellman["generator"]), strlen($arrDiffieHellman["prime"]) - 2);
                $strPrivate       = rand(1, 9);

                for ($ii = 0; $ii < $intPrivateLength; $ii++)
                {
                    $strPrivate .= rand(0, 9);
                }

                if (!preg_match("/^\d+$/", $strPrivate))
                {
                    $objLastException = new Exception("Private key is not a natural number");
                    continue;
                }

                if (!preg_match("/^\d+$/", $strPrime))
                {
                    $objLastException = new Exception("Prime key is not a natural number");
                    continue;
                }

                try
                {
                    // Start key gen
                    $objDiffieHellman = new \Crypt_DiffieHellman($arrDiffieHellman["prime"], $arrDiffieHellman["generator"], $strPrivate, \Crypt_DiffieHellman::NUMBER);
                    $objDiffieHellman->generateKeys();

                    $strPublicKey = $objDiffieHellman->getPublicKey();
                }
                catch (Exception $exc)
                {
                    $objLastException = $exc;
                    continue;
                }

                // Check puplic key
                if (!preg_match("/^\d+$/", $strPublicKey))
                {
                    $objLastException = new Exception("Public key is not a natural number");
                    continue;
                }

                $objLastException = null;
                break;
            }

            if ($objLastException)
            {
                throw new \RuntimeException($objLastException->getMessage());
            }

            $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                    ->set(array(
                        "tstamp"      => time(),
                        "prime"       => $arrDiffieHellman["prime"],
                        "generator"   => $arrDiffieHellman["generator"],
                        "public_key"  => $strPublicKey,
                        "private_key" => $strPrivate,
                    ))
                    ->execute($this->Input->get("con"));

            $arrDiffieHellman["public_key"]  = $strPublicKey;

            return $arrDiffieHellman;
        }
    }

    public function checkHandshake()
    {
        if ($this->Input->get("useAPIK") == true)
        {
            $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                    ->set(array(
                        "tstamp"            => time(),
                        "shared_secret_key" => $GLOBALS['TL_CONFIG']['ctoCom_APIKey'],
                    ))
                    ->execute($this->Input->get("con"));

            return true;
        }
        else
        {
            // Imoprt
            require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman.php';

            if (strlen($this->Input->get("key")) == 0)
            {
                throw new Exception("Could not find public key for handshake.");
            }

            // Load information
            $arrConnections = $this->Database->prepare("SELECT * FROM tl_ctocom_cache WHERE uid=?")
                    ->execute($this->Input->get("con"))
                    ->fetchAllAssoc();

            // Start key gen
            $objDiffieHellman = new \Crypt_DiffieHellman($arrConnections[0]["prime"], $arrConnections[0]["generator"], $arrConnections[0]["private_key"]);
            $objDiffieHellman->generateKeys();

            $strSecretKey = $objDiffieHellman->computeSecretKey($this->Input->get("key"))
                    ->getSharedSecretKey();

            $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                    ->set(array(
                        "tstamp"            => time(),
                        "shared_secret_key" => $strSecretKey,
                    ))
                    ->execute($this->Input->get("con"));

            return $objDiffieHellman->getPublicKey();
        }
    }

}
