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
    
    protected $strKey   = "";
    protected $strName  = "aes";
    protected $objAES;

    /**
     * Constructor
     */
    public function __construct()
    {   
        if (file_exists(TL_ROOT . '/plugins/phpseclib/Crypt/AES.php'))
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
            throw new Exception("Could not find '/plugins/phpseclib/Crypt/AES.php'. Please install 'phpseclib'.");
        }

        $strKey = str_replace("-", "", $strKey);
        $strKey = substr($strKey, 0, 16);
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
            throw new Exception("Could not find '/plugins/phpseclib/Crypt/AES.php'. Please install 'phpseclib'.");
        }

        $iv = mcrypt_create_iv(16, MCRYPT_DEV_RANDOM);
        $this->objAES->setIV($iv);
        return $iv . "|@|" . $this->objAES->encrypt($text);
    }

    // Decrypt
    public function Decrypt($text)
    {
        if ($this->objAES == null)
        {
            throw new Exception("Could not find '/plugins/phpseclib/Crypt/AES.php'. Please install 'phpseclib'.");
        }

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