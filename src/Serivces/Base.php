<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 08.01.2016
 * Time: 17:08
 */

namespace CtoCommunication\Serivces;

use CtoCommunication\Codifyengine\Factory;
use CtoCommunication\Container\Error;
use CtoCommunication\Helper\Config;
use CtoCommunication\Helper\Debug;
use CtoCommunication\InputOutput\InterfaceInputOutput;

class Base
{
    protected $strUrlGet;           // GET Parameter for the call
    protected $strIOEngine;         // Main Input/Output Engine
    protected $arrCookies;          // Cookies - not used
    protected $arrRpcList;          // A list with all RPC
    protected $mixOutput;           // Output @todo - Check if we need this

    // Config
    /**
     * Time in seconds for handshake timeout.
     *
     * @var int
     */
    protected $intHandshakeTimeout = 1200;

    /**
     * @var \CtoCommunication\Codifyengine\Base
     */
    protected $objCodifyengine;

    /**
     * @var \CtoCommunication\Codifyengine\Base
     */
    protected $objCodifyengineBasic;

    /**
     * @var InterfaceInputOutput
     */
    protected $objIOEngine;

    /**
     * @var Debug
     */
    protected $objDebug;

    /**
     * @var Error
     */
    protected $objError;

    //----------------------------------

    /**
     * The container for global configurations.
     *
     * @var Config
     */
    protected $config;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initEnvironment();
        $this->loadAdditionalData();
    }

    /**
     * Init the base Environment.
     */
    protected function initEnvironment()
    {
        // Load the config class.
        $this->config               = new Config();
        $this->objCodifyengineBasic = Factory::getEngine('aes');
        $this->objDebug             = Debug::getInstance();
        $this->objError             = false;
        $this->arrRpcList           = $GLOBALS["CTOCOM_FUNCTIONS"];

        $this->setIOEngine("default");
        $this->setCodifyengine();
    }

    /**
     * Load some additional data.
     */
    protected function loadAdditionalData()
    {
        require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman.php';
        require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman/Exception.php';
        require_once TL_ROOT . '/system/modules/DiffieHellman/DiffieHellman/Math/Exception.php';
    }

    /**
     * Set the url for connection
     *
     * @param string $strUrl
     *
     * @deprecated since 8. Jan 2016 use the setClient instead.
     */
    public function setUrl($strUrl)
    {
        throw new \RuntimeException('Unsupported function ' . __FUNCTION__);
    }

    /**
     * Set the API Key
     *
     * @param string $strApiKey
     *
     * @deprecated since 8. Jan 2016 use the setClient instead.
     */
    public function setApiKey($strApiKey)
    {
        throw new \RuntimeException('Unsupported function ' . __FUNCTION__);
    }

    /**
     * Set a username for http auth
     *
     * @param string $strHTTPUser
     *
     * @deprecated since 8. Jan 2016 use the setHttpAuth instead.
     */
    public function setHTTPUser($strHTTPUser)
    {
        throw new \RuntimeException('Unsupported function ' . __FUNCTION__);
    }

    /**
     * Set a password for http auth
     *
     * @param string $strHTTPPassword
     *
     * @deprecated since 8. Jan 2016 use the setHttpAuth instead.
     */
    public function setHTTPPassword($strHTTPPassword)
    {
        throw new \RuntimeException('Unsupported function ' . __FUNCTION__);
    }

    /**
     * Change codifyengine
     *
     * @param string $strName
     */
    public function setCodifyengine($strName = null)
    {
        $this->objCodifyengine = Factory::getEngine($strName);
    }

    /**
     * Change I/O enginge
     *
     * @param string $strName
     */
    public function setIOEngine($strName = 'default')
    {
        $this->objIOEngine = \CtoCommunication\InputOutput\Factory::getEngine($strName);
        $this->strIOEngine = $strName;
    }

    /**
     * Change I/O enginge
     *
     * @param string $strName
     */
    public function setIOEngineByContentTyp($strName = 'text/html')
    {
        $this->setIOEngine(\CtoCommunication\InputOutput\Factory::getEngingenameForContentType($strName));
    }

    /**
     * Change I/O enginge
     *
     * @param string $strName
     */
    public function setIOEngineByAccept($strName = 'text/html')
    {
        $this->setIOEngine(\CtoCommunication\InputOutput\Factory::getEngingenameForAccept($strName));
    }

    /**
     * Set Cookie information
     *
     * @param string $name  Key name of array
     *
     * @param mixed  $value Value for Cookie
     */
    public function setCookies($name, $value)
    {
        if ($value == "") {
            unset($this->arrCookies[$name]);
        } else {
            $this->arrCookies[$name] = $value;
        }
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
}
