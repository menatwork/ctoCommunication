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
 * Interface for Codifyengine
 */
abstract class Base
{
    /**
     * The encrypt and decrypt key.
     *
     * @var string
     */
    protected $key;

    /**
     * Return the crypt/decrypt key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Set the crypt/decrypt key.
     *
     * @param string $key    The password.
     *
     * @param int    $length The max length of the key.
     */
    public function setKey($key, $length = 16)
    {
        // Make some replacements.
        $key = str_replace(array("-", "_"), array("", ""), $key);

        // Check the length
        if (strlen($key) < $length) {
            $key .= str_repeat("5", strlen($key) - $length);
        } else if (strlen($key) > $length) {
            $key = substr($key, 0, $length);
        }

        // Save the key.
        $this->key = $key;

        // Set the key for the crypt class.
        $this->setCryptClassKey();
    }

    /**
     * Called after the setKey function for setting the key for the crypt class.
     *
     * @return void
     */
    abstract protected function setCryptClassKey();

    /**
     * Decrypt a string.
     *
     * @param string $text The content for the decryption.
     *
     * @return string The decrypted string
     */
    abstract public function Decrypt($text);

    /**
     * Encrypt a string.
     *
     * @param string $text The content for the encryption.
     *
     * @return string The encrypted string
     */
    abstract public function Encrypt($text);

    /**
     * Split a messages on the delimiter and run some checks.
     *
     * @param string $text The message with the iv.
     *
     * @return array A list first item is the iv the second key the message.
     */
    protected function splitText(&$text)
    {
        // Check the delimiter.
        if (stripos($text, "|@|") === false) {
            throw new \RuntimeException('Could not find the delimiter in the response.');
        }

        // Get the iv.
        $textPart = explode("|@|", $text);

        // If we have only one it means there is no content for the descrpytion.
        if (count($textPart) == 1) {
            return array($textPart[0], '');
        }

        // else return as is it.
        return $textPart;
    }

}
