<?php
/**
 * Implementation of the Diffie-Hellman Key Exchange cryptographic protocol
 * in PHP5. Enables two parties without any prior knowledge each other
 * establish a secure shared secret key across an insecure channel
 * of communication.
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Copyright (c) 2005-2007 Pádraic Brady <padraic.brady@yahoo.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the 
 *      documentation and/or other materials provided with the distribution.
 *    * The name of the author may not be used to endorse or promote products 
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Crypt
 * @package     Crypt_DiffieHellman
 * @author      Pádraic Brady <padraic.brady@yahoo.com>
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 * @version     $Id: DiffieHellman.php 294958 2010-02-12 02:40:31Z clockwerx $
 * @link        http://
 */

/** Crypt_DiffieHellman_Math */
require_once TL_ROOT . '/plugins/DiffieHellman/class/Math.php';

/**
 * Crypt_DiffieHellman class
 *
 * Example usage:
 *      Bob and Alice have started to communicate and wish to establish a
 *      shared common secret key with which to sign messages.
 *      Both establish two common pieces of information:
 *          - a large prime number
 *          - a generator number
 *      Both also generate a private key (different for each) and a public
 *      key. They then transmit their public keys to each other, and agree
 *      on the prime and generator also.
 *      Both then perform identical sets of Diffie Hellman calculations and
 *      calculate a key which only each could calculate.
 *
 *      This is secure for a very simple reason - no other party can reverse
 *      engineer the public keys to get hold of the private keys which are
 *      essential pieces of calculating the Diffie-Hellman shared secret.
 *      The algorithm ensures this by using Modular Exponentiation which
 *      expresses a one-way-function behaviour (it's computationally
 *      infeasible to reverse it).
 *
 *      Using the data below, both will agree a shared secret key of 117.
 *
 *      Alice: prime = 563
 *             generator = 5
 *             private key = 9
 *      Bob:   prime = 563
 *             generator = 5 
 *             private key = 14
 *      
 *      $alice = new Crypt_DiffieHellman(563, 5, 9);
 *      $alice_pubKey = alice->generateKeys()->getPublicKey();
 *      $bob = new Crypt_DiffieHellman(563, 5, 14);
 *      $bob_pubKey = $bob->generateKeys()->getPublicKey();
 *
 *      // the public keys are then exchanged (with agreed prime and generator)
 *      
 *      $alice_computeKey = $alice->computeSecretKey($bob_pubKey)->getSharedSecretKey();
 *      $bob_computeKey = $bob->computeSecretKey($alice_pubKey)->getSharedSecretKey();
 *
 *      assert($alice_computeKey == $bob_computeKey);
 *
 *      Alice and Bob have now established the same shared secret key of 117.
 *      They may now sign exchanged messages which the other party may then
 *      authenticate upon receipt.
 *
 *      In order to facilitate the practice of transmitting large integers in
 *      their binary form, input and output methods may accept an additional
 *      parameter of Crypt_DiffieHellman::BINARY to tell this method when the
 *      input/output should be converted from, or to, binary form. An alternate
 *      parameter of Crypt_DiffieHellman::BTWOC is used only for output methods
 *      and returns the binary big-endian twos complement of the binary form to
 *      maintain consistent binary conversion across platforms.
 *
 *      Although the example above uses a simple prime number, it is important
 *      to always use a sufficiently large prime, preferably one of the primes
 *      deemed to have positive cryptographic qualities. The generator is
 *      always a number less than the prime number.
 *      
 * 
 * @category   Encryption
 * @package    Crypt_DiffieHellman
 * @author     Pádraic Brady <padraic.brady@yahoo.com>
 * @copyright  2005-2007 Pádraic Brady
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://
 * @version    @package_version@
 * @access     public
 */
class Crypt_DiffieHellman
{

    /**
     * Default large prime number; required by the algorithm. 
     *
     * @var string
     */
    private $_prime = null;

    /**
     * The default generator number. This number must be greater than 0 but
     * less than the prime number set.
     * @var string
     */
    private $_generator = null;

    /**
     * A private number set by the local user. It's optional and will
     * be generated if not set.
     *
     * @var string
     */
    private $_privateKey = null;

    /**
     * BigInteger support object courtesy of Zend_Math with some additional
     * functions such as binary conversion and a RNG method which attempts to
     * use /dev/urandom or a less cryptographically secure algorithm if a
     * system based RNG cannot be found.
     *
     * @var Crypt_DiffieHellman_Math
     */
    private $_math = null;

    /**
     * The public key generated by this instance after calling generateKeys().
     *
     * @var string
     */
    private $_publicKey = null;

    /**
     * The shared secret key resulting from a completed Diffie Hellman
     * exchange
     *
     * @var string
     */
    private $_secretKey = null;

    /**
     * Constants; used to define inputs or outputs as binary or big numbers.
     * Binary form is often used as the exchange form for public keys.
     */
    const BINARY = 'binary';
    const NUMBER = 'number';
    const BTWOC  = 'btwoc';

    /**
     * Constructor; if set construct the object using the parameter array to
     * set values for Prime, Generator and Private.
     * If a Private Key is not set, one will be generated at random.
     *
     * @param string|integer $prime
     * @param string|integer $generator
     * @param string|integer $privateKey
     * @param string $privateKeyType
     * @param string $mathExtension
     */
    public function __construct($prime, $generator, $privateKey = null, $privateKeyType = null, $mathExtension = null)
    {
        $this->setPrime($prime);
        $this->setGenerator($generator);
        if (!is_null($privateKey)) {
            if (is_null($privateKeyType)) {
                $privateKeyType = self::NUMBER;
            }
            $this->setPrivateKey($privateKey, $privateKeyType);
        }
        $this->setBigIntegerMath($mathExtension);
    }

    /**
     * Generate own public key. If a private number has not already been
     * set, one will be generated at this stage.
     *
     * @return Crypt_DiffieHellman
     */
    public function generateKeys()
    {
        $this->_publicKey = $this->_math->powmod($this->getGenerator(), $this->getPrivateKey(), $this->getPrime());
        return $this;
    }

    /**
     * Returns own public key for communication to the second party to this
     * transaction.
     *
     * @param string $type
     * @return string
     */
    public function getPublicKey($type = self::NUMBER)
    {
        if (is_null($this->_publicKey)) {
            require_once TL_ROOT . '/plugins/DiffieHellman/class/Exception.php';
            throw new Crypt_DiffieHellman_Exception('A public key has not yet been generated using a prior call to generateKeys()');
        }
        if ($type == self::BINARY) {
            return $this->_math->toBinary($this->_publicKey);
        } elseif ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_publicKey));
        }
        return $this->_publicKey;
    }

    /**
     * Compute the shared secret key based on the public key received from the
     * the second party to this transaction. This should agree to the secret
     * key the second party computes on our own public key.
     * Once in agreement, the key is known to only to both parties.
     * By default, the function expects the public key to be in binary form
     * which is the typical format when being transmitted.
     *
     * @param string $publicKey
     * @param string $type
     * @return void
     */
    public function computeSecretKey($publicKey, $type = self::NUMBER)
    {
        if ($type == self::BINARY) {
            $publicKey = $this->_math->fromBinary($publicKey);
        }
        if (!preg_match("/^\d+$/", $publicKey)) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number');
        }
        $this->_secretKey = $this->_math->powmod($publicKey, $this->getPrivateKey(), $this->getPrime());
        return $this;
    }

    /**
     * Return the computed shared secret key from the DiffieHellman transaction
     *
     * @param string $type
     * @return string
     */
    public function getSharedSecretKey($type = self::NUMBER)
    {
        if (!isset($this->_secretKey)) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('A secret key has not yet been computed; call computeSecretKey()');
        }
        if ($type == self::BINARY) {
            return $this->_math->toBinary($this->_secretKey);
        } elseif ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_secretKey));
        }
        return $this->_secretKey;
    }

    /**
     * Setter for the value of the prime number
     *
     * @param string $number
     * @return Crypt_DiffieHellman
     */
    public function setPrime($number)
    {
        if (!preg_match("/^\d+$/", $number) || $number < 11) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number or too small: should be a large natural number prime');
        }
        $this->_prime = (string) $number;
        return $this;
    }

    /**
     * Getter for the value of the prime number
     *
     * @param string $type
     * @return string
     */
    public function getPrime($type = self::NUMBER)
    {
        if (!isset($this->_prime)) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('No prime number has been set');
        }

        if ($type == self::NUMBER) {
            return $this->_prime;
        } else if ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_prime));
        }

        return $this->_math->toBinary($this->_prime);
    }

    /**
     * Setter for the value of the generator number
     *
     * @param string $number
     * @return Crypt_DiffieHellman
     */
    public function setGenerator($number)
    {
        if (!preg_match("/^\d+$/", $number) || $number < 2) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number greater than 1');
        }
        $this->_generator = (string) $number;
        return $this;
    }

    /**
     * Getter for the value of the generator number
     *
     * @param string $type
     * @return string
     */
    public function getGenerator($type = self::NUMBER)
    {
        if (!isset($this->_generator)) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('No generator number has been set');
        }
        if ($type == self::NUMBER) {
            return $this->_generator;
        } else if ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_generator));
        }
        return $this->_math->toBinary($this->_generator);
    }

    /**
     * Setter for the value of the private number
     *
     * @param string|integer $number
     * @param string $type
     * @return Crypt_DiffieHellman
     */
    public function setPrivateKey($number, $type = self::NUMBER)
    {
        if ($type == self::BINARY) {
            $number = $this->_math->fromBinary($number);
        }
        if (!preg_match("/^\d+$/", $number)) {
            require_once(TL_ROOT . '/plugins/DiffieHellman/class/Exception.php');
            throw new Crypt_DiffieHellman_Exception('invalid parameter; not a positive natural number');
        }
        $this->_privateKey = (string) $number;
        return $this;
    }

    /**
     * Getter for the value of the private number
     *
     * @param string $type
     * @return string
     */
    public function getPrivateKey($type = self::NUMBER)
    {
        if (!isset($this->_privateKey)) {
            $this->setPrivateKey($this->_generatePrivateKey(), self::BINARY);
        }
        if ($type == self::BINARY) {
            return $this->_math->toBinary($this->_privateKey);
        } elseif ($type == self::BTWOC) {
            return $this->_math->btwoc($this->_math->toBinary($this->_privateKey));
        }
        return $this->_privateKey;
    }

    /**
     * Setter to pass an extension parameter which is used to create
     * a specific BigInteger instance for a specific extension type.
     * Allows manual setting of the class in case of an extension
     * problem or bug.
     *
     * Due to the temporary nature of BigInteger wrapper, this decision
     * is deferred to Crypt_DiffieHellman_Math which extends (in a
     * slightly reversed way) Crypt_DiffieHellman_Math_BigInteger.
     *
     * @param string $extension
     * @return void
     */
    public function setBigIntegerMath($extension = null)
    {
        $this->_math = new Crypt_DiffieHellman_Math($extension);
    }

    /**
     * In the event a private number/key has not been set by the user,
     * generate one at random.
     *
     * @return string
     */
    protected function _generatePrivateKey()
    {
        $rand = $this->_math->rand($this->getGenerator(), $this->getPrime());
        return $rand;
    }

}
