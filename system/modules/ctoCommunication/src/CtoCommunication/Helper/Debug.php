<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace CtoCommunication\Helper;

/**
 * Debug class
 */
class Debug extends \Backend
{

    //- Singelten pattern --------
    protected static $instance = null;
    //- Vars ---------------------
    protected $arrMeasurement;
    protected $arrDebug;
    //- Config -------------------
    protected $booMeasurement;
    protected $booDebug;
    protected $strFileMeasurement;
    protected $strFileDebug;

    /* -------------------------------------------------------------------------
     * Core
     */

    protected function __construct()
    {
        parent::__construct();

        $this->strFileDebug       = "system/tmp/CtoComDebug.txt";
        $this->strFileMeasurement = "system/tmp/CtoComMeasurement.txt";

        $this->booDebug       = false;
        $this->booMeasurement = false;
    }

    public function __destruct()
    {
        if ($this->booDebug) {
            $this->writeDebug();
        }

        if ($this->booMeasurement) {
            $this->writeMeasurement();
        }
    }

    /**
     * Get instance.
     *
     * @return Debug
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __get($name)
    {
        switch ($name) {
            case "activateMeasurement":
                return $this->booMeasurement;

            case "activateDebug":
                return $this->booDebug;

            case "pathMeasurement":
                return $this->strFileMeasurement;

            case "pathDebug":
                return $this->strFileDebug;

            default:
                return null;
        }
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case "activateMeasurement":
                $this->booMeasurement = (boolean)$value;
                break;

            case "activateDebug":
                $this->booDebug = (boolean)$value;
                break;

            case "pathMeasurement":
                $this->strFileMeasurement = $value;
                break;

            case "pathDebug":
                $this->strFileDebug = $value;
                break;

            default:
                throw new \Exception("Unknown set typ: " . $name);
        }
    }

    /* -------------------------------------------------------------------------
     * Getter and Setter
     */

    /**
     *
     * @return boolean
     */
    public function getMeasurement()
    {
        return $this->booMeasurement;
    }

    /**
     *
     * @param boolean $booMeasurement
     */
    public function setMeasurement($booMeasurement)
    {
        $this->booMeasurement = $booMeasurement;
    }

    /**
     *
     * @return boolean
     */
    public function getDebug()
    {
        return $this->booDebug;
    }

    /**
     *
     * @param boolean $booDebug
     */
    public function setDebug($booDebug)
    {
        $this->booDebug = $booDebug;
    }

    /**
     *
     * @return string
     */
    public function getFileMeasurement()
    {
        return $this->strFileMeasurement;
    }

    /**
     *
     * @param string $strFileMeasurement
     */
    public function setFileMeasurement($strFileMeasurement)
    {
        $this->strFileMeasurement = $strFileMeasurement;
    }

    /**
     *
     * @return string
     */
    public function getFileDebug()
    {
        return $this->strFileDebug;
    }

    /**
     *
     * @param string $strFileDebug
     */
    public function setFileDebug($strFileDebug)
    {
        $this->strFileDebug = $strFileDebug;
    }

    /* -------------------------------------------------------------------------
     * Mesurement and Debug Call Functions
     */

    /**
     * Start a Measurement
     *
     * @param string $strClass
     * @param string $strFunction
     * @param string $strInformation
     *
     * @return int
     */
    public function startMeasurement($strClass, $strFunction, $strInformation = "", $mixID = null)
    {
        if (!$this->booMeasurement) {
            return;
        }

        if ($mixID == null) {
            $mixID = count($this->arrMeasurement);
        }

        $this->arrMeasurement[$mixID] = array(
            "class"       => $strClass,
            "function"    => $strFunction,
            "information" => $strInformation,
            "start"       => microtime(true),
            "mem_peak"    => 0,
            "mem_start"   => memory_get_usage(true),
            "mem_end"     => 0,
        );

        return $mixID;
    }

    /**
     * Stop a Measurement
     *
     * @param string $strClass
     * @param string $strFunction
     *
     * @return void
     */
    public function stopMeasurement($strClass, $strFunction, $mixID)
    {
        if (!$this->booMeasurement) {
            return;
        }

        $floStop = microtime(true);
        $floTime = $floStop - $this->arrMeasurement[$mixID]["start"];

        $this->arrMeasurement[$mixID] = array_merge($this->arrMeasurement[$mixID], array(
                "stop"     => $floStop,
                "time"     => $floTime,
                "mem_end"  => memory_get_usage(true),
                "mem_peak" => memory_get_peak_usage(true)
            )
        );
    }

    /**
     *
     * @param string $strDebugname
     * @param string $strValue
     */
    public function addDebug($strDebugname, $strValue)
    {
        $this->arrDebug[$strDebugname . " - " . microtime(true)] = $strValue;
    }

    /* -------------------------------------------------------------------------
     * Write Functions
     */

    /**
     * Write a txt file.
     *
     * @return void
     */
    protected function writeMeasurement()
    {
        try {
            if ($this->arrMeasurement === null || empty($this->arrMeasurement) || count($this->arrMeasurement) == 0) {
                return;
            }

            $objFile = new \File($this->strFileMeasurement);

            $intTime = time();

            $strContent = "";
            $strContent .= "\n>>|------------------------------------------------------";
            $strContent .= "\n>>|-- Start Measurement Core at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= "\n>>\n\n";

            foreach ($this->arrMeasurement as $key => $value) {
                $strContent .= "$key - Class: " . $value["class"] . "\tFunction: " . $value["function"] . "\tInformation: " . $value["information"] .
                               "\n\t\tStart: " . $value["start"] . "\tEnd: " . $value["stop"] . "\tExecutiontime: " . number_format($value["time"],
                        5, ",", ".") . " Sekunden" .
                               "\n\t\tStartMem: " . round($value["mem_start"] / 1048576,
                        4) . "MB\tEndMem: " . round($value["mem_end"] / 1048576,
                        4) . "MB\t\tPeakMem: " . round($value["mem_peak"] / 1048576, 4) . " MB" .
                               "\n|----\n";
            }

            $strContent .= "\n\n>>";
            $strContent .= "\n>>|-- Close Measurement Core at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= "\n>>|------------------------------------------------------\n";

            if (!$objFile->append($strContent)) {
                $this->log("Could not write CtoCom Measurement file.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            }

            $objFile->close();
        } catch (\RuntimeException $exc) {
            $this->log("Could not write CtoCom Measurement file. Exit with error: " . $exc->getMessage(),
                __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
        }
    }

    /**
     * Write a txt file.
     *
     * @return void
     */
    protected function writeDebug()
    {
        try {
            if ($this->arrDebug === null || empty($this->arrDebug) || count($this->arrDebug) == 0) {
                return;
            }

            $objFile = new \File($this->strFileDebug);

            $intTime = time();

            $strContent = "";

            $strContent .= "\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>";
            $strContent .= "\n  + Hinweis:";
            $strContent .= "\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>\n\n";
            $strContent .= ">>|------------------------------------------------------";
            $strContent .= "\n>>|-- Start Log at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= "\n>>";
            $strContent .= "\n";

            foreach ($this->arrDebug as $key => $value) {
                $strContent .= "\n";
                $strContent .= "<|-- Start " . $key . " -----------------------------------|>";
                $strContent .= "\n\n";

                $strContent .= trim($value);

                $strContent .= "\n\n";
                $strContent .= "<|-- End " . $key . " -------------------------------------|>";
                $strContent .= "\n";
                $strContent .= "\n";
            }

            $strContent .= "\n";
            $strContent .= "\n>>";
            $strContent .= "\n>>|-- Close Log at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= "\n>>|------------------------------------------------------\n";


            if (!$objFile->append($strContent)) {
                $this->log("Could not write CtoCom Debug file.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
            }

            $objFile->close();
        } catch (\RuntimeException $exc) {
            $this->log("Could not write CtoCom Measurement file. Exit with error: " . $exc->getMessage(),
                __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
        }
    }

}
