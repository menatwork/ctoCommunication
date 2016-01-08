<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace CtoCommunication\Container;

use CtoCommunication\Container\Error;

/**
 * Interface for Codifyengine
 */
class IO
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
     * @return Error
     */
    public function getError()
    {
        return $this->objError;
    }

    public function setError(Error $objError)
    {
        $this->objError = $objError;
    }

}
