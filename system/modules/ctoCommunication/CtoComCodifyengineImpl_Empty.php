<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * CtoComCodifyengineImpl_Empty
 */
class CtoComCodifyengineImpl_Empty extends CtoComCodifyengineAbstract
{
    
    protected $strName = "empty";

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
        return $text;
    }

    // Decrypt
    public function Decrypt($text)
    {
        return trim($text);
    }

}