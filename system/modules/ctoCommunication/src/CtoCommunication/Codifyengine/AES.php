<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    ctoCommunication
 * @license    GNU/LGPL
 * @filesource
 */

namespace CtoCommunication\Codifyengine;

/**
 * CtoComCodifyengineImpl_AES
 */
class AES extends Base
{
    /**
     * The AES class.
     *
     * @var \phpseclib\Crypt\AES
     */
    protected $aes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aes = new \phpseclib\Crypt\AES();
    }

    /**
     * Called after the setKey function for setting the key for the crypt class.
     *
     * @return void
     */
    protected function setCryptClassKey()
    {
        $this->aes->setKey($this->getKey());
    }

    /**
     * Encrypt a string.
     *
     * @param string $text The content for the encryption.
     *
     * @return string The encrypted string
     *
     * @throws \Exception If the system was not able to generate a iv.
     */
    public function Encrypt($text)
    {
        // Init the iv.
        if(\function_exists('random_bytes')){
            $iv = \random_bytes(16);
            $iv = bin2hex($iv);
        } else if(\extension_loaded('mcrypt') && \function_exists('mcrypt_create_iv')){
            $iv = \mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        } else {
            throw new \RuntimeException('Could not find a fitting function for generating the IV.');
        }
        $this->aes->setIV($iv);

        // Crypt.
        return $iv . "|@|" . $this->aes->encrypt($text);
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
        $text = $this->splitText($text);

        // Set the iv.
        $this->aes->setIV($text[0]);

        // Decrypt.
        return $this->aes->decrypt($text[1]);
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName()
    {
        return 'aes';
    }
}
