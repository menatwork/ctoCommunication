<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

if (file_exists(TL_ROOT . '/system/modules/phpseclib/Crypt/AES.php'))
{
    include_once(TL_ROOT . '/system/modules/phpseclib/Crypt/AES.php');
}

/**
 * CtoComCodifyengineImpl_AESO
 */
class CtoComCodifyengineImpl_AESO extends \CtoComCodifyengineAbstract
{
    
    protected $strKey   = "";
    protected $strName  = "aes";
    protected $objAES;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (file_exists(TL_ROOT . '/system/modules/phpseclib/Crypt/AES.php'))
        {
            $this->objAES = new Crypt_AES();
        }
        else
        {
            $this->objAES = null;
        }
    }

    /* -------------------------------------------------------------------------
     * getter / setter / clear
     */

    public function setKey($strKey)
    {
        if ($this->objAES == null)
        {
            throw new \RuntimeException("Could not find '/system/modules/phpseclib/Crypt/AES.php'. Please install 'phpseclib'.");
        }
        
        $this->strKey = $strKey;
        $this->objAES->setKey($this->strKey);
    }

    /* -------------------------------------------------------------------------
     * Functions
     */

    // Encrypt
    public function Encrypt($text)
    {
        if ($this->objAES == null)
        {
            throw new \RuntimeException("Could not find '/system/modules/phpseclib/Crypt/AES.php'. Please install 'phpseclib'.");
        }

        return $this->objAES->encrypt($text);
    }

    // Decrypt
    public function Decrypt($text)
    {
        if ($this->objAES == null)
        {
            throw new \RuntimeException("Could not find '/system/modules/phpseclib/Crypt/AES.php'. Please install 'phpseclib'.");
        }

        return $this->objAES->decrypt($text);
    }

}