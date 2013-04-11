<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    ctoCommunication
 * @license    GNU/LGPL 
 * @filesource
 */

/**
 * System configuration
 */

// Palettes Insert
$arrPalettes = explode(";", $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = implode(";", array_merge(array_slice($arrPalettes, 0, 1), array('{ctoCommunication_legend},ctoCom_APIKey,ctoCom_responseLength,ctoCom_handshake'), array_slice($arrPalettes, 1)));

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['ctoCom_APIKey'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ctoCom_APIKey'],
    'inputType' => 'text',
    'explanation' => 'ctoComKey',
    'eval' => array('helpwizard' => true, 'tl_class' => 'long', 'minlength' => '32', 'maxlength' => '64'),
    'exclude' => true,
    'save_callback' => array(array('CtoCommunicationSettings', 'save_callback')),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['ctoCom_responseLength'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ctoCom_responseLength'],
    'inputType' => 'text',
    'eval' => array('rgxp' => 'digit', 'tl_class' => 'long', 'maxlength' => '64', 'minlength' => '5'),
    'exclude' => true,
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['ctoCom_handshake'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['handshake'],
    'inputType' => 'checkbox',
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