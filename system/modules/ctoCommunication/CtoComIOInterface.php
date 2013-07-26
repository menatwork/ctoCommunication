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
 * Interface for Codifyengine
 */
interface CtoComIOInterface
{

    /**
     * @param mix Anything like string, int, float, object, array and so on.
     * @return string The string for output
     */
    public function OutputPost($mixOutput, \CtoComCodifyengineAbstract $objCodifyEngine);

    /**
     * @param string $strPost The string from POST
     * @return mix Anything you want like string, int, objects, array and so on
     */
    public function InputPost($strPost, \CtoComCodifyengineAbstract $objCodifyEngine);

    /**
     * @param CtoComContainerIO $container Container with information
     */
    public function OutputResponse(\CtoComContainerIO $container, \CtoComCodifyengineAbstract $objCodifyEngine);

    /**
     * @param string $strResponse The Response String
     * @return CtoComContainerIO
     */
    public function InputResponse($strResponse, \CtoComCodifyengineAbstract $objCodifyEngine);
}