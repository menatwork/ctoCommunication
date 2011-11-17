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
 * @copyright  MEN AT WORK 2011
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

class CtoCommunication extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelten pattern
    protected static $instance = null;
    // Vars
    protected $strUrl;
    protected $strUrlGet;
    protected $strApiKey;
    protected $arrCookies;
    protected $arrRpcList;
    protected $arrError;
    protected $arrNullFields;
    protected $mixOutput;
    // Objects
    protected $objCodifyengine;
    protected $objCodifyengineBlow;
    protected $objDebug;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->objCodifyengine = CtoComCodifyengineFactory::getEngine();
        $this->objCodifyengineBlow = CtoComCodifyengineFactory::getEngine("blowfish");
        $this->objDebug = CtoComDebug::getInstance();

        $this->arrRpcList = $GLOBALS["CTOCOM_FUNCTIONS"];
        $this->arrError = array();
        $this->arrNullFields = array();
    }

    /**
     * Singelton Pattern
     * 
     * @return CtoCommunication 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new CtoCommunication();
        }

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Getter / Setter
     */

    /**
     * Set the url for connection
     * 
     * @param type $strUrl 
     */
    public function setUrl($strUrl)
    {
        $this->strUrl = $strUrl;
    }

    /**
     * Set the API Key
     * 
     * @param stirng $strApiKey 
     */
    public function setApiKey($strApiKey)
    {
        $this->strApiKey = $strApiKey;
    }

    /**
     * Set the client for the connection.
     *
     * @param int $id ID from client
     */
    public function setClient($strUrl, $strCodifyEngine = "Blowfish")
    {
        $this->strUrl = $strUrl;

        $this->setCodifyengine($strCodifyEngine);
    }

    /**
     * Change codifyengine
     * 
     * @param string $strName 
     */
    public function setCodifyengine($strName = Null)
    {        
        $this->objCodifyengine = CtoComCodifyengineFactory::getEngine($strName);
    }

    /**
     * Set Cookie information
     * 
     * @param string $name Key name of array
     * @param mix $value Value for Cookie 
     */
    public function setCookies($name, $value)
    {
        if ($value == "")
        {
            unset($this->arrCookies[$name]);
        }
        else
        {
            $this->arrCookies[$name] = $value;
        }
    }

    //- Getter -------------------

    /**
     * Retrun Url
     * 
     * @return string 
     */
    public function getUrl()
    {
        return $this->strUrl;
    }

    /**
     * Return Api Key
     * 
     * @return string 
     */
    public function getApiKey()
    {
        return $this->strApiKey;
    }

    /**
     * Return Cookies
     * 
     * @return array
     */
    public function getCookies()
    {
        return $this->arrCookies;
    }

    /**
     * Return name of the codifyengine
     * 
     * @return string 
     */
    public function getCodifyengine()
    {
        return $this->objCodifyengine->getName();
    }

    /* -------------------------------------------------------------------------
     * Getter and Setter for the debug class
     */

    /**
     *
     * @return boolean 
     */
    public function getMeasurement()
    {
        return $this->objDebug->getMeasurement();
    }

    /**
     *
     * @param boolean $booMeasurement 
     */
    public function setMeasurement($booMeasurement)
    {
        $this->objDebug->setMeasurement($booMeasurement);
    }

    /**
     *
     * @return boolean 
     */
    public function getDebug()
    {
        return $this->objDebug->getDebug();
    }

    /**
     *
     * @param boolean $booDebug 
     */
    public function setDebug($booDebug)
    {
        $this->objDebug->setDebug($booDebug);
    }

    /**
     *
     * @return string 
     */
    public function getFileMeasurement()
    {
        return $this->objDebug->getFileMeasurement();
    }

    /**
     *
     * @param string $strFileMeasurement 
     */
    public function setFileMeasurement($strFileMeasurement)
    {
        $this->objDebug->setFileMeasurement($strFileMeasurement);
    }

    /**
     *
     * @return string 
     */
    public function getFileDebug()
    {
        return $this->objDebug->getFileDebug();
    }

    /**
     *
     * @param string $strFileDebug 
     */
    public function setFileDebug($strFileDebug)
    {
        $this->objDebug->setFileDebug($strFileDebug);
    }

    /* -------------------------------------------------------------------------
     * Server / Client Run Functions
     */
    
    /**
     * Run as Server and send some data or files
     * 
     * @param string $rpc
     * @param array $arrData
     * @param boolean $isGET
     * @return mixed
     * @throws Exception 
     */
    public function runServer($rpc, $arrData = array(), $isGET = FALSE)
    {
        $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $rpc);

        $this->strUrlGet = "";

        // Check if everything is set
        if ($this->strApiKey == "" || $this->strApiKey == null)
        {
            throw new Exception("The API Key is not set. Please set first API Key.");
        }

        if ($this->strUrl == "" || $this->strUrl == null)
        {
            throw new Exception("There is no URL set for connection. Please set first the url.");
        }

        // Add Get Parameter
        $strCryptApiKey = $this->objCodifyengineBlow->Encrypt($rpc . "@|@" . $this->strApiKey);
        $strCryptApiKey = urlencode($strCryptApiKey);

        if (strpos($this->strUrl, "?") !== FALSE)
        {
            $this->strUrlGet .= "&engine=" . $this->objCodifyengine->getName() . "&act=" . $rpc . "&apikey=" . $strCryptApiKey;
        }
        else
        {
            $this->strUrlGet .= "?engine=" . $this->objCodifyengine->getName() . "&act=" . $rpc . "&apikey=" . $strCryptApiKey;
        }

        // Set Key for codifyengine
        $this->objCodifyengine->setKey($this->strApiKey);
        
        // New Request
        $objRequest = new RequestExtended();
        $objRequest->acceptgzip = 0;
        $objRequest->acceptdeflate = 0;
        
        // Set Header Accept-Language        
        $objRequest->setHeader("Accept-Language", vsprintf("%s, en;q=0.8", array($GLOBALS['TL_LANGUAGE'])));

        // Which method ? GET or POST
        if ($isGET)
        {
            $objRequest->method = "GET";
            foreach ($arrData as $key => $value)
            {
                $this->strUrlGet .= "&" . $value["name"] . "=" . $value["value"];
            }
        }
        else
        {
            // Build Multipart Post Data
            $objMultipartFormdata = new MultipartFormdata();
            foreach ($arrData as $key => $value)
            {
                if (isset($value["filename"]) == true && strlen($value["filename"]) != 0)
                {
                    // Set field for file
                    if (!$objMultipartFormdata->setFileField($value["name"], $value["filepath"], $value["mime"]))
                    {
                        throw new Exception("Could not add file to postheader.");
                    }
                }
                else
                {
                    // Encrypt funktion
                    $strValue = $this->objCodifyengine->Encrypt(serialize(array("data" => $value["value"])));
                    // Set field
                    $objMultipartFormdata->setField($value["name"], $strValue);
                }
            }

            // Create HTTP Data code
            $objRequest->data = $objMultipartFormdata->compile();

            // Set typ and mime typ
            $objRequest->method = "POST";
            $objRequest->datamime = $objMultipartFormdata->getContentTypeHeader();
        }
        
        // Send new request
        $objRequest->send($this->strUrl . $this->strUrlGet);

        // Debug
        $this->objDebug->addDebug("Request", substr($objRequest->request, 0, 25000));
        $this->objDebug->addDebug("Response", substr($objRequest->response, 0, 25000));

        // Check if evething is okay for connection
        if ($objRequest->hasError())
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Error by sending request with measages: " . $objRequest->code . " " . $objRequest->error);
        }

        if (strlen($objRequest->response) == 0)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("We got a blank response from server.");
        }

        if (strpos($objRequest->response, "Fatal error") !== FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("We got a Fatal error on client site. " . $objRequest->response);
        }
        
        if (strpos($objRequest->response, "Warning") !== FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            
            $intStart = stripos($objRequest->response, "<strong>Warning</strong>:");
            $intEnd = stripos($objRequest->response, "on line");
            
            throw new Exception("We got a Warning on client site.<br /><br />" . substr($objRequest->response, $intStart, $intEnd - $intStart));
        }

        if (strpos($objRequest->response, "<|@|") === FALSE || strpos($objRequest->response, "|@|>") === FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Could not find start or endtag from response.");
        }

        $mixContent = $objRequest->response;

        $intStart = intval(strpos($mixContent, "<|@|") + 4);
        $intLength = intval(strpos($mixContent, "|@|>") - $intStart);

        $mixContent = $this->objCodifyengine->Decrypt(substr($mixContent, $intStart, $intLength));
        $this->objDebug->addDebug("Response Decrypte", substr($mixContent, 0, 2500));

        $mixContent = deserialize($mixContent);

        if (is_array($mixContent) == false)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            throw new Exception("Response is not a array. Maybe wrong key or codifyengine.");
        }

        $mixContent = $this->cleanUp($mixContent);

        if ($mixContent["success"] == 1)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
            return $mixContent["response"];
        }
        else
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);

            $string = vsprintf("There was a error on client site with message:<br/><br/>%s<br/><br/>RPC Call: %s | Class: %s | Function: %s", array(
                nl2br($mixContent["error"][0]["msg"]),
                $mixContent["error"][0]["rpc"],
                (strlen($mixContent["error"][0]["class"]) != 0) ? $mixContent["error"][0]["class"] : " - ",
                (strlen($mixContent["error"][0]["function"]) != 0) ? $mixContent["error"][0]["function"] : " - ",
                    )
            );

            throw new Exception($string);
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
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

    /**
     * Run the communication as client
     *
     * @return void
     */
    public function runClient()
    {
        // Start measurement
        $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $this->Input->get("act"));

        // If we have a ping, just do nothing
        if($this->Input->get("act") == "ping")
        {
            exit();
        }
        
        // API Key - Check -----------------------------------------------------

        if (strlen($this->Input->get("apikey")) == 0)
        {
            $this->log(vsprintf("Call from %s without a API Key.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }

        $mixVar = html_entity_decode($this->Input->get("apikey"));
        $mixVar = $this->objCodifyengineBlow->Decrypt($this->Input->get("apikey"));
        $mixVar = trimsplit("@\|@", $mixVar);
        $strApiKey = $mixVar[1];
        $strAction = $mixVar[0];

        if ($strAction != $this->Input->get("act"))
        {
            $this->log(vsprintf("Error Api Key from %s. Request action: %s | Key action: %s | Api: %s", array(
                        $this->Environment->ip,
                        $this->Input->get("act"),
                        $strAction,
                        $strApiKey
                    )), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }

        if ($GLOBALS['TL_CONFIG']['ctoCom_APIKey'] != $strApiKey)
        {
            $this->log(vsprintf("Call from %s with a wrong API Key: %s", array($this->Environment->ip, $this->Input->get("apikey"))), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }
        
        // Check language settings ---------------------------------------------
        
        if(empty ($GLOBALS['TL_LANGUAGE']))
        {
            $GLOBALS['TL_LANGUAGE'] = "de";
        }      

        // Change the Codifyengine if set --------------------------------------

        if (strlen($this->Input->get("engine")) != 0)
        {
            // Try to change codifyengine
            try
            {
                // Set new an reload key
                $this->setCodifyengine($this->Input->get("engine"));
                $this->objCodifyengine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
            }
            // Error by setting new enigne.
            catch (Exception $exc)
            {
                $this->log("Try to load codifyengine for ctoCommunication with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                exit();
            }
        }
        else
        {
            $this->setCodifyengine("Blowfish");
            $this->objCodifyengine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
        }

        // Run RPC-Check function ----------------------------------------------

        $mixRPCCall = $this->Input->get("act");
        // Check if act is set
        if (strlen($mixRPCCall) == 0)
        {
            $this->arrError[] = array(
                "language" => "rpc_missing",
                "id" => 1,
                "object" => "",
                "msg" => "Missing RPC Call",
                "rpc" => $mixRPCCall,
                "class" => "",
                "function" => "",
            );
        }
        else
        {
            if (!key_exists($mixRPCCall, $this->arrRpcList))
            {
                $this->arrError[] = array(
                    "language" => "rpc_unknown",
                    "id" => 1,
                    "object" => "",
                    "msg" => "Unknown RPC Call",
                    "rpc" => $mixRPCCall,
                    "class" => "",
                    "function" => "",
                );
            }
            else
            {
                $arrParameter = array();

                if ($this->arrRpcList[$mixRPCCall]["parameter"] != FALSE && is_array($this->arrRpcList[$mixRPCCall]["parameter"]))
                {
                    switch ($this->arrRpcList[$mixRPCCall]["typ"])
                    {
                        case "POST":
                            // Decode post 
                            foreach ($_POST as $key => $value)
                            {
                                $mixPost = $this->Input->post($key, true);
                                $mixPost = $this->objCodifyengine->Decrypt($mixPost);
                                $mixPost = deserialize($mixPost);
                                $mixPost = $mixPost["data"];
                                
                                if (is_null($mixPost))
                                {
                                    $this->arrNullFields[] = $key;
                                    $this->Input->setPost($key, $mixPost);
                                }
                                else
                                {
                                    $this->Input->setPost($key, $mixPost);
                                }
                            }

                            // Check if all post are set
                            foreach ($this->arrRpcList[$mixRPCCall]["parameter"] as $value)
                            {
                                $arrPostKey = array_keys($_POST);   
                                                                
                                if (!in_array($value, $arrPostKey) && !in_array($value, $this->arrNullFields))
                                {
                                    $this->arrError[] = array(
                                        "language" => "rpc_data_missing",
                                        "id" => 2,
                                        "object" => $value,
                                        "msg" => "Missing data for " . $value,
                                        "rpc" => $mixRPCCall,
                                        "class" => $this->arrRpcList[$mixRPCCall]["class"],
                                        "function" => $this->arrRpcList[$mixRPCCall]["function"],
                                    );
                                }
                                else
                                {
                                    if(in_array($value, $this->arrNullFields))
                                    {
                                        $arrParameter[$value] = NULL;  
                                    }
                                    else
                                    {
                                        $arrParameter[$value] = $this->Input->post($value, true);  
                                    }                                                                     
                                }
                            }
                            break;

                        default:
                            break;
                    }
                }
            }

            if (count($this->arrError) != 0)
            {
                $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
                return $this->generateOutput();
            }


            try
            {
                $this->objDebug->startMeasurement($this->arrRpcList[$mixRPCCall]["class"], $this->arrRpcList[$mixRPCCall]["function"]);

                $strClassname = $this->arrRpcList[$mixRPCCall]["class"];

                if (!class_exists($strClassname))
                {
                    $this->arrError[] = array(
                        "language" => "rpc_class_not_exists",
                        "id" => 4,
                        "object" => "",
                        "msg" => "The choosen class didn`t exists.",
                        "rpc" => $mixRPCCall,
                        "class" => $this->arrRpcList[$mixRPCCall]["class"],
                        "function" => $this->arrRpcList[$mixRPCCall]["function"],
                    );
                }

                $objReflection = new ReflectionClass($strClassname);
                if ($objReflection->hasMethod("getInstance"))
                {
                    $object = call_user_func_array(array($this->arrRpcList[$mixRPCCall]["class"], "getInstance"), array());
                    $this->mixOutput = call_user_func_array(array($object, $this->arrRpcList[$mixRPCCall]["function"]), $arrParameter);
                }
                else
                {
                    $object = new $this->arrRpcList[$mixRPCCall]["class"];
                    $this->mixOutput = call_user_func_array(array($object, $this->arrRpcList[$mixRPCCall]["function"]), $arrParameter);
                }

                $this->objDebug->stopMeasurement($this->arrRpcList[$mixRPCCall]["class"], $this->arrRpcList[$mixRPCCall]["function"]);
            }
            catch (Exception $exc)
            {
                $this->arrError[] = array(
                    "language" => "rpc_unknown_exception",
                    "id" => 3,
                    "object" => "",
                    "msg" => $exc->getMessage(),
                    "rpc" => $mixRPCCall,
                    "class" => $this->arrRpcList[$mixRPCCall]["class"],
                    "function" => $this->arrRpcList[$mixRPCCall]["function"],
                );

                $this->log(vsprintf("RPC Exception: %s | %s", array($exc->getMessage(), nl2br($exc->getTraceAsString()))), __CLASS__ . " | " . __FUNCTION__, TL_ERROR);
            }
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);
        return $this->generateOutput();
    }

    /* --------------------------------------------------------------------------
     * Helper functions
     */

    /**
     * Build the answer and serialize it
     *
     * @return string
     */
    protected function generateOutput()
    {
        $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__);

        if (count($this->arrError) == 0)
        {
            $strOutput = serialize(array(
                "success" => 1,
                "error" => "",
                "response" => $this->mixOutput,
                    ));
        }
        else
        {
            $strOutput = serialize(array(
                "success" => 0,
                "error" => $this->arrError,
                "response" => "",
                    ));
        }

        $strOutput = $this->objCodifyengine->Encrypt($strOutput);

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__);

        return "<|@|" . $strOutput . "|@|>";
    }
    
    /**
     * Check the required extensions and files for ctoCommunication
     * 
     * @param string $strContent
     * @param string $strTemplate
     * @return string
     */
    public function checkExtensions($strContent, $strTemplate)
    {
        if ($strTemplate == 'be_main')
        {
            if (!is_array($_SESSION["TL_INFO"]))
            {
                $_SESSION["TL_INFO"] = array();
            }

            // required extensions
            $arrRequiredExtensions = array(
                'httprequestextended' => 'httprequestextended',
            );

            // check for required extensions
            foreach ($arrRequiredExtensions as  $key => $val)
            {
                if (!in_array($val, $this->Config->getActiveModules()))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required extension <strong>' . $key . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }
        }

        return $strContent;
    }

}

?>