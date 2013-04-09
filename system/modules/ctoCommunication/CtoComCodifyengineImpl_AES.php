<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

if (file_exists(TL_ROOT . '/system/modules/phpseclib/Crypt/AES.php'))
{
    include_once(TL_ROOT . '/system/modules/phpseclib/Crypt/AES.php');
}

/**
 * CtoComCodifyengineImpl_AES
 */
class CtoComCodifyengineImpl_AES extends CtoComCodifyengineAbstract
{

    protected $strKey  = "";
    protected $strName = "aes";
    protected $objAES;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!file_exists(TL_ROOT . '/system/modules/phpseclib/Crypt/AES.php'))
        {
            throw new Exception("Missing AES plugin in '/system/modules/phpseclib/Crypt/AES.php'");
        }
    }

    /* -------------------------------------------------------------------------
     * getter / setter / clear
     */

    public function setKey($strKey)
    {
        $strKey = str_replace(array("-", "_"), array("", "") , $strKey);
        
        if(strlen($strKey) < 16)
        {
            $strKey .= str_repeat("5", strlen($strKey) - 16);
        }
        else if(strlen($strKey) > 16)
        {
            $strKey = substr($strKey, 0, 16);
        }        
        
        $this->strKey = $strKey;
    }

    /* -------------------------------------------------------------------------
     * Functions
     */

    // Encrypt
    public function Encrypt($text)
    {
        $this->objAES = new Crypt_AES();
        $this->objAES->setKey($this->strKey);
        
        $iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        $this->objAES->setIV($iv);
        
        return $iv . "|@|" . $this->objAES->encrypt($text);
    }

    // Decrypt
    public function Decrypt($text)
    {
        $this->objAES = new Crypt_AES();
        $this->objAES->setKey($this->strKey);

        $arrText = explode("|@|", $text);

        if (!is_array($arrText) || count($arrText) != 2)
        {
            throw new Exception("Error by decrypt. Missing IV");
        }

        $this->objAES->setIV($arrText[0]);
        return $this->objAES->decrypt($arrText[1]);
    }

}

?>