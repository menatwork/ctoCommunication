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
 * Interface for Codifyengine
 */
class CtoComContainerIO
{

    protected $booSuccess;    
    protected $mixResponse;
    protected $booSplitcontent;
    protected $intSplitcount;
    protected $strSplitname;
    protected $objError;

    public function isSuccess()
    {
        return $this->booSuccess;
    }

    public function setSuccess($booSuccess)
    {
        $this->booSuccess = $booSuccess;
    }

    public function getResponse()
    {
        return $this->mixResponse;
    }

    public function setResponse($mixResponse)
    {
        $this->mixResponse = $mixResponse;
    }

    public function getSplitcount()
    {
        return $this->intSplitcount;
    }

    public function setSplitcount($intSplitcount)
    {
        $this->intSplitcount = $intSplitcount;
    }

    public function getSplitname()
    {
        return $this->strSplitname;
    }

    public function setSplitname($strSplitname)
    {
        $this->strSplitname = $strSplitname;
    }
    
    public function isSplitcontent()
    {
        return $this->booSplitcontent;
    }

    public function setSplitcontent($booSplitcontent)
    {
        $this->booSplitcontent = $booSplitcontent;
    }
    
    /**
     * @return CtoComContainerError 
     */
    public function getError()
    {
        return $this->objError;
    }

    public function setError(CtoComContainerError $objError)
    {
        $this->objError = $objError;
    }


}

?>