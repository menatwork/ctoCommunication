<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\CtoCommunicationBundle\InputOutput;

use MenAtWork\CtoCommunicationBundle\Codifyengine\Base as CodifyengineBase;
use MenAtWork\CtoCommunicationBundle\Container\IO;

/**
 * Interface for Codifyengine
 */
interface InterfaceInputOutput
{

    /**
     * @param mixed            $mixOutput Anything like string, int, float, object, array and so on.
     *
     * @param CodifyengineBase $objCodifyEngine
     *
     * @return string The string for output
     */
    public function OutputPost($mixOutput, $objCodifyEngine);

    /**
     * @param string           $strPost The string from POST
     *
     * @param CodifyengineBase $objCodifyEngine
     *
     * @return mixed Anything you want like string, int, objects, array and so on
     */
    public function InputPost($strPost, $objCodifyEngine);

    /**
     * @param IO               $container Container with information
     *
     * @param CodifyengineBase $objCodifyEngine
     */
    public function OutputResponse(IO $container, $objCodifyEngine);

    /**
     * @param string           $strResponse The Response String
     *
     * @param CodifyengineBase $objCodifyEngine
     *
     * @return IO
     */
    public function InputResponse($strResponse, $objCodifyEngine);
}
