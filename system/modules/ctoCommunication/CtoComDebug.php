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

/**
 * Debug class
 */
class CtoComDebug extends Backend
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

        $this->strFileDebug = "system/tmp/CtoComDebug.txt";
        $this->strFileMeasurement = "system/tmp/CtoComMeasurement.txt";

        $this->booDebug = false;
        $this->booMeasurement = false;
    }
    
    public function __destruct()
    {
        if ($this->booDebug)
            $this->writeDebug();

        if ($this->booMeasurement)
            $this->writeMeasurement();
    }

    /**
     * Get instance. 
     * 
     * @return CtoComDebug 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new CtoComDebug();

        return self::$instance;
    }

    public function __get($name)
    {
        switch ($name)
        {
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
        switch ($name)
        {
            case "activateMeasurement":
                $this->booMeasurement = (boolean) $value;
                break;

            case "activateDebug":
                $this->booDebug = (boolean) $value;
                break;

            case "pathMeasurement":
                $this->strFileMeasurement = $value;
                break;

            case "pathDebug":
                $this->strFileDebug = $value;
                break;

            default:
                throw new Exception("Unknown set typ: " . $name);
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
     * @return void 
     */
    public function startMeasurement($strClass, $strFunction, $strInformation = "")
    {
        if (!$this->booMeasurement)
            return;

        $this->arrMeasurement[$strClass . "|" . $strFunction] = array(
            "class" => $strClass,
            "function" => $strFunction,
            "information" => $strInformation,
            "start" => microtime(true),
            "mem_peak" => 0,
            "mem_start" => memory_get_usage(true),
            "mem_end" => 0,
        );
    }

    /**
     * Stop a Measurement
     * 
     * @param string $strClass
     * @param string $strFunction
     * @return void 
     */
    public function stopMeasurement($strClass, $strFunction)
    {
        if (!$this->booMeasurement)
            return;

        $floStop = microtime(true);
        $floTime = $floStop - $this->arrMeasurement[$strClass . "|" . $strFunction]["start"];

        $this->arrMeasurement[$strClass . "|" . $strFunction] = array_merge($this->arrMeasurement[$strClass . "|" . $strFunction], array(
            "stop" => $floStop,
            "time" => $floTime,
            "mem_end" => memory_get_usage(true),
            "mem_peak" => memory_get_peak_usage(true))
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
        try
        {
            $objFile = new File($this->strFileMeasurement);
            
            $intTime = time();

            if (count($this->arrMeasurement) == 0)
            {
                $objFile->close();
                return;
            }

            $strContent = "";
            $strContent .= "\n>>|------------------------------------------------------";
            $strContent .= "\n>>|-- Start Measurement Core at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= "\n>>\n\n";

            foreach ($this->arrMeasurement as $key => $value)
            {
                $strContent .= "Class: " . $value["class"] . "\tFunction: " . $value["function"] . "\tInformation: " . $value["information"] .
                        "\n\t\tStart: " . $value["start"] . "\tEnd: " . $value["stop"] . "\tExecutiontime: " . number_format($value["time"], 5, ",", ".") . " Sekunden" .
                        "\n\t\tStartMem: " . round($value["mem_start"] / 1048576, 4) . "MB\tEndMem: " . round($value["mem_end"] / 1048576, 4) . "MB\t\tPeakMem: " . round($value["mem_peak"] / 1048576, 4) . " MB" .
                        "\n|----\n";
            }

            $strContent .= "\n\n>>";
            $strContent .= "\n>>|-- Close Measurement Core at " . date("H:i:s d.m.Y", $intTime);
            $strContent .= "\n>>|------------------------------------------------------\n";

            if (!$objFile->append($strContent))
                $this->log("Could not write CtoCom Measurement file.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

            $objFile->close();
        }
        catch (Exception $exc)
        {
            $this->log("Could not write CtoCom Measurement file. Exit with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
        }
    }

    /**
     * Write a txt file.
     * 
     * @return void 
     */
    protected function writeDebug()
    {  
        try
        {
            $objFile = new File($this->strFileDebug);
            
            $intTime = time();

            if (count($this->arrDebug) == 0)
            {
                $objFile->close();
                return;
            }

            $strContent = "";

            $strContent .= "\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>";
            $strContent .="\n  + Hinweis:";
            $strContent .="\n<|++++++++++++++++++++++++++++++++++++++++++++++++++++++|>\n\n";
            $strContent .=">>|------------------------------------------------------";
            $strContent .="\n>>|-- Start Log at " . date("H:i:s d.m.Y", $intTime);
            $strContent .="\n>>";
            $strContent .="\n";

            foreach ($this->arrDebug as $key => $value)
            {
                $$strContent .="\n";
                $strContent .="<|-- Start " . $key . " -----------------------------------|>";
                $strContent .="\n\n";

                $strContent .=trim($value);

                $strContent .="\n\n";
                $strContent .="<|-- End " . $key . " -------------------------------------|>";
                $strContent .="\n";
                $strContent .="\n";
            }

            $strContent .="\n";
            $strContent .="\n>>";
            $strContent .="\n>>|-- Close Log at " . date("H:i:s d.m.Y", $intTime);
            $strContent .="\n>>|------------------------------------------------------\n";
            
        

            if (!$objFile->append($strContent))
                $this->log("Could not write CtoCom Debug file.", __FUNCTION__ . " | " . __CLASS__, TL_ERROR);

            $objFile->close();          
        }
        catch (Exception $exc)
        {
            $this->log("Could not write CtoCom Measurement file. Exit with error: " . $exc->getMessage(), __FUNCTION__ . " | " . __CLASS__, TL_ERROR);
        }
    }

}

?>