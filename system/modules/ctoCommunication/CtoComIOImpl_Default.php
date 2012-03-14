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

/**
 * Interface for Codifyengine
 */
class CtoComIOImpl_Default extends System implements CtoComIOInterface
{

    /**
     * 
     */
    public function OutputPost($mixOutput, CtoComCodifyengineAbstract $objCodifyEngine)
    {
        // serliaze/encrypt/compress/base64 function                    
        $mixOutput = serialize(array("data" => $mixOutput));
        $mixOutput = $objCodifyEngine->Encrypt($mixOutput);
        $mixOutput = gzcompress($mixOutput);
        $mixOutput = base64_encode($mixOutput);

        return $mixOutput;
    }

    /**
     * @param string $strPost The string from POST
     * @return mix Anything you want like string, int, objects, array and so on
     */
    public function InputPost($mixPost, CtoComCodifyengineAbstract $objCodifyEngine)
    {
        $mixPost = base64_decode($mixPost);
        $mixPost = gzuncompress($mixPost);
        $mixPost = $objCodifyEngine->Decrypt($mixPost);
        $mixPost = deserialize($mixPost);

        $mixPost = $mixPost["data"];

        if (is_null($mixPost))
        {
            return null;
        }
        else
        {
            return $mixPost;
        }
    }

    /**
     * String Response as String
     */
    public function OutputResponse(CtoComContainerIO $objContainer, CtoComCodifyengineAbstract $objCodifyEngine)
    {
        if ($objContainer->getError() != null)
        {
            $mixError = array();
            $mixError["language"]  = $objContainer->getError()->getLanguage();
            $mixError["id"]        = $objContainer->getError()->getID();
            $mixError["object"]    = $objContainer->getError()->getObject();
            $mixError["msg"]       = $objContainer->getError()->getMessage();
            $mixError["rpc"]       = $objContainer->getError()->getRPC();
            $mixError["class"]     = $objContainer->getError()->getClass();
            $mixError["function"]  = $objContainer->getError()->getFunction();
            $mixError["exception"] = $objContainer->getError()->getException();
        }
        else
        {
            $mixError = "";
        }

        $mixOutput = array(
            "success" => $objContainer->isSuccess(),
            "error" => $mixError,
            "response" => $objContainer->getResponse(),
            "splitcontent" => $objContainer->isSplitcontent(),
            "splitcount" => $objContainer->getSplitcount(),
            "splitname" => $objContainer->getSplitname()
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
     * @return CtoComIOResponseContainer
     */
    public function InputResponse($strResponse, CtoComCodifyengineAbstract $objCodifyEngine)
    {
        // Check for start and end tag
        if (strpos($strResponse, "<|@|") === FALSE || strpos($strResponse, "|@|>") === FALSE)
        {
            throw new Exception("Could not find start or endtag from response.");
        }

        // Find position of start/end - tag
        $intStart  = intval(strpos($strResponse, "<|@|") + 4);
        $intLength = intval(strpos($strResponse, "|@|>") - $intStart);

        $strResponse = substr($strResponse, $intStart, $intLength);
        $strResponse = base64_decode($strResponse);
        $strResponse = @gzuncompress($strResponse);

        // Check if uncompress works
        if ($strResponse === FALSE)
        {
            throw new Exception("Error on uncompressing the response. Maybe wrong API-Key or ctoCom version.");
        }

        // Decrypt
        $strResponse = $objCodifyEngine->Decrypt($strResponse);

        // Deserialize response
        $arrResponse = deserialize($strResponse);

        // Check if we have a array
        if (is_array($arrResponse) == false)
        {
            throw new Exception("Response is not an array. Maybe wrong API-Key or cryptionengine.");
        }

        // Clean array
        $arrResponse = $this->cleanUp($arrResponse);

        $objContainer = new CtoComContainerIO();
        $objContainer->setSuccess($arrResponse["success"]);
        $objContainer->setResponse($arrResponse["response"]);
        $objContainer->setSplitcontent($arrResponse["splitcontent"]);
        $objContainer->setSplitcount($arrResponse["splitcount"]);
        $objContainer->setSplitname($arrResponse["splitname"]);

        // Set error
        if ($arrResponse["error"] != "")       
        {
            $objError = new CtoComContainerError();
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
     * @return array 
     */
    protected function cleanUp($arrArray)
    {
        foreach ($arrArray as $key => $value)
        {
            if (is_array($value))
            {
                $arrArray[$key] = $this->cleanUp($value);
            }
            else
            {
                $arrArray[$key] = html_entity_decode($value);
            }
        }

        return $arrArray;
    }

}

?>
