<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  MEN AT WORK 2012 
 * @package    slideItMoo
 * @license    GNU/LGPL 
 * @filesource
 */

// Be silenced
@error_reporting(0);
@ini_set("display_errors", 0);

/**
 * Runonce Job
 */
class runonceJob extends Backend
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
        catch (Exception $exc)
        {
            $this->log("Error by updating database table tl_ctocom_cache.", "ctoCommunictaion Runonce", TL_ERROR);          
        }
    }

}

// Run once
$objRunonceJob = new runonceJob();
$objRunonceJob->run();
?>