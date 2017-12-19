<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\CtoCommunicationBundle\Codifyengine;

/**
 * CtoComCodifyengineImpl_Empty
 */
class NoneCrypt extends Base
{
    /**
     * Encrypt a string.
     *
     * @param string $text The content for the encryption.
     *
     * @return string The encrypted string
     */
    public function Encrypt($text)
    {
        return $text;
    }

    /**
     * Decrypt a string.
     *
     * @param string $text The content for the decryption.
     *
     * @return string The decrypted string
     */
    public function Decrypt($text)
    {
        return trim($text);
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName()
    {
        return 'empty';
    }

    /**
     * Called after the setKey function for setting the key for the crypt class.
     *
     * @return void
     */
    protected function setCryptClassKey()
    {
        // Nothing to do.
    }
}
