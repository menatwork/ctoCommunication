<?php

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