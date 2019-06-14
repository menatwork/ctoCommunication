<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 08.01.2016
 * Time: 17:08
 */

namespace MenAtWork\CtoCommunicationBundle\Controller;

use MenAtWork\CtoCommunicationBundle\Container\ClientState;
use MenAtWork\CtoCommunicationBundle\Container\Error;
use MenAtWork\CtoCommunicationBundle\Container\IO;
use MenAtWork\CtoCommunicationBundle\InputOutput\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Client extends Base
{
    const HTTP_CODE_OK = 200;
    const HTTP_CODE_FORBIDDEN = 403;
    const HTTP_CODE_NOT_FOUND = 404;
    const HTTP_CODE_FAILED_DEPENDENCY = 424;

    public function __construct()
    {
        // call the parent.
        parent::__construct();

        // preset the language.
        if (empty($GLOBALS['TL_LANGUAGE'])) {
            $GLOBALS['TL_LANGUAGE'] = 'en';
        }
    }

    /**
     * Star for routing.
     */
    public function execute(Request $request)
    {
        // Init the state container.
        $clientState = new ClientState();
        $clientState
            ->setRequest($request)
            ->setConTimeout(1200);

        // Check ping.
        if ($clientState->isPingRequest()) {
            return $this->handlePing();
        }

        // Check if we have an api key.
        if (!$clientState->hasRequestApiKey()) {
            return $this->handle404();
        }

        // Check the crypt engines.
        if (!$clientState->setupCrypt()) {
            return $this->handleResponse(self::HTTP_CODE_FORBIDDEN);
        }

        //Now the key for the crypt class.
        if ($clientState->isHandshakeRequest()) {
            $passwordState = $clientState->setupCryptPassword($clientState::CRYPT_PASSWORD_API_KEY);
        } else {
            $passwordState = $clientState->setupCryptPassword($clientState::CRYPT_PASSWORD_EXCHANGE);
        }

        // If the password was not set end here.
        if (!$passwordState) {
            return $this->handleResponse(self::HTTP_CODE_FAILED_DEPENDENCY, 'pw');
        }

        // Validate the secret with the request.
        if (!$clientState->validateAction()) {
            return $this->handleResponse(self::HTTP_CODE_FAILED_DEPENDENCY, 'val');
        }

        return $this->run($clientState);
    }

    /**
     * Just handle a ping request.
     *
     * @return Response
     */
    private function handlePing()
    {
        return new Response('', self::HTTP_CODE_OK);
    }

    /**
     * Send the 404 header.
     *
     * @return Response
     */
    private function handle404()
    {
        return new Response('', self::HTTP_CODE_NOT_FOUND);
    }

    /**
     * Default handling function.
     *
     * @param int $statusCode The http header code.
     *
     * @return Response
     */
    private function handleResponse($statusCode, $text = '')
    {
        return new Response($text, $statusCode);
    }

    /**
     * Run the communication as client
     *
     * @param ClientState $clientState
     *
     * @return Response The response
     */
    public function run($clientState)
    {
        /* ---------------------------------------------------------------------
         * Set I/O System
         */
        if ($clientState->hasRequestFormat()) {
            if (Factory::engineExist($clientState->getRequestFormat())) {
                $this->setIOEngine($clientState->getRequestFormat());
            } else {
                $this->setIOEngine();

                $objError = new Error();
                $objError->setLanguage('unknown_io');
                $objError->setID(10);
                $objError->setObject('');
                $objError->setMessage('No I/O Interface found for accept.');
                $objError->setRPC('');
                $objError->setClass('');
                $objError->setFunction('');

                return $this->generateOutput($clientState, $objError);
            }
        } else {
            $strAccept = $_SERVER['HTTP_ACCEPT'];
            $strAccept = preg_replace('/;q=\d\.\d/', '', $strAccept);
            $arrAccept = explode(',', $strAccept);

            $strIOEngine = false;

            foreach ($arrAccept as $key => $value) {
                $strIOEngine = Factory::getEngingenameForAccept($value);

                if ($strIOEngine !== false) {
                    break;
                }
            }

            if ($strIOEngine === false) {
                $this->objIOEngine = Factory::getEngine('default');

                $objError = new Error();
                $objError->setLanguage('unknown_io');
                $objError->setID(10);
                $objError->setObject('');
                $objError->setMessage(sprintf('No I/O Interface found for accept: %s', var_export($strAccept, true)));
                $objError->setRPC('');
                $objError->setClass('');
                $objError->setFunction('');

                return $this->generateOutput($clientState, $objError);
            } else {
                $this->setIOEngine($strIOEngine);
            }
        }

        /* ---------------------------------------------------------------------
         * Run RPC-Check function
         */

        // Check if act is set
        $mixRPCCall = $clientState->getAct();

        if (strlen($mixRPCCall) == 0) {
            $objError = new Error();
            $objError->setLanguage('rpc_missing');
            $objError->setID(1);
            $objError->setObject('');
            $objError->setMessage('Missing RPC Call');
            $objError->setRPC($mixRPCCall);
            $objError->setClass('');
            $objError->setFunction('');

            return $this->generateOutput($clientState, $objError);
        }

        if (!array_key_exists($mixRPCCall, $this->arrRpcList)) {
            $objError = new Error();
            $objError->setLanguage('rpc_unknown');
            $objError->setID(1);
            $objError->setObject('');
            $objError->setMessage('Unknown RPC Call');
            $objError->setRPC($mixRPCCall);
            $objError->setClass('');
            $objError->setFunction('');

            return $this->generateOutput($clientState, $objError);
        }

        /* ---------------------------------------------------------------------
         * Build a list with parameter from the POST
         */

        $arrParameter = array();

        if ($this->arrRpcList[$mixRPCCall]['parameter'] != false
            && is_array($this->arrRpcList[$mixRPCCall]['parameter'])
        ) {
            switch ($this->arrRpcList[$mixRPCCall]['typ']) {
                // Decode post
                case 'POST':
                    // Decode each post
                    $arrPostValues = array();
                    foreach ($clientState->getAllParametersFromRequest() as $key => $value) {
                        $mixPost             = $this->objIOEngine->InputPost($value, $clientState->getExtendedCodifyEngine());
                        $arrPostValues[$key] = $mixPost;
                    }

                    // Check if all post are set
                    foreach ($this->arrRpcList[$mixRPCCall]['parameter'] as $value) {
                        $arrPostKey = array_keys($arrPostValues);

                        if (!in_array($value, $arrPostKey)) {
                            $arrParameter[$value] = null;
                        } else {
                            // Get the raw data.
                            $arrParameter[$value] = $arrPostValues[$value];
                        }
                    }

                    unset($arrPostValues);
                    break;

                default:
                    break;
            }
        }

        /* ---------------------------------------------------------------------
         * Call function
         */

        $mixOutput = null;
        try {
            $strClassname = $this->arrRpcList[$mixRPCCall]['class'];

            if (!class_exists($strClassname)) {
                $objError = new Error();
                $objError->setLanguage('rpc_class_not_exists');
                $objError->setID(4);
                $objError->setObject($value);
                $objError->setMessage('The choosen class didn`t exists.');
                $objError->setRPC($mixRPCCall);
                $objError->setClass($this->arrRpcList[$mixRPCCall]['class']);
                $objError->setFunction($this->arrRpcList[$mixRPCCall]['function']);

                return $this->generateOutput($clientState, $objError);
            }

            $objReflection = new \ReflectionClass($strClassname);
            if ($objReflection->hasMethod('getInstance')) {
                $object = call_user_func_array
                (
                    array($this->arrRpcList[$mixRPCCall]['class'], 'getInstance'),
                    array()
                );

                $mixOutput = call_user_func_array
                (
                    array($object, $this->arrRpcList[$mixRPCCall]['function']),
                    $arrParameter
                );
            } else {
                $object = new $this->arrRpcList[$mixRPCCall]['class'];
                $mixOutput = call_user_func_array
                (
                    array($object, $this->arrRpcList[$mixRPCCall]['function']),
                    $arrParameter
                );
            }
        } catch (\Exception $exc) {
            $objError = new Error();
            $objError->setLanguage('rpc_unknown_exception');
            $objError->setID(3);
            $objError->setObject('');
            $objError->setMessage($exc->getMessage());
            $objError->setRPC($mixRPCCall);
            $objError->setClass($this->arrRpcList[$mixRPCCall]['class']);
            $objError->setFunction($this->arrRpcList[$mixRPCCall]['function']);
            $objError->setException($exc);

            $this->log
            (
                sprintf
                (
                    'RPC Exception: %s | %s',
                    $exc->getMessage(),
                    nl2br($exc->getTraceAsString())
                ),
                __CLASS__ . ' | ' . __FUNCTION__,
                TL_ERROR
            );

            return $this->generateOutput($clientState, $objError);
        }

        return $this->generateOutput($clientState, null, $mixOutput);
    }

    /**
     * Build the answer and serialize it
     *
     * @param ClientState $clientState The state of the client.
     *
     * @param Error       $error       The error container.
     *
     * @param null|mixed  $output      The output from the function.
     *
     * @return Response The response object.
     */
    protected function generateOutput($clientState, $error = null, $output = null)
    {
        $objOutputContainer = new IO();

        // Check if we have an error or not.
        if ($error === null) {
            $objOutputContainer->setSuccess(true);
            $objOutputContainer->setResponse($output);
            $objOutputContainer->setSplitcontent(false);
            $objOutputContainer->setSplitcount(0);
            $objOutputContainer->setSplitname('');
        } else {
            $objOutputContainer->setSuccess(false);
            $objOutputContainer->setError($error);
            $objOutputContainer->setResponse(null);
            $objOutputContainer->setSplitcontent(false);
            $objOutputContainer->setSplitcount(0);
            $objOutputContainer->setSplitname('');
        }

        $mixOutput = $this
            ->objIOEngine
            ->OutputResponse($objOutputContainer, $clientState->getExtendedCodifyEngine());

        // Check if we have a big output and split it
        if ($this->config->getResponseLength() != -1 && strlen($mixOutput) > $this->config->getResponseLength()) {
            $mixOutput    = str_split($mixOutput, (int)($this->config->getResponseLength() * 0.8));
            $strFileName  = md5(time()) . md5(rand(0, 65000)) . '.ctoComPart';
            $intCountPart = count($mixOutput);

            foreach ($mixOutput as $keyOutput => $valueOutput) {
                $objFile = new \File('system/tmp/' . $keyOutput . '_' . $strFileName);
                $objFile->write($valueOutput);
                $objFile->close();
            }

            $objOutputContainer = new IO();
            $objOutputContainer->setSuccess(true);
            $objOutputContainer->setResponse(null);
            $objOutputContainer->setSplitcontent(true);
            $objOutputContainer->setSplitcount($intCountPart);
            $objOutputContainer->setSplitname($strFileName);

            $mixOutput = $this
                ->objIOEngine
                ->OutputResponse($objOutputContainer, $clientState->getExtendedCodifyEngine());
        }

        return new Response
        (
            $mixOutput,
            200,
            [
                'Content-Type' => $GLOBALS['CTOCOM_IO'][$this->strIOEngine]['contentType']
            ]
        );
    }
}
