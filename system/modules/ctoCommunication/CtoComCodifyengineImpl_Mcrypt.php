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
 * CtoComCodifyengineImpl_Mcrypt
 */
class CtoComCodifyengineImpl_Mcrypt extends \CtoComCodifyengineAbstract
{
    
    protected $strKey = "";
    protected $strName = "mcrypt";

    /**
     * Constructor
     */
    public function __construct()
    {
        
    }    

    /* -------------------------------------------------------------------------
     * getter / setter / clear
     */

    public function setKey($strKey)
    {       
        $this->strKey = $strKey;
    }

    /* -------------------------------------------------------------------------
     * Functions
     */

    // Verschluesseln
    public function Encrypt($text)
    {
        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
        $ks = mcrypt_enc_get_key_size($td);
        
        /* Create key */
        $key = substr(md5($this->strKey), 0, $ks);

        /* Intialize encryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Encrypt data */
        $encrypted = mcrypt_generic($td, $text);
       
        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        /* Show string */
        return $iv . "|@|" . $encrypted;
    }

    // Decrypt
    public function Decrypt($text)
    {
        $arrText = explode("|@|", $text);
        
        if(!is_array($arrText) || count($arrText) != 2)
        {
            throw new \RuntimeException("Error by decrypt. Missing IV");
        }
        
        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        
        $iv = $arrText[0];
        $ks = mcrypt_enc_get_key_size($td);
        
        /* Create key */
        $key = substr(md5($this->strKey), 0, $ks);

        /* Initialize encryption module for decryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Decrypt encrypted string */
        $decrypted = mdecrypt_generic($td, $arrText[1]);

        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        /* Show string */
        return $decrypted;
    }

}