<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

// Be silenced
@error_reporting(0);
@ini_set("display_errors", 0);

/**
 * Runonce Job
 */
class runonceJob extends \Backend
{

    //- Core Functions ---------------------------------------------------------

    public function __construct()
    {
        // Call parent
        parent::__construct();
    }

    /**
     * Run job
     */
    public function run()
    {
        try
        {
            // Api key            
            if (strlen($GLOBALS['TL_CONFIG']['ctoCom_APIKey']) == 0)
            {
                $objKey = $this->Database->prepare("SELECT UUID() as uid")->execute();
                $this->Config->add("\$GLOBALS['TL_CONFIG']['ctoCom_APIKey']", $objKey->uid);
                $this->log("Create an API Key for ctoCommunictaion.", "ctoCommunictaion Runonce", TL_GENERAL);
            }
        }
        catch (Exception $exc)
        {
            // Write log 
            $this->log("Error by creating an APIKey for ctoCommunictaion.", "ctoCommunictaion Runonce", TL_ERROR);            
        }

        try
        {
            // Database update
            $arrTables = $this->Database->listTables();

            if (in_array("tl_ctocom_cache", $arrTables))
            {
                $arrIndexes = $this->Database->prepare("SHOW INDEX FROM `tl_ctocom_cache`")->executeUncached()->fetchAllAssoc();

                foreach ($arrIndexes as $keyIndex => $valueIndex)
                {
                    if ($valueIndex["Key_name"] == "tstamp")
                    {
                        $this->Database->prepare("ALTER TABLE `tl_ctocom_cache` DROP INDEX `tstamp`")->execute();
                        $this->Database->prepare("ALTER TABLE `tl_ctocom_cache` ADD KEY `uid` (`uid`)")->execute();

                        break;
                    }
                }
            }
        }
        catch (\RuntimeException $exc)
        {
            $this->log("Error by updating database table tl_ctocom_cache.", "ctoCommunictaion Runonce", TL_ERROR);          
        }
    }

}

// Run once
$objRunonceJob = new runonceJob();
$objRunonceJob->run();

?>