<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 19.12.2017
 * Time: 18:31
 */

namespace MenAtWork\CtoCommunicationBundle\Container;


use MenAtWork\CtoCommunicationBundle\Codifyengine\Base as CodifyengineBase;
use MenAtWork\CtoCommunicationBundle\Codifyengine\Factory;
use Symfony\Component\HttpFoundation\Request;

class ClientState
{
    const CRYPT_PASSWORD_API_KEY = 'api_key';
    const CRYPT_PASSWORD_EXCHANGE = 'exchange';

    /**
     * Contains a list of all handshake functions.
     *
     * @var array
     */
    protected $handshakeFunctions = array
    (
        "CTOCOM_HELLO",
        "CTOCOM_START_HANDSHAKE",
        "CTOCOM_CHECK_HANDSHAKE",
        "CTOCOM_VERSION"
    );

    /**
     * Timeout for the handshake.
     *
     * @var int
     */
    protected $conTimeout;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CodifyengineBase
     */
    protected $basicCodifyEngine;

    /**
     * @var CodifyengineBase
     */
    protected $extendedCodifyEngine;

    /**
     * Set the current request.
     *
     * @param Request $request The current request.
     *
     * @return ClientState
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return int
     */
    public function getConTimeout()
    {
        return $this->conTimeout;
    }

    /**
     * @param int $conTimeout
     *
     * @return ClientState
     */
    public function setConTimeout($conTimeout)
    {
        $this->conTimeout = $conTimeout;

        return $this;
    }

    /**
     * Get the act from the request.
     *
     * @return string
     */
    private function getAct()
    {
        return $this->request->get('act');
    }

    /**
     * Get the connection ID.
     *
     * @return string
     */
    private function getCon()
    {
        return $this->request->get('con');
    }

    /**
     * Check if the current request is a ping.
     *
     * @return bool
     */
    public function isPingRequest()
    {
        return $this->getAct() == 'ping';
    }

    /**
     * Check if the current request is a handshake function.
     *
     * @return bool
     */
    public function isHandshakeRequest()
    {
        return in_array($this->getAct(), $this->handshakeFunctions);
    }

    /**
     * Get the wish engine from the connection.
     *
     * @return mixed
     *
     * @throws \RuntimeException If there is no engine.
     */
    public function getEngineName()
    {
        $engine = $this->request->get('engine');
        if (empty($engine)) {
            throw new \RuntimeException('No engine was set.');
        }

        return $engine;
    }

    /**
     * Setup the crypt functions.
     *
     * @return bool
     */
    public function setupCrypt()
    {
        // Check if IV was send, when send use the new AES else the old one.
        try {
            $this->basicCodifyEngine    = Factory::getEngine("aes");
            $this->extendedCodifyEngine = Factory::getEngine($this->getEngineName());
        } catch (\RuntimeException $exc) {
            $this->log
            (
                "Try to load the engine for ctoCommunication with error: " . $exc->getMessage(),
                __FUNCTION__ . " | " . __CLASS__,
                TL_ERROR
            );

            return false;
        }

        return true;
    }


    public function setupCryptPassword($use = self::CRYPT_PASSWORD_API_KEY)
    {
        // Use the api key.
        if ($use == self::CRYPT_PASSWORD_API_KEY) {
            $this->basicCodifyEngine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
            $this->extendedCodifyEngine->setKey($GLOBALS['TL_CONFIG']['ctoCom_APIKey']);
        } else if ($use == self::CRYPT_PASSWORD_EXCHANGE) {
            // Check if we have some data
            $arrConnections = \Database::getInstance()
                ->prepare("SELECT * FROM tl_ctocom_cache WHERE uid=?")
                ->execute($this->getCon())
                ->fetchAllAssoc();

            if (count($arrConnections) == 0) {
                $this->log
                (
                    vsprintf
                    (
                        "Call from %s with a unknown connection ID.",
                        \Environment::get('ip')
                    ),
                    __FUNCTION__ . " | " . __CLASS__,
                    TL_ERROR
                );

                return false;
            }

            // Check if time out isn't reached.
            if ($arrConnections[0]["tstamp"] + $this->getConTimeout() < time()) {
                \Database::getInstance()
                    ->prepare("DELETE FROM tl_ctocom_cache WHERE uid=?")
                    ->execute($this->getCon());

                $this->log
                (
                    vsprintf
                    (
                        "Call from %s with a expired connection ID.",
                        \Environment::get('ip')
                    ),
                    __FUNCTION__ . " | " . __CLASS__,
                    TL_ERROR
                );

                return false;
            }

            // Reset timestamp
            \Database::getInstance()
                ->prepare("UPDATE tl_ctocom_cache %s WHERE uid=?")
                ->set(array("tstamp" => time()))
                ->execute($this->getCon());

            // Set codify key from database
            $this->basicCodifyEngine->setKey($arrConnections[0]["shared_secret_key"]);
            $this->extendedCodifyEngine->setKey($arrConnections[0]["shared_secret_key"]);
        }

        return true;
    }

    /**
     * @param $msg
     * @param $where
     * @param $type
     */
    private function log($msg, $where, $type)
    {
        // ToDo: Add
    }
}
