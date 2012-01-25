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
class CtoCommunication extends Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelten pattern
    protected static $instance = null;
    // Vars
    protected $strConnectionID;
    protected $strConnectionKey;
    protected $strUrl;
    protected $strUrlGet;
    protected $strApiKey;
    protected $strHTTPUser;
    protected $strHTTPPassword;
    protected $arrCookies;
    protected $arrRpcList;
    protected $arrError;
    protected $arrNullFields;
    protected $mixOutput;
    // Objects
    protected $objCodifyengine;
    protected $objCodifyengineBasic;
    protected $objDebug;
    // Config
    /**
     * Time in seconds for handshake timeout.
     * @var int 
     */
    protected $intHandshakeTimeout = 1200;
    protected $intMaxResponseLength;
    protected $arrResponses = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );

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
        $this->objCodifyengineBasic = CtoComCodifyengineFactory::getEngine("aes");
        $this->objDebug = CtoComDebug::getInstance();

        $this->arrRpcList = $GLOBALS["CTOCOM_FUNCTIONS"];
        $this->arrError = array();
        $this->arrNullFields = array();

        if (empty($GLOBALS['TL_CONFIG']['ctoCom_responseLength']) || $GLOBALS['TL_CONFIG']['ctoCom_responseLength'] < 10000)
        {
            $this->intMaxResponseLength = 40000;
        }
        else
        {
            $this->intMaxResponseLength = $GLOBALS['TL_CONFIG']['ctoCom_responseLength'];
        }
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

        // Load Session information
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (is_array($arrPool) && key_exists(md5($strUrl), $arrPool))
        {
            $this->strConnectionID = $arrPool[md5($strUrl)]["id"];
            $this->strConnectionKey = $arrPool[md5($strUrl)]["key"];
        }
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
    public function setClient($strUrl, $strCodifyEngine = "aes")
    {
        // Set client
        $this->strUrl = $strUrl;

        // Set codify
        $this->setCodifyengine($strCodifyEngine);

        // Load Session information
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (is_array($arrPool) && key_exists(md5($strUrl), $arrPool))
        {
            $this->strConnectionID = $arrPool[md5($strUrl)]["id"];
            $this->strConnectionKey = $arrPool[md5($strUrl)]["key"];
        }
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

    /**
     * Set a username for http auth
     * 
     * @param string $strHTTPUser 
     */
    public function setHTTPUser($strHTTPUser)
    {
        $this->strHTTPUser = $strHTTPUser;
    }

    /**
     * Set a password for http auth
     * 
     * @param string $strHTTPPassword 
     */
    public function setHTTPPassword($strHTTPPassword)
    {
        $this->strHTTPPassword = $strHTTPPassword;
    }

    /**
     * Set the Connection ID
     * 
     * @param stirng $strConnectionID 
     */
    public function setConnectionID($strConnectionID)
    {
        $this->strConnectionID = $strConnectionID;

        // Save information in Session
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (!is_array($arrPool))
        {
            $arrPool = array();
        }
        $arrPool[md5($this->strUrl)]["id"] = $strConnectionID;
        $this->Session->set("CTOCOM_ConnectionPool", $arrPool);
    }

    /**
     * Save the connection key
     * 
     * @param string $strConnectionKey 
     */
    public function setConnectionKey($strConnectionKey)
    {
        $this->strConnectionKey = $strConnectionKey;

        // Save information in Session
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (!is_array($arrPool))
        {
            $arrPool = array();
        }
        $arrPool[md5($this->strUrl)]["key"] = $strConnectionKey;
        $this->Session->set("CTOCOM_ConnectionPool", $arrPool);
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

    /**
     * Get username for http auth.
     * 
     * @return string 
     */
    public function getHTTPUser()
    {
        return $this->strHTTPUser;
    }

    /**
     * Get password for http auth.
     * 
     * @return string 
     */
    public function getHTTPPassword()
    {
        return $this->strHTTPPassword;
    }

    /**
     * Get the Connection ID
     * 
     * @return string 
     */
    public function getConnectionID()
    {
        return $this->strConnectionID;
    }

    /**
     * Return the connection key
     * 
     * @return string 
     */
    public function getConnectionKey()
    {
        return $this->strConnectionKey;
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
        $strMeasureID1 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $rpc);

        $this->strUrlGet = "";

        $strMeasureID2 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "Init. System");

        // Check if everything is set
        if ($this->strApiKey == "" || $this->strApiKey == null)
        {
            throw new Exception("The API Key is not set. Please set first API Key.");
        }

        if ($this->strUrl == "" || $this->strUrl == null)
        {
            throw new Exception("There is no URL set for connection. Please set first the url.");
        }

        // Get Session information for condify key
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (!empty($this->strConnectionKey) && !in_array($rpc, array("CTOCOM_HELLO", "CTOCOM_START_HANDSHAKE", "CTOCOM_CHECK_HANDSHAKE")))
        {
            // Set Key for codifyengine
            $this->objCodifyengineBasic->setKey($this->strConnectionKey);
            $this->objCodifyengine->setKey($this->strConnectionKey);
        }
        else
        {
            // Set Key for codifyengine
            $this->objCodifyengineBasic->setKey($this->strApiKey);
            $this->objCodifyengine->setKey($this->strApiKey);
        }

        // Add Get Parameter
        $strCryptApiKey = $this->objCodifyengineBasic->Encrypt($rpc . "@|@" . $this->strApiKey);
        $strCryptApiKey = base64_encode($strCryptApiKey);

        if (strpos($this->strUrl, "?") !== FALSE)
        {
            $this->strUrlGet .= "&engine=" . $this->objCodifyengine->getName() . "&act=" . $rpc . "&apikey=" . urlencode($strCryptApiKey) . "&con=" . $this->strConnectionID;
        }
        else
        {
            $this->strUrlGet .= "?engine=" . $this->objCodifyengine->getName() . "&act=" . $rpc . "&apikey=" . urlencode($strCryptApiKey) . "&con=" . $this->strConnectionID;
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID2);

        $strMeasureID3 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "Init. Request");

        // New Request
        $objRequest = new RequestExtended();
        $objRequest->acceptgzip = 0;
        $objRequest->acceptdeflate = 0;

        if (strlen($this->strHTTPUser) != 0 || strlen($this->strHTTPPassword) != 0)
        {
            $objRequest->username = $this->strHTTPUser;
            $objRequest->password = $this->strHTTPPassword;
        }

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
                $strMeasureID4 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "Init. MultipartFormdata - " . $value["name"]);

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
                    // serliaze/encrypt/compress/base64 function                    
                    $strValue = serialize(array("data" => $value["value"]));
                    $intStrlenSer = strlen($strValue);

                    $strValue = $this->objCodifyengine->Encrypt($strValue);
                    $intStrlenCod = strlen($strValue);

                    //$strValue = bzcompress ($strValue);
                    $strValue = gzcompress($strValue);
                    $intStrlenCom = strlen($strValue);

                    $strValue = base64_encode($strValue);
                    $intStrlenB64 = strlen($strValue);

                    $this->objDebug->addDebug("Post Data - " . $value["name"], vsprintf("Ser: %s | Cod: %s | Com: %s | B64: %s", array($intStrlenSer, $intStrlenCod, $intStrlenCom, $intStrlenB64)));

                    // Set field
                    $objMultipartFormdata->setField($value["name"], $strValue);
                }

                $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID4);
            }

            // Create HTTP Data code
            $objRequest->data = $objMultipartFormdata->compile();

            // Set typ and mime typ
            $objRequest->method = "POST";
            $objRequest->datamime = $objMultipartFormdata->getContentTypeHeader();
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID3);

        $strMeasureID5 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "Start sending.");

        // Send new request
        $objRequest->send($this->strUrl . $this->strUrlGet);

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID5);

        $response = $objRequest->response;

        // Debug
        $this->objDebug->addDebug("Request", substr($objRequest->request, 0, 2000));

        // Build response Header informations
        $strResponseHeader = "";
        foreach ($objRequest->headers as $keyHeader => $valueHeader)
        {
            $strResponseHeader .= $keyHeader . ": " . $valueHeader . "\n";
        }
        $this->objDebug->addDebug("Response", $strResponseHeader . "\n\n" . substr($response, 0, 2000));

        // Check if we have time out
        if ($objRequest->timedOut)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("Sorry we have a time out. Please try again.");
        }

        // Check if everything is okay for connection
        if ($objRequest->hasError())
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("Error on transmission, with message: " . $objRequest->code . " " . $objRequest->error);
        }

        // Check if everything is okay for connection
        if ($objRequest->code != 200)
        {
            // Check if we have time out
            if (empty($objRequest->code))
            {
                $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
                throw new Exception("Sorry we have a time out. Please try again.");
            }
            else
            {
                $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
                throw new Exception("Error on transmission, with message: " . $objRequest->code . " - " . $this->arrResponses[$objRequest->code]);
            }
        }

        // Check if we have a response
        if (strlen($response) == 0)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("We got a blank response from server.");
        }

        // Check for "Fatal error" on client side
        if (strpos($response, "Fatal error") !== FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("We got a Fatal error on client site. " . $response);
        }

        // Check for "Warning" on client side
        if (strpos($response, "Warning") !== FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);

            $intStart = stripos($response, "<strong>Warning</strong>:");
            $intEnd = stripos($response, "on line");

            throw new Exception("We got a Warning on client site.<br /><br />" . substr($response, $intStart, $intEnd - $intStart));
        }

        // Check for start and end tag
        if (strpos($response, "<|@|") === FALSE || strpos($response, "|@|>") === FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("Could not find start or endtag from response.");
        }

        // Rebuild original msg
        $mixContent = $response;

        // Find position of start/end - tag
        $intStart = intval(strpos($mixContent, "<|@|") + 4);
        $intLength = intval(strpos($mixContent, "|@|>") - $intStart);

        $mixContent = substr($mixContent, $intStart, $intLength);
        $mixContent = base64_decode($mixContent);
        //$mixContent = bzdecompress($mixContent);
        $mixContent = @gzuncompress($mixContent);

        // Check if uncompress works
        if ($mixContent === FALSE)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("Error on uncompressing the response. Maybe wrong API-Key or ctoCom version.");
        }

        // Decrypt response
        $mixContent = $this->objCodifyengine->Decrypt($mixContent);

        $this->objDebug->addDebug("Response Decrypte", substr($mixContent, 0, 2000));

        // Deserialize response
        $mixContent = deserialize($mixContent);

        // Check if we have a array
        if (is_array($mixContent) == false)
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            throw new Exception("Response is not an array. Maybe wrong API-Key or cryptionengine.");
        }

        // Clean array
        $mixContent = $this->cleanUp($mixContent);

        // Check if client says "Everthing okay"
        if ($mixContent["success"] == 1)
        {
            if ($mixContent["splitcontent"] == true)
            {
                try
                {
                    $mixContent["response"] = $this->rebuildSplitcontent($mixContent["splitname"], $mixContent["splitcount"]);
                }
                catch (Exception $exc)
                {
                    $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
                    throw $exc;
                }
            }

            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
            return $mixContent["response"];
        }
        else
        {
            $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);

            $string = vsprintf("There was an error on client site with message:<br/><br/>%s<br/><br/>RPC Call: %s | Class: %s | Function: %s", array(
                nl2br($mixContent["error"][0]["msg"]),
                $mixContent["error"][0]["rpc"],
                (strlen($mixContent["error"][0]["class"]) != 0) ? $mixContent["error"][0]["class"] : " - ",
                (strlen($mixContent["error"][0]["function"]) != 0) ? $mixContent["error"][0]["function"] : " - ",
                    )
            );

            throw new Exception($string);
        }

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $strMeasureID1);
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

    protected function rebuildSplitcontent($strSplitname, $intSplitCount)
    {
        $mixContent = "";

        for ($i = 0; $i < $intSplitCount; $i++)
        {
            @set_time_limit(60);

            $arrData = array(
                array(
                    "name" => "splitname",
                    "value" => $strSplitname,
                ),
                array(
                    "name" => "splitcount",
                    "value" => $i,
                )
            );

            $mixContent .= $this->runServer("CTOCOM_GET_RESPONSE_PART", $arrData);
        }

        // Check for start and end tag
        if (strpos($mixContent, "<|@|") === FALSE || strpos($mixContent, "|@|>") === FALSE)
        {
            throw new Exception("Could not find start or endtag from response. Error in split content.");
        }

        // Find position of start/end - tag
        $intStart = intval(strpos($mixContent, "<|@|") + 4);
        $intLength = intval(strpos($mixContent, "|@|>") - $intStart);

        $mixContent = substr($mixContent, $intStart, $intLength);
        $mixContent = base64_decode($mixContent);
        //$mixContent = bzdecompress($mixContent);
        $mixContent = @gzuncompress($mixContent);

        // Check if uncopress works
        if ($mixContent === FALSE)
        {
            throw new Exception("Error on uncompressing the response. Maybe wrong API-Key or ctoCom version.");
        }

        // Decrypt response
        $mixContent = $this->objCodifyengine->Decrypt($mixContent);

        $this->objDebug->addDebug("Response Decrypte", substr($mixContent, 0, 2000));

        // Deserialize response
        $mixContent = deserialize($mixContent);

        // Check if we have a array
        if (is_array($mixContent) == false)
        {
            throw new Exception("Response is not an array. Maybe wrong API-Key or cryptionengine.");
        }

        // Clean array
        $mixContent = $this->cleanUp($mixContent);

        // Check if client says "Everthing okay"
        if ($mixContent["success"] == 1)
        {
            return $mixContent["response"];
        }
        else
        {
            $string = vsprintf("There was an error on client site with message:<br/><br/>%s<br/><br/>RPC Call: %s | Class: %s | Function: %s", array(
                nl2br($mixContent["error"][0]["msg"]),
                $mixContent["error"][0]["rpc"],
                (strlen($mixContent["error"][0]["class"]) != 0) ? $mixContent["error"][0]["class"] : " - ",
                (strlen($mixContent["error"][0]["function"]) != 0) ? $mixContent["error"][0]["function"] : " - ",
                    )
            );

            throw new Exception($string);
        }
    }

    /**
     * Run the communication as client
     *
     * @return void
     */
    public function runClient()
    {
        // Start measurement
        $intMeasurement1 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__, "RPC: " . $this->Input->get("act"));

        // If we have a ping, just do nothing
        if ($this->Input->get("act") == "ping")
        {
            exit();
        }

        // Set codify key ------------------------------------------------------
        
        // Check if we have a incomming connection for handshake
        if (in_array($this->Input->get("act"), array("CTOCOM_HELLO", "CTOCOM_START_HANDSHAKE", "CTOCOM_CHECK_HANDSHAKE")))
        {
            $this->objCodifyengine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
            $this->objCodifyengineBasic->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
            $strCodifyKey = $GLOBALS['TL_CONFIG']['ctoCom_APIKey'];
        }
        else
        {
            // Use the private key from connection pool
            if (strlen($this->Input->get("con")) != 0)
            {
                $arrConnections = $this->Database->prepare("SELECT * FROM tl_ctocom_cache WHERE uid=?")
                        ->execute($this->Input->get("con"))
                        ->fetchAllAssoc();

                // Check if we have some data
                if (count($arrConnections) == 0)
                {
                    $this->log(vsprintf("Call from %s with a unknown connection ID.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    exit();
                }

                if ($arrConnections[0]["tstamp"] + $this->intHandshakeTimeout < time())
                {
                    $this->log(vsprintf("Call from %s without a expired connection ID.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    exit();
                }

                $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                        ->set(array("tstamp" => time()))
                        ->execute($this->Input->get("con"));

                $this->objCodifyengineBasic->setKey($arrConnections[0]["shared_secret_key"]);
                $this->objCodifyengine->setKey($arrConnections[0]["shared_secret_key"]);
                $strCodifyKey = $arrConnections[0]["shared_secret_key"];
            }
            else
            {
                $this->log(vsprintf("Call from %s without a connection ID.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                exit();
            }
        }

        // API Key - Check -----------------------------------------------------

        if (strlen($this->Input->get("apikey")) == 0)
        {
            $this->log(vsprintf("Call from %s without a API Key.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            exit();
        }

        $mixVar = $this->objCodifyengineBasic->Decrypt(base64_decode($this->Input->get("apikey", true)));
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

        if (empty($GLOBALS['TL_LANGUAGE']))
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
                $this->objCodifyengine->setKey($strCodifyKey);
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
            $this->setCodifyengine("aes");
            $this->objCodifyengine->setKey($strCodifyKey);
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
                                $mixPost = $this->Input->postRaw($key);
                                $mixPost = base64_decode($mixPost);
                                //$mixPost = bzdecompress($mixPost);
                                $mixPost = gzuncompress($mixPost);
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
                                    if (in_array($value, $this->arrNullFields))
                                    {
                                        $arrParameter[$value] = NULL;
                                    }
                                    else
                                    {
                                        $arrParameter[$value] = $this->Input->postRaw($value);
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
                $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $intMeasurement1);
                return $this->generateOutput();
            }

            try
            {
                $intMeasurement2 = $this->objDebug->startMeasurement($this->arrRpcList[$mixRPCCall]["class"], $this->arrRpcList[$mixRPCCall]["function"]);

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

                $this->objDebug->stopMeasurement($this->arrRpcList[$mixRPCCall]["class"], $this->arrRpcList[$mixRPCCall]["function"], $intMeasurement2);
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

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $intMeasurement1);
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
        $intMeasurement1 = $this->objDebug->startMeasurement(__CLASS__, __FUNCTION__);

        if (count($this->arrError) == 0)
        {
            $mixOutput = serialize(array(
                "success" => 1,
                "error" => "",
                "response" => $this->mixOutput,
                "splitcontent" => false,
                "splitcount" => 0,
                "splitname" => ""
                    ));
        }
        else
        {
            $mixOutput = serialize(array(
                "success" => 0,
                "error" => $this->arrError,
                "response" => "",
                "splitcontent" => false,
                "splitcount" => 0,
                "splitname" => ""
                    ));
        }

        $mixOutput = $this->objCodifyengine->Encrypt($mixOutput);

        $this->objDebug->stopMeasurement(__CLASS__, __FUNCTION__, $intMeasurement1);

        //$strOutput = bzcompress($strOutput);
        $mixOutput = gzcompress($mixOutput);
        $mixOutput = base64_encode($mixOutput);
        $mixOutput = "<|@|" . $mixOutput . "|@|>";

        //$this->objDebug->addDebug("Response", $mixOutput);

        // Check if we have a big output and split it 
        if (strlen($mixOutput) > $this->intMaxResponseLength)
        {
            $mixOutput = str_split($mixOutput, (int) ($this->intMaxResponseLength * 0.5));

            $strFileName = md5(time()) . md5(rand(0, 65000)) . ".ctoComPart";
            $intCountPart = count($mixOutput);

            foreach ($mixOutput as $keyOutput => $valueOutput)
            {
                $objFile = new File("system/tmp/" . $keyOutput . "_" . $strFileName);
                $objFile->write($valueOutput);
                $objFile->close();
            }

            $mixOutput = serialize(array(
                "success" => 1,
                "error" => "",
                "response" => "",
                "splitcontent" => true,
                "splitcount" => $intCountPart,
                "splitname" => $strFileName
                    ));

            $mixOutput = $this->objCodifyengine->Encrypt($mixOutput);

            //$strOutput = bzcompress($strOutput);
            $mixOutput = gzcompress($mixOutput);
            $mixOutput = base64_encode($mixOutput);
            $mixOutput = "<|@|" . $mixOutput . "|@|>";
        }

        // Clean output buffer
        while (@ob_end_clean());

        // Echo response
        echo($mixOutput);
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
            foreach ($arrRequiredExtensions as $key => $val)
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

    /* --------------------------------------------------------------------------
     * Start Connection - Handshake
     */

    public function startConnection()
    {
        // Imoprt
        require_once TL_ROOT . '/plugins/DiffieHellman/DiffieHellman.php';

        // Say "Hello" for connection id
        $strMyNumber = $this->runServer("CTOCOM_HELLO");
        $this->setConnectionID($strMyNumber);

        // Start key handshake
        $arrDiffieHellman = $this->runServer("CTOCOM_START_HANDSHAKE");

        $objLastException = null;

        for ($i = 0; $i < 10; $i++)
        {
            // Create random private key.
            $intPrivateLength = rand(strlen($arrDiffieHellman["generator"]), strlen($arrDiffieHellman["prime"]) - 2);
            $strPrivate = rand(1, 9);

            for ($ii = 0; $ii < $intPrivateLength; $ii++)
            {
                $strPrivate .= rand(0, 9);
            }

            if (!preg_match("/^\d+$/", $strPrivate))
            {
                $objLastException = new Exception("Private key is not a natural number");
                continue;
            }

            try
            {
                // Start key gen
                $objDiffieHellman = new Crypt_DiffieHellman($arrDiffieHellman["prime"], $arrDiffieHellman["generator"], $strPrivate);
                $objDiffieHellman->generateKeys();

                // Send public key for check 
                $arrData = array(
                    array(
                        "name" => "key",
                        "value" => $objDiffieHellman->getPublicKey(),
                    )
                );
            }
            catch (Exception $exc)
            {
                $objLastException = $exc;
                continue;
            }

            $objLastException = null;
            break;
        }

        if ($objLastException != null)
        {
            throw $objLastException;
        }

        $strPublicKey = $this->runServer("CTOCOM_CHECK_HANDSHAKE", $arrData, true);

        if ($arrDiffieHellman["public_key"] != $strPublicKey)
        {
            throw new Exception("Error for handshake. Public-Key from client isn't valide.");
        }

        $strSecretKey = $objDiffieHellman->computeSecretKey($arrDiffieHellman["public_key"])
                ->getSharedSecretKey();

        // Save and end 
        $this->setConnectionKey($strSecretKey);
    }

    public function stopConnection()
    {
        // Close connection
        $this->runServer("CTOCOM_BYE");

        // Reset Session information
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (is_array($arrPool) && key_exists(md5($this->strUrl), $arrPool))
        {
            unset($arrPool[md5($this->strUrl)]);
        }
        $this->Session->set("CTOCOM_ConnectionPool", $arrPool);
    }

}

?>