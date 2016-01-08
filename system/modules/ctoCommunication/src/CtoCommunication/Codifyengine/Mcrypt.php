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
 * CtoComCodifyengineImpl_Mcrypt
 */
class Mcrypt extends Base
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
        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
        $ks = mcrypt_enc_get_key_size($td);

        /* Create key */
        $key = substr(md5($this->key), 0, $ks);

        /* Intialize encryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Encrypt data */
        $encrypted = mcrypt_generic($td, $text);

        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        /* Show string */

        return $iv . "|@|" . $encrypted;
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
        // Get the iv and message.
        $text = $this->splitText($text);

        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */

        $iv = $text[0];
        $ks = mcrypt_enc_get_key_size($td);

        /* Create key */
        $key = substr(md5($this->key), 0, $ks);

        /* Initialize encryption module for decryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Decrypt encrypted string */
        $decrypted = mdecrypt_generic($td, $text[1]);

        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        /* Show string */

        return $decrypted;
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

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName()
    {
        return 'mcrypt';
    }
}
