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
 *  CtoCommunication
 * 
 *  Core Features:
 *      - I/O Interface System for Accept and Content-Typ
 *      - Codify Interface System
 *      - Splitcontent Managment System
 *      - Handshake for private Key over DiffieHellman
 *      - Connection ID`s for each session
 *      - Error Handling
 *      - RPC Functions
 * 
 *  Bugs:
 *      - Sometimes the DiffieHellman throw a "Not a natural number" exception
 *      - If Memory-Limit hits maximum on client, you will get a 500 as HTTP response
 *      - If something goes wrong on client, you will get a 500 as HTTP response
 * 
 *  Warnings:
 *      - Files are not codify
 *      - GET is not codify
 *      - Don't send files over 50MB, it would take a long time to send them
 */
class CtoCommunication extends \Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // Singelten pattern
    protected static $instance            = null;
    // Vars
    protected $strConnectionID;     // Id of connection
    protected $strConnectionKey;    // Used Key [API-Key|DiffieHellman]
    protected $strUrl;              // Base url
    protected $strUrlGet;           // GET Parameter for the call
    protected $strApiKey;           // API Key
    protected $strHTTPUser;         // HTTP Auth name
    protected $strHTTPPassword;     // HTTP Auth password
    protected $strIOEngine;         // Main Input/Output Engine
    protected $arrCookies;          // Cookies - not used
    protected $arrRpcList;          // A list with all RPC
    protected $mixOutput;           // Output @todo - Check if we need this
    // Config
    /**
     * Time in seconds for handshake timeout.
     * @var int 
     */
    protected $intMaxResponseLength;
    protected $intHandshakeTimeout = 1200;

    /**
     * @var CtoComCodifyengineAbstract 
     */
    protected $objCodifyengine;

    /**
     * @var CtoComCodifyengineAbstract 
     */
    protected $objCodifyengineBasic;

    /**
     * @var CtoComIOInterface 
     */
    protected $objIOEngine;

    /**
     * @var CtoComDebug 
     */
    protected $objDebug;

    /**
     * @var CtoComContainerError 
     */
    protected $objError;

    /* -------------------------------------------------------------------------
     * Core
     */

    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();

        $this->objCodifyengineBasic = \CtoComCodifyengineFactory::getEngine("aes");
        $this->objDebug = \CtoComDebug::getInstance();
        $this->objError = false;

        $this->arrRpcList = $GLOBALS["CTOCOM_FUNCTIONS"];

        $this->setIOEngine("default");
        $this->setCodifyengine();

        if (empty($GLOBALS['TL_CONFIG']['ctoCom_responseLength']) || $GLOBALS['TL_CONFIG']['ctoCom_responseLength'] < 10000)
        {
            $this->intMaxResponseLength = -1;
        }
        else
        {
            $this->intMaxResponseLength = $GLOBALS['TL_CONFIG']['ctoCom_responseLength'];
        }
        
        require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman.php';
        require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman/Exception.php';
        require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman/Math/Exception.php';
    }

    /**
     * Singelton Pattern
     * 
     * @return \CtoCommunication 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new \CtoCommunication();
        }

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * Getter / Setter
     */

    //- Setter -------------------

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
    public function setClient($strUrl, $strCodifyEngine = "aes", $strIOEngine = "default")
    {
        // Set client
        $this->strUrl = $strUrl;

        // Set codify
        $this->setCodifyengine($strCodifyEngine);

        // Set I/O engine
        $this->setIOEngine($strIOEngine);

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
        $this->objCodifyengine = \CtoComCodifyengineFactory::getEngine($strName);
    }

    /**
     * Change I/O enginge
     * 
     * @param string $strName 
     */
    public function setIOEngine($strName = 'default')
    {
        $this->objIOEngine = \CtoComIOFactory::getEngine($strName);
        $this->strIOEngine = $strName;
    }

    /**
     * Change I/O enginge
     * 
     * @param string $strName 
     */
    public function setIOEngineByContentTyp($strName = 'text/html')
    {
        $this->setIOEngine(\CtoComIOFactory::getEngingenameForContentType($strName));
    }

    /**
     * Change I/O enginge
     * 
     * @param string $strName 
     */
    public function setIOEngineByAccept($strName = 'text/html')
    {
        $this->setIOEngine(\CtoComIOFactory::getEngingenameForAccept($strName));
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

    /**
     * Save the connection key
     * 
     * @param string $strConnectionKey 
     */
    public function setConnectionBasicCodify($strCodify)
    {
        // Set the new engine
        $this->objCodifyengineBasic = \CtoComCodifyengineFactory::getEngine($strCodify);

        // Save information in Session
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");

        if (!is_array($arrPool))
        {
            $arrPool = array();
        }

        if (empty($this->strUrl))
        {
            throw new \RuntimeException("Client or client-url missing. Could not set new basic codifyengine.");
        }

        $arrPool[md5($this->strUrl)]["codifyengine"] = $strCodify;
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
        /* ---------------------------------------------------------------------
         * Check if everything is set / Init
         */

        if ($this->strApiKey == "" || $this->strApiKey == null)
        {
            throw new \RuntimeException("The API Key is not set. Please set first API Key.");
        }

        if ($this->strUrl == "" || $this->strUrl == null)
        {
            throw new \RuntimeException("There is no URL set for connection. Please set first the url.");
        }

        // Reset GET parameter
        $this->strUrlGet = "";

        /* ---------------------------------------------------------------------
         * Check if we need another core codify engine
         */

        // Get Session information for condify key & engine        
        $arrPoolInformation = $this->Session->get("CTOCOM_ConnectionPool");

        if (is_array($arrPoolInformation) && key_exists(md5($this->strUrl), $arrPoolInformation) && key_exists("codifyengine", $arrPoolInformation[md5($this->strUrl)]))
        {
            $this->objCodifyengineBasic = \CtoComCodifyengineFactory::getEngine($arrPoolInformation[md5($this->strUrl)]["codifyengine"]);

            if ($this->objCodifyengine->getName() == "aes")
            {
                $this->objCodifyengine = \CtoComCodifyengineFactory::getEngine($arrPoolInformation[md5($this->strUrl)]["codifyengine"]);
            }
        }

        if (empty($this->strConnectionKey) || in_array($rpc, array("CTOCOM_HELLO", "CTOCOM_START_HANDSHAKE", "CTOCOM_CHECK_HANDSHAKE", "CTOCOM_VERSION")))
        {
            $this->objCodifyengineBasic->setKey($this->strApiKey);
            $this->objCodifyengine->setKey($this->strApiKey);
        }
        else
        {
            $this->objCodifyengineBasic->setKey($this->strConnectionKey);
            $this->objCodifyengine->setKey($this->strConnectionKey);
        }

        /* ---------------------------------------------------------------------
         * Add Get Parameter
         */

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

        /* ---------------------------------------------------------------------
         * New Request
         */

        $objRequest = new \RequestExtended();
        $objRequest->acceptgzip = 0;
        $objRequest->acceptdeflate = 0;
        $objRequest->useragent .= "Mozilla/5.0 (compatible; CyberSpectrum RequestExtended on Contao ".VERSION.".".BUILD."; rv:1.0); CtoCommunication RPC (ctoComV" . $GLOBALS["CTOCOM_VERSION"] . ")";

        // Set http auth
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
            $objMultipartFormdata = new \MultipartFormdata();
            foreach ($arrData as $key => $value)
            {
                if (isset($value["filename"]) == true && strlen($value["filename"]) != 0)
                {
                    // Set field for file
                    if (!$objMultipartFormdata->setFileField($value["name"], $value["filepath"], $value["mime"]))
                    {
                        throw new \RuntimeException("Could not add file to postheader.");
                    }
                }
                else
                {
                    // Set field
                    $objMultipartFormdata->setField($value["name"], $this->objIOEngine->OutputPost($value["value"], $this->objCodifyengine));
                }
            }

            // Create HTTP Data code
            $objRequest->data = $objMultipartFormdata->compile();

            // Set typ and mime typ
            $objRequest->method = "POST";
            $objRequest->datamime = $objMultipartFormdata->getContentTypeHeader();
        }

        $booRequestResult = $objRequest->send($this->strUrl . $this->strUrlGet);

        // Send new request
        if ($booRequestResult == false || $objRequest->hasError())
        {
            throw new \RuntimeException("Error on transmission, with message: " . $objRequest->code . " " . $objRequest->error);
        }

        $this->objDebug->addDebug("Request", substr($objRequest->request, 0, 2048));

        /* ---------------------------------------------------------------------
         * Check response for errors
         */

        // Build response Header informations        
        $arrHeaderKeys = array();
        $strResponseHeader = "";

        foreach ($objRequest->getResponseHeaderKeys() as $keyHeader)
        {
            $arrHeaderKeys[] = strtolower($keyHeader);
        }

        $arrHeaderKeys = array_unique($arrHeaderKeys);

        foreach ($arrHeaderKeys as $keyHeader)
        {
            $strResponseHeader .= $keyHeader . ": " . $objRequest->getResponseHeader($keyHeader) . "\n";
        }

        $this->objDebug->addDebug("Response", $strResponseHeader . "\n\n" . substr($objRequest->response, 0, 2048));

        // Check if we have time out
        if ($objRequest->timedOut)
        {
            throw new \RuntimeException("Sorry we have a time out. Please try again.");
        }

        // Check if we have a response
        if (strlen($objRequest->response) == 0)
        {
            throw new \RuntimeException("We got a blank response from server.");
        }

        // Check for "Fatal error" on client side
        if (strpos($objRequest->response, "Fatal error") !== FALSE)
        {
            throw new \RuntimeException("We got a Fatal error on client site. " . $objRequest->response);
        }

        // Check for "Warning" on client side
        if (strpos($objRequest->response, "Warning") !== FALSE)
        {
            $intStart = stripos($response, "<strong>Warning</strong>:");
            $intEnd   = stripos($response, "on line");

            throw new \RuntimeException("We got a Warning on client site.<br /><br />" . substr($objRequest->response, $intStart, $intEnd - $intStart));
        }

        /* ---------------------------------------------------------------------
         * ctoCom I/O System 
         */

        $strContentType = $objRequest->headers['Content-Type'];
        $strContentType = preg_replace("/;.*$/", "", $strContentType);

        // Search a engine
        $objIOEngine = \CtoComIOFactory::getEngingeForContentType($strContentType);

        // Check if we have found one
        if ($objIOEngine == false)
        {
            throw new \RuntimeException("No I/O class found for " . $strContentType);
        }

        // Parse response
        $objResponse = $objIOEngine->InputResponse($objRequest->response, $this->objCodifyengine);

        // Write Debug msg
        $strDebug = "";
        $strDebug .= "Success:     ";
        $strDebug .= ($objResponse->isSuccess()) ? "true" : "false";
        $strDebug .= "\n";
        if ($objResponse->isSplitcontent() == true)
        {
            $strDebug .= "Split:       " . $objResponse->isSplitcontent();
            $strDebug .= "\n";
            $strDebug .= "Splitinfo:   " . "Count - " . $objResponse->getSplitcount() . " Name - " . $objResponse->getSplitname();
            $strDebug .= "\n";
        }
        if ($objResponse->getError() != null && is_object($objResponse->getError()))
        {
            $strDebug .= "Error:       " . $objResponse->getError()->getMessage();
            $strDebug .= "\n";
            $strDebug .= "Error RPC:   " . $objResponse->getError()->getRPC();
            $strDebug .= "\n";
            $strDebug .= "Error Class: " . $objResponse->getError()->getClass();
            $strDebug .= "\n";
            $strDebug .= "Error Func.: " . $objResponse->getError()->getFunction();
            $strDebug .= "\n";            
        }
        $strDebug .= "Response:    " . substr(json_encode($objResponse->getResponse()), 0, 2048);

        $this->objDebug->addDebug("Response Object", $strDebug);

        /* ---------------------------------------------------------------------
         * Check Response
         */

        // Check if client says "Everthing okay"
        if ($objResponse->isSuccess())
        {
            if ($objResponse->isSplitcontent() == true)
            {
                try
                {
                    $objResponse->setResponse($this->rebuildSplitcontent($objResponse->getSplitname(), $objResponse->getSplitcount()));
                }
                catch (\RuntimeException $exc)
                {
                    throw $exc;
                }
            }

            return $objResponse->getResponse();
        }
        else
        {
            if ($this->getDebug() == true)
            {
                $string = vsprintf($GLOBALS['TL_LANG']['ERR']['client_error'] . ":<br />%s<br /><br />RPC Call: %s | Class: %s | Function: %s", array(
                    nl2br($objResponse->getError()->getMessage()),
                    $objResponse->getError()->getRPC(),
                    (strlen($objResponse->getError()->getClass()) != 0) ? $objResponse->getError()->getClass() : " - ",
                    (strlen($objResponse->getError()->getFunction()) != 0) ? $objResponse->getError()->getFunction() : " - ",
                        )
                );
            }
            else
            {
                if ($objResponse->getError()->getRPC() == "")
                {
                    $string = "There was an unknown error on client site.";
                }
                else
                {
                    $string = vsprintf($GLOBALS['TL_LANG']['ERR']['client_error'] . ":<br />%s<br /><br />RPC Call: %s", array(
                        nl2br($objResponse->getError()->getMessage()),
                        $objResponse->getError()->getRPC(),
                            )
                    );
                }
            }

            throw new \RuntimeException($string);
        }
    }

    protected function rebuildSplitcontent($strSplitname, $intSplitCount)
    {
        $mixContent = "";

        for ($i = 0; $i < $intSplitCount; $i++)
        {
            @set_time_limit(300);

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

        $objResponse = $this->objIOEngine->InputResponse($mixContent, $this->objCodifyengine);

        // Check if client says "Everything ok"
        if ($objResponse->isSuccess() == true)
        {
            return $objResponse->getResponse();
        }
        else
        {
            $string = vsprintf($GLOBALS['TL_LANG']['ERR']['client_error'] . ":<br />%s<br /><br />RPC Call: %s", array(
                nl2br($objResponse->getError()->getMessage()),
                $objResponse->getError()->getRPC(),
                    )
            );

            throw new \RuntimeException($string);
        }
    }

    /**
     * Run the communication as client
     *
     * @return void
     */
    public function runClient()
    {
        // If we have a ping, just do nothing
        if ($this->Input->get("act") == "ping")
        {
            // Clean output buffer
            while (@ob_end_clean());
            exit();
        }

        /* ---------------------------------------------------------------------
         * Check if we have a old AES or a new AES with IV.
         * Set codifyengine keys.
         * Check the connection ID and refresh/delete it.
         */

        // Check if IV was send, when send use the new AES else the old one.
        try
        {
            if (preg_match("/.*\|@\|.*/", base64_decode($this->Input->get("apikey", true))))
            {
                $this->objCodifyengineBasic = \CtoComCodifyengineFactory::getEngine("aes");

                if ($this->Input->get("engine") == "aes")
                {
                    $this->setCodifyengine($this->Input->get("aes"));
                }
                else
                {
                    $this->setCodifyengine($this->Input->get("engine"));
                }
            }
            else
            {
                $this->objCodifyengineBasic = \CtoComCodifyengineFactory::getEngine("aeso");

                if ($this->Input->get("engine") == "aes")
                {
                    $this->setCodifyengine("aeso");
                }
                else
                {
                    $this->setCodifyengine($this->Input->get("engine"));
                }
            }
        }
        catch (\RuntimeException $exc)
        {
            $this->log("Try to load the engine for ctoCommunication with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            // Clean output buffer
            while (@ob_end_clean());
            exit();
        }

        // Check if we have a incomming connection for handshake
        if (in_array($this->Input->get("act"), array("CTOCOM_HELLO", "CTOCOM_START_HANDSHAKE", "CTOCOM_CHECK_HANDSHAKE", "CTOCOM_VERSION")))
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
                // Check if we have some data
                $arrConnections = $this->Database->prepare("SELECT * FROM tl_ctocom_cache WHERE uid=?")
                        ->execute($this->Input->get("con"))
                        ->fetchAllAssoc();

                if (count($arrConnections) == 0)
                {
                    $this->log(vsprintf("Call from %s with a unknown connection ID.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    // Clean output buffer
                    while (@ob_end_clean());
                    exit();
                }

                // Check if time out isn't reached.
                if ($arrConnections[0]["tstamp"] + $this->intHandshakeTimeout < time())
                {
                    $this->Database->prepare("DELETE FROM tl_ctocom_cache WHERE uid=?")
                            ->execute($this->Input->get("con"));

                    $this->log(vsprintf("Call from %s with a expired connection ID.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
                    // Clean output buffer
                    while (@ob_end_clean());
                    exit();
                }

                // Reset timestamp
                $this->Database->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                        ->set(array("tstamp" => time()))
                        ->execute($this->Input->get("con"));

                // Set codify key from database
                $this->objCodifyengineBasic->setKey($arrConnections[0]["shared_secret_key"]);
                $this->objCodifyengine->setKey($arrConnections[0]["shared_secret_key"]);
                $strCodifyKey = $arrConnections[0]["shared_secret_key"];
            }
            else
            {
                $this->log(vsprintf("Call from %s without a connection ID.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

                // Clean output buffer
                while (@ob_end_clean());
                exit();
            }
        }

        /* ---------------------------------------------------------------------
         * Check the API key.
         * Check if the API Key was send. 
         * Check if the API key contains the RPC Call and the API Key from this
         * Contao Version. 
         */

        // Check if a API-Key was send
        if (strlen($this->Input->get("apikey")) == 0)
        {
            $this->log(vsprintf("Call from %s without a API Key.", $this->Environment->ip), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

            // Clean output buffer
            while (@ob_end_clean());
            exit();
        }

        // Check RPC Call from get and the RPC Call from API-Key
        $mixVar    = $this->objCodifyengineBasic->Decrypt(base64_decode($this->Input->get("apikey", true)));
        $mixVar    = trimsplit("@\|@", $mixVar);
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

            // Clean output buffer
            while (@ob_end_clean());
            exit();
        }

        if ($GLOBALS['TL_CONFIG']['ctoCom_APIKey'] != $strApiKey)
        {
            $this->log(vsprintf("Call from %s with a wrong API Key: %s", array($this->Environment->ip, $this->Input->get("apikey"))), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

            // Clean output buffer
            while (@ob_end_clean());
            exit();
        }

        /* ---------------------------------------------------------------------
         * Check language settings
         */

        if (empty($GLOBALS['TL_LANGUAGE']))
        {
            $GLOBALS['TL_LANGUAGE'] = "en";
        }

        /* ---------------------------------------------------------------------
         * Set I/O System
         */

        if (strlen($this->Input->get("format")) != 0)
        {
            if (\CtoComIOFactory::engineExist($this->Input->get("format")))
            {
                $this->setIOEngine($this->Input->get("format"));
            }
            else
            {
                $this->setIOEngine();

                $this->objError = new \CtoComContainerError();
                $this->objError->setLanguage("unknown_io");
                $this->objError->setID(10);
                $this->objError->setObject("");
                $this->objError->setMessage("No I/O Interface found for accept.");
                $this->objError->setRPC("");
                $this->objError->setClass("");
                $this->objError->setFunction("");

                $this->generateOutput();
                exit();
            }
        }
        else
        {
            $strAccept = $_SERVER['HTTP_ACCEPT'];
            $strAccept = preg_replace("/;q=\d\.\d/", "", $strAccept);
            $arrAccept = trimsplit(",", $strAccept);

            $strIOEngine = false;

            foreach ($arrAccept as $key => $value)
            {
                $strIOEngine = \CtoComIOFactory::getEngingenameForAccept($value);

                if ($strIOEngine !== false)
                {
                    break;
                }
            }

            if ($strIOEngine === false)
            {
                $this->objIOEngine = \CtoComIOFactory::getEngine('default');

                $this->objError = new \CtoComContainerError();
                $this->objError->setLanguage("unknown_io");
                $this->objError->setID(10);
                $this->objError->setObject("");
                $this->objError->setMessage("No I/O Interface found for accept: $strAccept");
                $this->objError->setRPC("");
                $this->objError->setClass("");
                $this->objError->setFunction("");

                $this->generateOutput();
                exit();
            }
            else
            {
                $this->setIOEngine($strIOEngine);
            }
        }

        /* ---------------------------------------------------------------------
         * Run RPC-Check function
         */

        // Check if act is set
        $mixRPCCall = $this->Input->get("act");

        if (strlen($mixRPCCall) == 0)
        {
            $this->objError = new \CtoComContainerError();
            $this->objError->setLanguage("rpc_missing");
            $this->objError->setID(1);
            $this->objError->setObject("");
            $this->objError->setMessage("Missing RPC Call");
            $this->objError->setRPC($mixRPCCall);
            $this->objError->setClass("");
            $this->objError->setFunction("");

            $this->generateOutput();
            exit();
        }

        if (!key_exists($mixRPCCall, $this->arrRpcList))
        {
            $this->objError = new \CtoComContainerError();
            $this->objError->setLanguage("rpc_unknown");
            $this->objError->setID(1);
            $this->objError->setObject("");
            $this->objError->setMessage("Unknown RPC Call");
            $this->objError->setRPC($mixRPCCall);
            $this->objError->setClass("");
            $this->objError->setFunction("");

            $this->generateOutput();
            exit();
        }

        /* ---------------------------------------------------------------------
         * Build a list with parameter from the POST
         */

        $arrParameter = array();

        if ($this->arrRpcList[$mixRPCCall]["parameter"] != FALSE && is_array($this->arrRpcList[$mixRPCCall]["parameter"]))
        {
            switch ($this->arrRpcList[$mixRPCCall]["typ"])
            {
                // Decode post 
                case "POST":
                    // Decode each post
                    foreach ($_POST as $key => $value)
                    {
                        $mixPost = $this->Input->postRaw($key);
                        $mixPost = $this->objIOEngine->InputPost($mixPost, $this->objCodifyengine);

                        $this->Input->setPost($key, $mixPost);
                    }

                    // Check if all post are set
                    foreach ($this->arrRpcList[$mixRPCCall]["parameter"] as $value)
                    {
                        $arrPostKey = array_keys($_POST);

                        if (!in_array($value, $arrPostKey))
                        {
                            $arrParameter[$value] = NULL;
                        }
                        else
                        {
                            $arrParameter[$value] = $this->Input->postRaw($value);
                        }
                    }
                    break;

                default:
                    break;
            }
        }

        /* ---------------------------------------------------------------------
         * Call function
         */

        try
        {
            $strClassname = $this->arrRpcList[$mixRPCCall]["class"];

            if (!class_exists($strClassname))
            {
                $this->objError = new \CtoComContainerError();
                $this->objError->setLanguage("rpc_class_not_exists");
                $this->objError->setID(4);
                $this->objError->setObject($value);
                $this->objError->setMessage("The choosen class didn`t exists.");
                $this->objError->setRPC($mixRPCCall);
                $this->objError->setClass($this->arrRpcList[$mixRPCCall]["class"]);
                $this->objError->setFunction($this->arrRpcList[$mixRPCCall]["function"]);

                $this->generateOutput();
                exit();
            }

            $objReflection = new \ReflectionClass($strClassname);

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
        }
        catch (\RuntimeException $exc)
        {
            $this->objError = new \CtoComContainerError();
            $this->objError->setLanguage("rpc_unknown_exception");
            $this->objError->setID(3);
            $this->objError->setObject("");
            $this->objError->setMessage($exc->getMessage());
            $this->objError->setRPC($mixRPCCall);
            $this->objError->setClass($this->arrRpcList[$mixRPCCall]["class"]);
            $this->objError->setFunction($this->arrRpcList[$mixRPCCall]["function"]);
            $this->objError->setException($exc);

            $this->log(vsprintf("RPC Exception: %s | %s", array($exc->getMessage(), nl2br($exc->getTraceAsString()))), __CLASS__ . " | " . __FUNCTION__, TL_ERROR);

            $this->generateOutput();
            exit();
        }

        $this->generateOutput();
        exit();
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
        $objOutputContainer = new \CtoComContainerIO();

        if ($this->objError == false)
        {
            $objOutputContainer->setSuccess(true);
            $objOutputContainer->setResponse($this->mixOutput);
            $objOutputContainer->setSplitcontent(false);
            $objOutputContainer->setSplitcount(0);
            $objOutputContainer->setSplitname("");
        }
        else
        {
            $objOutputContainer->setSuccess(false);
            $objOutputContainer->setError($this->objError);
            $objOutputContainer->setResponse(null);
            $objOutputContainer->setSplitcontent(false);
            $objOutputContainer->setSplitcount(0);
            $objOutputContainer->setSplitname("");
        }

        $mixOutput = $this->objIOEngine->OutputResponse($objOutputContainer, $this->objCodifyengine);

        // Check if we have a big output and split it 
        if ($this->intMaxResponseLength != -1 && strlen($mixOutput) > $this->intMaxResponseLength)
        {
            $mixOutput    = str_split($mixOutput, (int) ($this->intMaxResponseLength * 0.8));
            $strFileName  = md5(time()) . md5(rand(0, 65000)) . ".ctoComPart";
            $intCountPart = count($mixOutput);

            foreach ($mixOutput as $keyOutput => $valueOutput)
            {
                $objFile = new File("system/tmp/" . $keyOutput . "_" . $strFileName);
                $objFile->write($valueOutput);
                $objFile->close();
            }

            $objOutputContainer = new \CtoComContainerIO();
            $objOutputContainer->setSuccess(true);
            $objOutputContainer->setResponse(null);
            $objOutputContainer->setSplitcontent(true);
            $objOutputContainer->setSplitcount($intCountPart);
            $objOutputContainer->setSplitname($strFileName);

            $mixOutput = $this->objIOEngine->OutputResponse($objOutputContainer, $this->objCodifyengine);
        }

        // Set some header fields
        header("Content-Type: " . $GLOBALS["CTOCOM_IO"][$this->strIOEngine]["contentType"]);

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

            // required files
            $arrRequiredFiles = array(
                'phpseclib' => 'system/modules/phpseclib/Crypt/AES.php'
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

            // check for required files
            foreach ($arrRequiredFiles as $key => $val)
            {
                if (!file_exists(TL_ROOT . '/' . $val))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required file/extension <strong>' . $key . '</strong>'));
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
        /*
         * Try to get the Version from client. 
         * If we get a blank response or a error, the system try to use the 
         * old AES Codifyengine. 
         */
        try
        {
            $strVersion = $this->runServer("CTOCOM_VERSION");

            if (version_compare($strVersion, $GLOBALS["CTOCOM_VERSION"], '<'))
            {
                $this->setConnectionBasicCodify("aeso");
            }
        }
        catch (\RuntimeException $exc)
        {
            $this->log("The client with the adress: " . $this->strUrl . " seems to be an older Version.", __CLASS__ . " | " . __FUNCTION__, TL_INFO);
            $this->setConnectionBasicCodify("aeso");
        }

        // Check handshake
        if ($GLOBALS['TL_CONFIG']['ctoCom_handshake'] == true)
        {
            // Set flag for API key use
            $arrData = array(
                array(
                    "name" => "useAPIK",
                    "value" => true,
                )
            );

            // Say "Hello" for connection id
            $strMyNumber = $this->runServer("CTOCOM_HELLO");
            $this->setConnectionID($strMyNumber);

            // Start key handshake
            if (!$this->runServer("CTOCOM_START_HANDSHAKE", $arrData, true))
            {
                throw new \RuntimeException("Could not set API Key for handshake.");
            }

            if (!$this->runServer("CTOCOM_CHECK_HANDSHAKE", $arrData, true))
            {
                throw new \RuntimeException("Could not set API Key for handshake.");
            }

            // Save and end 
            $this->setConnectionKey($this->strApiKey);
        }
        else
        {
            // Imoprt
            require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman.php';

            // Say "Hello" for connection id
            $strMyNumber = $this->runServer("CTOCOM_HELLO");
            $this->setConnectionID($strMyNumber);

            // Start key handshake
            $arrDiffieHellman = $this->runServer("CTOCOM_START_HANDSHAKE");

            $objLastException = null;

            for ($i = 0; $i < 100; $i++)
            {
                // Create random private key.
                $intPrivateLength = rand(strlen($arrDiffieHellman["generator"]), strlen($arrDiffieHellman["prime"]) - 2);
                $strPrivate       = rand(1, 9);

                for ($ii = 0; $ii < $intPrivateLength; $ii++)
                {
                    $strPrivate .= rand(0, 9);
                }

                if (!preg_match("/^\d+$/", $strPrivate))
                {
                    $objLastException = new \RuntimeException("Private key is not a natural number");
                    continue;
                }

                try
                {
                    // Start key gen
                    $objDiffieHellman = new \Crypt_DiffieHellman($arrDiffieHellman["prime"], $arrDiffieHellman["generator"], $strPrivate);
                    $objDiffieHellman->generateKeys();

                    // Send public key for check 
                    $arrData = array(
                        array(
                            "name" => "key",
                            "value" => $objDiffieHellman->getPublicKey(),
                        )
                    );
                }
                catch (\RuntimeException $exc)
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
                throw new \RuntimeException("Error for handshake. Public-Key from client isn't valide.");
            }

            $strSecretKey = $objDiffieHellman->computeSecretKey($arrDiffieHellman["public_key"])
                    ->getSharedSecretKey();

            // Save and end 
            $this->setConnectionKey($strSecretKey);
        }
    }

    public function stopConnection()
    {
        try
        {
            // Close connection
            $this->runServer("CTOCOM_BYE");
        }
        catch (Exception $exc)
        {
            // Do nothing
        }

        // Reset Session information
        $arrPool = $this->Session->get("CTOCOM_ConnectionPool");
        if (is_array($arrPool) && key_exists(md5($this->strUrl), $arrPool))
        {
            unset($arrPool[md5($this->strUrl)]);
        }

        $this->Session->set("CTOCOM_ConnectionPool", $arrPool);
    }

}