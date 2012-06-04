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
if (file_exists(TL_ROOT . '/plugins/phpseclib/Crypt/AES.php'))
{
    include_once(TL_ROOT . '/plugins/phpseclib/Crypt/AES.php');
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
        if (!file_exists(TL_ROOT . '/plugins/phpseclib/Crypt/AES.php'))
        {
            throw new Exception("Missing AES plugin in '/plugins/phpseclib/Crypt/AES.php'");
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