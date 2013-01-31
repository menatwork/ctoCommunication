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
 * Interface for CtoComContainerError
 */
class CtoComContainerError
{

    protected $strLanguage;
    protected $intID;
    protected $mixObject;
    protected $strMessage;
    protected $strRPC;
    protected $strClass;
    protected $strFunction;
    protected $objException;

    public function getLanguage()
    {
        return $this->strLanguage;
    }

    public function setLanguage($strLanguage)
    {
        $this->strLanguage = $strLanguage;
    }

    public function getID()
    {
        return $this->intID;
    }

    public function setID($intID)
    {
        $this->intID = $intID;
    }

    public function getObject()
    {
        return $this->mixObject;
    }

    public function setObject($mixObject)
    {
        $this->mixObject = $mixObject;
    }

    public function getMessage()
    {
        return $this->strMessage;
    }

    public function setMessage($strMessage)
    {
        $this->strMessage = $strMessage;
    }

    public function getRPC()
    {
        return $this->strRPC;
    }

    public function setRPC($strRPC)
    {
        $this->strRPC = $strRPC;
    }

    public function getClass()
    {
        return $this->strClass;
    }

    public function setClass($strClass)
    {
        $this->strClass = $strClass;
    }

    public function getFunction()
    {
        return $this->strFunction;
    }

    public function setFunction($strFunction)
    {
        $this->strFunction = $strFunction;
    }
    
    public function getException()
    {
        return $this->objException;
    }

    public function setException($objException)
    {
        $this->objException = $objException;
    }



}

?>