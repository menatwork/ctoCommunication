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
 * @copyright  MEN AT WORK 2012 
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * System configuration
 */
// Palettes Insert
$arrPalettes = explode(";", $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = implode(";", array_merge(array_slice($arrPalettes, 0, 1), array('{ctoCommunication_legend},ctoCom_APIKey,ctoCom_responseLength'), array_slice($arrPalettes, 1)));

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['ctoCom_APIKey'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ctoCom_APIKey'],
    'inputType' => 'text',
    'explanation' => 'ctoComKey',
    'eval' => array('helpwizard' => true, 'tl_class' => 'long', 'minlength' => '32', 'maxlength' => '64'),
    'exclude' => true,
    'save_callback' => array(array('CtoCommunicationSettings', 'save_callback')),
);
// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['ctoCom_responseLength'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ctoCom_responseLength'],
    'inputType' => 'text',
    'eval' => array('rgxp' => 'digit', 'tl_class' => 'long', 'maxlength' => '64', 'minlength' => '5'),
    'exclude' => true,
);


class CtoCommunicationSettings extends Backend
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate the sec key for server
     * 
     * @param type $varValue
     * @param DataContainer $dca
     * @return type 
     */
    public function save_callback($varValue, DataContainer $dca)
    {
        if ($varValue == "")
        {
            $objKey = $this->Database->prepare("SELECT UUID() as uid")->execute();
            return $objKey->uid;
        }

        return $varValue;
    }

}

?>