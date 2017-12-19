<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 08.01.2016
 * Time: 10:19
 */

namespace MenAtWork\CtoCommunicationBundle\Container;


class Connection
{
    /**
     * The ID of the connection.
     *
     * @var int
     */
    protected $id;

    /**
     * API Key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Base url.
     *
     * @var string
     */
    protected $url;

    /**
     * HTTP Auth name.
     *
     * @var string
     */
    protected $httpUser;

    /**
     * The Auth password.
     *
     * @var string
     */
    protected $httpPassword;

    /**
     * The current connection id;
     *
     * @var int
     */
    protected $connectionID;

    /**
     * The current connection key
     *
     * @var string
     */
    protected $connectionKey;

    /**
     * The name of the default codify engine.
     *
     * @var string
     */
    protected $defaultCodifyEngineName;

    /**
     * Check if the url is empty.
     *
     * @return bool
     */
    public function isUrlEmpty()
    {
        return empty($this->url);
    }

    /**
     * Check if the url is empty.
     *
     * @return bool
     */
    public function isConnectionKeyEmpty()
    {
        return empty($this->connectionKey);
    }


    /**
     * Check if the api key is empty.
     *
     * @return bool
     */
    public function isApiKeyEmpty()
    {
        return empty($this->apiKey);
    }

    /**
     * Check if the default codify engine name is empty.
     *
     * @return bool
     */
    public function isDefaultCodifyEngineNameEmpty()
    {
        return empty($this->defaultCodifyEngineName);
    }

    /**
     * Check if a http user and passwort is set.
     *
     * @return bool
     */
    public function hasHttpAuth()
    {
        return (!empty($this->httpPassword) && !empty($this->httpUser));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Connection
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return Connection
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Connection
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpUser()
    {
        return $this->httpUser;
    }

    /**
     * @param string $httpUser
     *
     * @return Connection
     */
    public function setHttpUser($httpUser)
    {
        $this->httpUser = $httpUser;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpPassword()
    {
        return $this->httpPassword;
    }

    /**
     * @param string $httpPassword
     *
     * @return Connection
     */
    public function setHttpPassword($httpPassword)
    {
        $this->httpPassword = $httpPassword;

        return $this;
    }

    /**
     * @return int
     */
    public function getConnectionID()
    {
        return $this->connectionID;
    }

    /**
     * @param int $connectionID
     *
     * @return Connection
     */
    public function setConnectionID($connectionID)
    {
        $this->connectionID = $connectionID;

        return $this;
    }

    /**
     * @return string
     */
    public function getConnectionKey()
    {
        return $this->connectionKey;
    }

    /**
     * @param string $connectionKey
     *
     * @return Connection
     */
    public function setConnectionKey($connectionKey)
    {
        $this->connectionKey = $connectionKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultCodifyEngineName()
    {
        return $this->defaultCodifyEngineName;
    }

    /**
     * @param string $defaultCodifyEngineName
     *
     * @return Connection
     */
    public function setDefaultCodifyEngineName($defaultCodifyEngineName)
    {
        $this->defaultCodifyEngineName = $defaultCodifyEngineName;

        return $this;
    }
}
