<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

use Contao\Config;
use Contao\Database;

// Be silenced
@error_reporting(0);
@ini_set("display_errors", 0);

/**
 * Runonce Job
 */
class runonceJob
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
        try {
            // Api key            
            if (strlen($GLOBALS['TL_CONFIG']['ctoCom_APIKey']) == 0) {
                $objKey = Database::getInstance()->prepare("SELECT UUID() as uid")->execute();
                Config::getInstance()->add("\$GLOBALS['TL_CONFIG']['ctoCom_APIKey']", $objKey->uid);
            }
        } catch (Exception $exc) {

        }

        try {
            // Database update
            $arrTables = Database::getInstance()->listTables();

            if (in_array("tl_ctocom_cache", $arrTables)) {
                $arrIndexes = Database::getInstance()->prepare("SHOW INDEX FROM `tl_ctocom_cache`")->execute()->fetchAllAssoc();

                foreach ($arrIndexes as $keyIndex => $valueIndex) {
                    if ($valueIndex["Key_name"] == "tstamp") {
                        Database::getInstance()->prepare("ALTER TABLE `tl_ctocom_cache` DROP INDEX `tstamp`")->execute();
                        Database::getInstance()->prepare("ALTER TABLE `tl_ctocom_cache` ADD KEY `uid` (`uid`)")->execute();

                        break;
                    }
                }
            }
        } catch (\RuntimeException $exc) {
        }
    }
}

// Run once
$objRunonceJob = new runonceJob();
$objRunonceJob->run();