<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace CtoCommunication\InputOutput;

use CtoCommunication\Codifyengine\Base as CodifyengineBase;
use CtoCommunication\Container\Error;
use CtoCommunication\Container\IO;
use CtoCommunication\Helper\Debug;

/**
 * Interface for Codifyengine
 */
class Base implements InterfaceInputOutput
{

    /**
     * @param mixed            $mixOutput
     * @param CodifyengineBase $objCodifyEngine
     *
     * @return mixed|string
     */
    public function OutputPost($mixOutput, $objCodifyEngine)
    {
        // serliaze/encrypt/compress/base64 function
        $mixOutput = serialize(array("data" => $mixOutput));
        $mixOutput = $objCodifyEngine->Encrypt($mixOutput);
        $mixOutput = gzcompress($mixOutput);
        $mixOutput = base64_encode($mixOutput);

        return $mixOutput;
    }

    /**
     * @param string           $mixPost
     * @param CodifyengineBase $objCodifyEngine
     *
     * @return mixed Anything you want like string, int, objects, array and so on
     * @internal param string $strPost The string from POST
     *
     */
    public function InputPost($mixPost, $objCodifyEngine)
    {
        $mixPost = base64_decode($mixPost);
        $mixPost = gzuncompress($mixPost);
        $mixPost = $objCodifyEngine->Decrypt($mixPost);
        $mixPost = unserialize($mixPost);

        $mixPost = $mixPost["data"];

        if (is_null($mixPost)) {
            return null;
        } else {
            return $mixPost;
        }
    }

    /**
     * String Response as String
     *
     * @param IO   $objContainer
     * @param Base $objCodifyEngine
     *
     * @return array|string
     */
    public function OutputResponse(IO $objContainer, $objCodifyEngine)
    {
        if ($objContainer->getError() != null) {
            $mixError              = array();
            $mixError["language"]  = $objContainer->getError()->getLanguage();
            $mixError["id"]        = $objContainer->getError()->getID();
            $mixError["object"]    = $objContainer->getError()->getObject();
            $mixError["msg"]       = $objContainer->getError()->getMessage();
            $mixError["rpc"]       = $objContainer->getError()->getRPC();
            $mixError["class"]     = $objContainer->getError()->getClass();
            $mixError["function"]  = $objContainer->getError()->getFunction();
            $mixError["exception"] = $objContainer->getError()->getException();
        } else {
            $mixError = "";
        }

        $mixOutput = array(
            "success"      => $objContainer->isSuccess(),
            "error"        => $mixError,
            "response"     => $objContainer->getResponse(),
            "splitcontent" => $objContainer->isSplitcontent(),
            "splitcount"   => $objContainer->getSplitcount(),
            "splitname"    => $objContainer->getSplitname()
        );

        $mixOutput = serialize($mixOutput);
        $mixOutput = $objCodifyEngine->Encrypt($mixOutput);
        $mixOutput = gzcompress($mixOutput);
        $mixOutput = base64_encode($mixOutput);
        $mixOutput = "<|@|" . $mixOutput . "|@|>";

        return $mixOutput;
    }

    /**
     * @todo Update the error class or better make a implementation of it
     * @return IO
     */
    public function InputResponse($strResponse, $objCodifyEngine)
    {
        $objDebug = Debug::getInstance();

        // Check for start and end tag
        if (strpos($strResponse, "<|@|") === false || strpos($strResponse, "|@|>") === false) {
            $objDebug->addDebug("Error CtoComIOImpl_Default", substr($strResponse, 0, 4096));
            throw new \RuntimeException("Could not find start or endtag from response.");
        }

        // Find position of start/end - tag
        $intStart  = intval(strpos($strResponse, "<|@|") + 4);
        $intLength = intval(strpos($strResponse, "|@|>") - $intStart);

        $strResponse = substr($strResponse, $intStart, $intLength);
        $strResponse = base64_decode($strResponse);
        $strResponse = @gzuncompress($strResponse);

        // Check if uncompress works
        if ($strResponse === false) {
            throw new \RuntimeException("Error on uncompressing the response. Maybe wrong API-Key or ctoCom version.");
        }

        // Decrypt
        $strResponse = $objCodifyEngine->Decrypt($strResponse);

        // Deserialize response
        $arrResponse = unserialize($strResponse);

        // Check if we have a array
        if (is_array($arrResponse) == false) {
            $objDebug->addDebug("Error CtoComIOImpl_Default", substr($arrResponse, 0, 4096));
            throw new \RuntimeException("Response is not an array. Maybe wrong API-Key or cryptionengine.");
        }

        // Clean array
        $arrResponse = $this->cleanUp($arrResponse);

        $objContainer = new IO();
        $objContainer->setSuccess($arrResponse["success"]);
        $objContainer->setResponse($arrResponse["response"]);
        $objContainer->setSplitcontent($arrResponse["splitcontent"]);
        $objContainer->setSplitcount($arrResponse["splitcount"]);
        $objContainer->setSplitname($arrResponse["splitname"]);

        // Set error
        if ($arrResponse["error"] != "") {
            $objError = new Error();
            $objError->setID($arrResponse["error"]["id"]);
            $objError->setObject($arrResponse["error"]["object"]);
            $objError->setMessage($arrResponse["error"]["msg"]);
            $objError->setRPC($arrResponse["error"]["rpc"]);
            $objError->setClass($arrResponse["error"]["class"]);
            $objError->setFunction($arrResponse["error"]["function"]);
            $objError->setException($arrResponse["error"]["exception"]);

            $objContainer->setError($objError);
        }

        return $objContainer;
    }

    /**
     * Run throw a array and decode html entities
     *
     * @param array $arrArray
     *
     * @return array
     */
    protected function cleanUp($arrArray)
    {
        foreach ($arrArray as $key => $value) {
            if (is_array($value)) {
                $arrArray[$key] = $this->cleanUp($value);
            } else if (is_object($value)) {
                continue;
            } else {
                $arrArray[$key] = html_entity_decode($value);
            }
        }

        return $arrArray;
    }

}
