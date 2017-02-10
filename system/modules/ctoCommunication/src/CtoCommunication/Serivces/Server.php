<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 08.01.2016
 * Time: 17:08
 */

namespace CtoCommunication\Serivces;

use CtoCommunication\Codifyengine\Factory;
use CtoCommunication\Container\Connection;
use CtoCommunication\Container\IO;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Post\PostFile;

class Server extends Base
{
    /**
     * Holds all information for the connection.
     *
     * @var Connection
     */
    protected $client;

    /**
     * The HTTP request.
     *
     * @var \GuzzleHttp\Message\ResponseInterface | \Psr\Http\Message\ResponseInterface
     */
    protected $request;

    /**
     * The response from the request.
     *
     * @var string|IO
     */
    protected $response;

    /**
     * The RPC call.
     *
     * @var string
     */
    protected $rpc;

    /**
     * The to be sent to the client.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Flag if GET or POST request.
     *
     * @var bool
     */
    protected $isGetRequest = false;

    /**
     * Server constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set all client params.
     *
     * @param string $url          The base url of the client.
     *
     * @param string $apiKey       The api key.
     *
     * @param string $codifyEngine The codify engine.
     *
     * @param string $ioEngine     The I/O engine.
     *
     * @return $this
     */
    public function setClient($url, $apiKey, $codifyEngine = "aes", $ioEngine = "default")
    {
        $this->client = new Connection();
        $this->client
            ->setUrl($url)
            ->setApiKey($apiKey);

        // Load the session information.
        $this->loadConnectionSettings($this->client);

        // Set codify and I/O engine
        $this->setCodifyengine($codifyEngine);
        $this->setIOEngine($ioEngine);

        return $this;
    }

    /**
     * Set a username for http auth
     *
     * @param string $user     The username.
     *
     * @param string $password The password.
     *
     * @return $this
     */
    public function setHttpAuth($user, $password)
    {
        // Check if we have a client.
        if ($this->client == null) {
            throw new \RuntimeException('First set a client by using the "setClient" function.');
        }

        // Set the data.
        $this->client
            ->setHttpUser($user)
            ->setHttpPassword($password);

        return $this;
    }

    /**
     * Build a query string with all information for a server request.
     *
     * @param bool $asArray Flag if we want the query params as array.
     *
     * @return string the query string
     */
    protected function getQueryString($asArray = false)
    {
        // Crypt the API key and the RPC for security.
        $strCryptApiKey = $this->objCodifyengineBasic->Encrypt($this->rpc . "@|@" . $this->client->getApiKey());
        $strCryptApiKey = base64_encode($strCryptApiKey);

        if ($asArray) {
            return [
                'engine' => $this->objCodifyengine->getName(),
                'act'    => $this->rpc,
                'apikey' => $strCryptApiKey,
                'con'    => $this->client->getConnectionID()
            ];
        } else {
            // Build the GET query string.
            $queryString = sprintf(
                "engine=%s&act=%s&apikey=%s&con=%s",
                $this->objCodifyengine->getName(),
                $this->rpc,
                $strCryptApiKey,
                $this->client->getConnectionID()
            );

            // Get the connector between base url and query string.
            $connector = (strpos($this->client->getUrl(), "?") !== false) ? "&" : "?";

            // Save it local.
            return $connector . $queryString;
        }
    }

    /**
     * Load from the session all current connection information.
     *
     * @param Connection $client
     */
    protected function loadConnectionSettings($client)
    {
        // Load Session information
        $pool           = \Session::getInstance()->get('CTOCOM_ConnectionPool');
        $connectionUUID = md5($client->getUrl());

        // If we have data set it.
        if (is_array($pool) && array_key_exists($connectionUUID, $pool)) {
            $client
                ->setConnectionID($pool[$connectionUUID]['id'])
                ->setConnectionKey($pool[$connectionUUID]['key'])
                ->setDefaultCodifyEngineName($pool[$connectionUUID]['codifyengine']);
        }
    }

    /**
     * Save all connection information to the session.
     *
     * @param Connection $client
     */
    protected function saveConnectionSettings($client)
    {
        // Save information in the session.
        $pool = \Session::getInstance()->get('CTOCOM_ConnectionPool');
        if (!is_array($pool)) {
            $pool = array();
        }

        // Add all data to the array.
        $connectionUUID        = md5($client->getUrl());
        $pool[$connectionUUID] = array
        (
            'id'           => $client->getConnectionID(),
            'key'          => $client->getConnectionKey(),
            'codifyengine' => $client->getDefaultCodifyEngineName()
        );

        // Save back into the session.
        \Session::getInstance()->set('CTOCOM_ConnectionPool', $pool);
    }

    /**
     * Save the connection key
     *
     * @param string $codifyEngine The name of the codify engine.
     */
    public function setConnectionBasicCodify($codifyEngine)
    {
        // Set the new engine
        $this->objCodifyengineBasic = Factory::getEngine($codifyEngine);
        $this->client->setDefaultCodifyEngineName($codifyEngine);
    }

    public function startConnection()
    {
        /*
         * Try to get the Version from client.
         * If we get a blank response or a error, the system try to use the
         * old AES Codifyengine.
         */
        try {
            $strVersion = $this->run("CTOCOM_VERSION");

            if (version_compare($strVersion, $GLOBALS["CTOCOM_VERSION"], '<')) {
                $this->setConnectionBasicCodify("aes");
            }
        } catch (\RuntimeException $exc) {
            \System::log("The client with the adress: " . $this->strUrl . " seems to be an older Version.",
                __CLASS__ . " | " . __FUNCTION__, TL_INFO);
            $this->setConnectionBasicCodify("aes");
        }

        // Check handshake
        if ($GLOBALS['TL_CONFIG']['ctoCom_handshake'] == true) {
            // Set flag for API key use
            $arrData = array(
                array(
                    "name"  => "useAPIK",
                    "value" => true,
                )
            );

            // Say "Hello" for connection id
            $strMyNumber = $this->run("CTOCOM_HELLO");
            $this->client->setConnectionID($strMyNumber);

            // Start key handshake
            if (!$this->run("CTOCOM_START_HANDSHAKE", $arrData, true)) {
                throw new \RuntimeException("Could not set API Key for handshake.");
            }

            if (!$this->run("CTOCOM_CHECK_HANDSHAKE", $arrData, true)) {
                throw new \RuntimeException("Could not set API Key for handshake.");
            }

            // Save and end
            $this->client->setConnectionKey($this->client->getApiKey());
            $this->saveConnectionSettings($this->client);
        } else {
            // Imoprt
            require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman.php';

            // Say "Hello" for connection id
            $strMyNumber = $this->run("CTOCOM_HELLO");
            $this->client->setConnectionID($strMyNumber);

            // Start key handshake
            $arrDiffieHellman = $this->run("CTOCOM_START_HANDSHAKE");

            $objLastException = null;

            for ($i = 0; $i < 100; $i++) {
                // Create random private key.
                $intPrivateLength = rand(strlen($arrDiffieHellman["generator"]),
                    strlen($arrDiffieHellman["prime"]) - 2);
                $strPrivate       = rand(1, 9);

                for ($ii = 0; $ii < $intPrivateLength; $ii++) {
                    $strPrivate .= rand(0, 9);
                }

                if (!preg_match("/^\d+$/", $strPrivate)) {
                    $objLastException = new \RuntimeException("Private key is not a natural number");
                    continue;
                }

                try {
                    // Start key gen
                    $objDiffieHellman = new \Crypt_DiffieHellman($arrDiffieHellman["prime"],
                        $arrDiffieHellman["generator"], $strPrivate);
                    $objDiffieHellman->generateKeys();

                    // Send public key for check
                    $arrData = array(
                        array(
                            "name"  => "key",
                            "value" => $objDiffieHellman->getPublicKey(),
                        )
                    );
                } catch (\RuntimeException $exc) {
                    $objLastException = $exc;
                    continue;
                }

                $objLastException = null;
                break;
            }

            if ($objLastException != null) {
                throw $objLastException;
            }

            $strPublicKey = $this->run("CTOCOM_CHECK_HANDSHAKE", $arrData, true);

            if ($arrDiffieHellman["public_key"] != $strPublicKey) {
                throw new \RuntimeException("Error for handshake. Public-Key from client isn't valide.");
            }

            $strSecretKey = $objDiffieHellman
                ->computeSecretKey($arrDiffieHellman["public_key"])
                ->getSharedSecretKey();

            // Save and end
            $this->client->setConnectionKey($strSecretKey);
            $this->saveConnectionSettings($this->client);
        }
    }

    public function stopConnection()
    {
        try {
            // Close connection
            $this->run("CTOCOM_BYE");
        } catch (\Exception $exc) {
            // Do nothing
        }

        // Reset Session information
        $arrPool    = \Session::getInstance()->get("CTOCOM_ConnectionPool");
        $clientUUID = md5($this->client->getUrl());
        if (is_array($arrPool) && array_key_exists($clientUUID, $arrPool)) {
            unset($arrPool[$clientUUID]);
        }

        \Session::getInstance()->set("CTOCOM_ConnectionPool", $arrPool);
    }

    /**
     * Run as Server and send some data or files
     *
     * @param string  $rpc     The RPC name.
     *
     * @param array   $arrData A list of post data.
     *
     * @param boolean $isGET   Flag if use the GET instead of POST.
     *
     * @return mixed
     * @throws \Exception
     */
    public function run($rpc, $arrData = array(), $isGET = false)
    {
        // Save all local.
        $this->rpc          = $rpc;
        $this->data         = $arrData;
        $this->isGetRequest = $isGET;

        // Run.
        return $this
            ->checkEnvironment()
            ->setupCodifyEngine()
            ->sendRequest()
            ->validateResponse()
            ->parseResponse()
            ->getData();
    }

    /**
     * Check if everything is set / Init
     *
     * @return $this
     */
    protected function checkEnvironment()
    {
        // Check the client.
        if ($this->client == null) {
            throw new \RuntimeException('No client is set.');
        }

        // Check the API key.
        if ($this->client->isApiKeyEmpty()) {
            throw new \RuntimeException('The API Key is not set. Please set first API Key.');
        }

        // Check the url.
        if ($this->client->isUrlEmpty()) {
            throw new \RuntimeException('There is no URL set for connection. Please set first the url.');
        }

        return $this;
    }

    /**
     *  Check if we need another core codify engine.
     *
     * @return $this
     */
    protected function setupCodifyEngine()
    {
        // Set the default cryption.
        if (!$this->client->isDefaultCodifyEngineNameEmpty()) {
            $this->objCodifyengine = $this->objCodifyengineBasic = Factory::getEngine($this->client->getDefaultCodifyEngineName());
        }

        // If we have no connection key or a special case..
        $isSpecialRpc = in_array(
            $this->rpc,
            array("CTOCOM_HELLO", "CTOCOM_START_HANDSHAKE", "CTOCOM_CHECK_HANDSHAKE", "CTOCOM_VERSION")
        );

        // Set the api key as crypt key.
        if ($this->client->isConnectionKeyEmpty() || $isSpecialRpc) {
            $this->objCodifyengineBasic->setKey($this->client->getApiKey());
            $this->objCodifyengine->setKey($this->client->getApiKey());
        } else {
            $this->objCodifyengineBasic->setKey($this->client->getConnectionKey());
            $this->objCodifyengine->setKey($this->client->getConnectionKey());
        }

        return $this;
    }

    /**
     * Send a new request.
     *
     * @return $this
     */
    protected function sendRequest()
    {
        // Setup the basi settings.
        $httpClient  = new Client([
            'timeout' => $this->config->getConnectionTimeout(),
        ]);
        $requestType = 'GET';
        $request     = null;
        $options     = array
        (
            'headers' => array
            (
                'Accept-Language' => vsprintf("%s, en;q=0.8", array($GLOBALS['TL_LANGUAGE'])),
                'Accept'          => 'text/plain; q=0.5, text/html'
            )
        );

        // Add the user auth if set.
        if ($this->client->hasHttpAuth()) {
            $options['auth'] = [$this->client->getHttpUser(), $this->client->getHttpPassword()];
        }

        if (class_exists('GuzzleHttp\Post\PostBody')) {
            if ($this->isGetRequest) { // Only get.
                // Build the array with the query params.
                $query = array();
                foreach ($this->data as $key => $value) {
                    $query[$value["name"]] = $value["value"];
                }

                // Add to the options.
                $options = array_merge_recursive($options, array('query' => $query));
            } else {
                // Create a new post body and add all form fields.
                $postBody = new PostBody();
                foreach ($this->data as $key => $value) {
                    if (isset($value["filename"]) == true && strlen($value["filename"]) != 0) {
                        $postBody->addFile
                        (
                            new PostFile
                            (
                                $value["name"],
                                fopen($value["filepath"], 'r'),
                                basename($value["filepath"]),
                                array
                                (
                                    'contentType' => $value["mime"],
                                    'encoding'    => 'binary'
                                )
                            )
                        );
                    } else {
                        $postBody->setField($value["name"],
                            $this->objIOEngine->OutputPost($value["value"], $this->objCodifyengine));
                    }
                }

                // Create a new request for post.
                $requestType = 'POST';
                $options     = array_merge_recursive($options, array('body' => $postBody));
            }

            // create a new request.
            $request = $httpClient->createRequest(
                $requestType,
                $this->client->getUrl() . $this->getQueryString(),
                $options
            );

            // Send the request.
            $this->request = $httpClient->send($request);

        } else {
            if ($this->isGetRequest) { // Only get.
                // Build the array with the query params.
                $query = array();
                foreach ($this->data as $key => $value) {
                    $query[$value["name"]] = $value["value"];
                }

                // Add to the options.
                $options = array_merge_recursive($options, array('query' => $query));
            } else {
                // Create a new post body and add all form fields.
                $postBody = [];
                foreach ($this->data as $key => $value) {
                    if (isset($value["filename"]) == true && strlen($value["filename"]) != 0) {
                        $postBody[] = [
                            'name'     => $value["name"],
                            'contents' => fopen($value["filepath"], 'r'),
                            'filename' => basename($value["filepath"]),
                            'headers'  => [
                                'contentType' => $value["mime"],
                                'encoding'    => 'binary'
                            ]
                        ];
                    } else {
                        $postBody[] = [
                            'name'     => $value["name"],
                            'contents' => $this->objIOEngine->OutputPost($value["value"], $this->objCodifyengine)
                        ];
                    }
                }

                // Create a new request for post.
                $requestType = 'POST';
                $options     = array_merge_recursive($options, array('multipart' => $postBody));
            }

            // Add the query parts.
            $options = array_merge_recursive($options, array('query' => $this->getQueryString(true)));

            // Send the request.
            $this->request = $httpClient->request(
                $requestType,
                $this->client->getUrl(),
                $options
            );
        }

        return $this;
    }

    /**
     * Validate the response.
     *
     * @return $this
     */
    protected function validateResponse()
    {
        $body           = $this->request->getBody();
        $this->response = $body->getContents();

        // Send new request
        if ($this->request == false || $this->request->getReasonPhrase() != 'OK') {
            $this->objDebug->addDebug("Request", substr($this->response, 0, 4096));
            $this->objDebug->addDebug("Error Response", substr($this->response, 0, 4096));

            throw new \RuntimeException("Error on transmission, with message: " . $this->request->getStatusCode());
        }

        $this->objDebug->addDebug("Request", substr($this->response, 0, 4096));

        // Build response Header information.
        $strResponseHeader = "";
        $arrHeaderKeys     = array_keys($this->request->getHeaders());

        foreach ($arrHeaderKeys as $keyHeader) {
            $strResponseHeader .= $keyHeader . ": " . $this->request->getHeader($keyHeader) . "\n";
        }

        $this->objDebug->addDebug("Response", $strResponseHeader . "\n\n" . $this->request->getBody()->read(2048));

        // Check if we have a response
        if (strlen($this->response) == 0) {
            throw new \RuntimeException("We got a blank response from server.");
        }

        // Check for "Fatal error" on client side
        if (strpos($this->response, "Fatal error") !== false) {
            throw new \RuntimeException("We got a Fatal error on client site. " . $this->response);
        }

        // Check for "Warning" on client side
        if (strpos($this->response, "Warning") !== false) {
            $intStart = stripos($this->response, "<strong>Warning</strong>:");
            $intEnd   = stripos($this->response, "on line");

            if ($intEnd === false) {
                $intLength = strlen($this->response) - $intStart;
            } else {
                $intLength = $intEnd - $intStart;
            }

            throw new \RuntimeException("We got a Warning on client site.<br /><br />" . substr($this->response,
                    $intStart,
                    $intLength));
        }

        return $this;
    }

    /**
     * Get the right I/O Class and rewrite the content.
     *
     * @return $this
     */
    protected function parseResponse()
    {
        // Get the list of content types.
        $contentTypes = $this->request->getHeader('Content-Type');
        // If not an array, make an array from it.
        if (!is_array($contentTypes)) {
            $contentTypes = preg_replace("/;.*$/", "", $contentTypes);
            $contentTypes = array($contentTypes);
        } else {
            $contentTypes = array_map(
                function ($value) {
                    return preg_replace("/;.*$/", "", $value);
                },
                $contentTypes
            );
        }

        // Try to find the best fitting engine.
        $objIOEngine = false;
        foreach ($contentTypes as $contentType) {
            // Search a engine
            $objIOEngine = \CtoCommunication\InputOutput\Factory::getEngingeForContentType($contentType);

            if ($objIOEngine !== false) {
                break;
            }
        }

        // Check if we have found one
        if ($objIOEngine == false) {
            throw new \RuntimeException("No I/O class found for " . $contentTypes);
        }

        // Parse response
        $this->response = $objIOEngine->InputResponse($this->response, $this->objCodifyengine);

        // Write Debug msg
        $strDebug = "";
        $strDebug .= "Success:     ";
        $strDebug .= ($this->response->isSuccess()) ? "true" : "false";
        $strDebug .= "\n";

        if ($this->response->isSplitcontent() == true) {
            $strDebug .= "Split:       " . $this->response->isSplitcontent();
            $strDebug .= "\n";
            $strDebug .= "Splitinfo:   " . "Count - " . $this->response->getSplitcount() . " Name - " . $this->response->getSplitname();
            $strDebug .= "\n";
        }

        if ($this->response->getError() != null && is_object($this->response->getError())) {
            $strDebug .= "Error:       " . $this->response->getError()->getMessage();
            $strDebug .= "\n";
            $strDebug .= "Error RPC:   " . $this->response->getError()->getRPC();
            $strDebug .= "\n";
            $strDebug .= "Error Class: " . $this->response->getError()->getClass();
            $strDebug .= "\n";
            $strDebug .= "Error Func.: " . $this->response->getError()->getFunction();
            $strDebug .= "\n";
        }

        $strDebug .= "Response:    " . substr(json_encode($this->response->getResponse()), 0, 2048);

        $this->objDebug->addDebug("Response Object", $strDebug);

        return $this;
    }

    /**
     * Extract data or throw errors.
     *
     * @return mixed
     */
    protected function getData()
    {
        // Check if client says "Everything aokay"
        if ($this->response->isSuccess()) {
            if ($this->response->isSplitcontent() == true) {
                try {
                    $this->response->setResponse($this->rebuildSplitcontent($this->response->getSplitname(),
                        $this->response->getSplitcount()));
                } catch (\RuntimeException $exc) {
                    throw $exc;
                }
            }

            return $this->response->getResponse();
        } else {
            if ($this->getDebug() == true) {
                $string = vsprintf($GLOBALS['TL_LANG']['ERR']['client_error'] . ":<br />%s<br /><br />RPC Call: %s | Class: %s | Function: %s",
                    array(
                        nl2br($this->response->getError()->getMessage()),
                        $this->response->getError()->getRPC(),
                        (strlen($this->response->getError()->getClass()) != 0) ? $this->response->getError()->getClass() : " - ",
                        (strlen($this->response->getError()->getFunction()) != 0) ? $this->response->getError()->getFunction() : " - ",
                    )
                );
            } else {
                if ($this->response->getError()->getRPC() == "") {
                    $string = "There was an unknown error on client site.";
                } else {
                    $string = vsprintf($GLOBALS['TL_LANG']['ERR']['client_error'] . ":<br />%s<br /><br />RPC Call: %s",
                        array(
                            nl2br($this->response->getError()->getMessage()),
                            $this->response->getError()->getRPC(),
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

        for ($i = 0; $i < $intSplitCount; $i++) {
            @set_time_limit(300);

            $arrData = array(
                array(
                    "name"  => "splitname",
                    "value" => $strSplitname,
                ),
                array(
                    "name"  => "splitcount",
                    "value" => $i,
                )
            );

            $mixContent .= $this->run("CTOCOM_GET_RESPONSE_PART", $arrData);
        }

        $objResponse = $this->objIOEngine->InputResponse($mixContent, $this->objCodifyengine);

        // Check if client says "Everything ok"
        if ($objResponse->isSuccess() == true) {
            return $objResponse->getResponse();
        } else {
            $string = vsprintf($GLOBALS['TL_LANG']['ERR']['client_error'] . ":<br />%s<br /><br />RPC Call: %s", array(
                    nl2br($objResponse->getError()->getMessage()),
                    $objResponse->getError()->getRPC(),
                )
            );

            throw new \RuntimeException($string);
        }
    }
}
