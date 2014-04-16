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
 * Interface for Codifyengine
 */
abstract class CtoComCodifyengineAbstract
{
    protected $strKey;
    protected $strName;

    abstract public function Decrypt($string);

    abstract public function Encrypt($string);

    abstract public function setKey($string);

    /**
     * Return key
     * 
     * @return string 
     */
    public function getKey()
    {
        return $this->strKey;
    }

    /**
     * Return name
     * 
     * @return string 
     */
    public function getName()
    {
        return $this->strName;
    }

}