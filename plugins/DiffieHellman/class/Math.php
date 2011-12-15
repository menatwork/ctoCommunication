<?php
/**
 * Math extension wrapper for DiffieHellman with some additional helper
 * methods for RNG and binary conversion.
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Copyright (c) 2005-2007, Pádraic Brady <padraic.brady@yahoo.com>
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
 * @category    Encryption
 * @package     Crypt_DiffieHellman
 * @author      Pádraic Brady <padraic.brady@yahoo.com>
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 * @version     $Id: Math.php 281415 2009-05-30 01:00:54Z shupp $
 * @link        http://
 */

/** Crypt_DiffieHellman_Math_BigInteger */
require_once 'Math/BigInteger.php';

/**
 * Crypt_DiffieHellman_Math class
 *
 * Example usage:
 *      $math = new Crypt_DiffieHellman_Math;
 *      $binaryForm = $math->toBinary('384834813984910010746469093412498181642341794');
 *      $numberForm = $math->fromBinary($binaryForm);
 *
 *      $math = new Crypt_DiffieHellman_Math('gmp');
 *      $randomNumber = $math->rand(2, '384834813984910010746469093412498181642341794');
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
class Crypt_DiffieHellman_Math extends Crypt_DiffieHellman_Math_BigInteger
{

    /**
     * Generate a pseudorandom number within the given range.
     * Will attempt to read from a systems RNG if it exists.
     *
     * @param string|int $min
     * @param string|int $max
     * @return string
     * @todo Even more pseudorandomness would be nice...
     */
    public function rand($minimum, $maximum)
    {
        if (file_exists('/dev/urandom')) {
            $frandom = fopen('/dev/urandom', 'r');
            if ($frandom !== false) {
                return fread($frandom, strlen($maximum) - 1);
            }
        }
        if (strlen($maximum) < 4) {
            return mt_rand($minimum, $maximum - 1);
        }
        $rand = '';
        $i2 = strlen($maximum) - 1;
        for ($i = 1;$i < $i2;$i++) {
            $rand .= mt_rand(0,9);
        }
        $rand .= mt_rand(0,9);
        return $rand;
    }

    /**
     * Perform a btwoc operation on the given BigInteger number in
     * binary form which returns the big-endian two's complement.
     *
     * @param string $long
     * @return string
     */
    public function btwoc($long) {
        if (ord($long[0]) > 127) {
            return "\x00" . $long;
        }
        return $long;
    }

    /**
     * Convert a Binary value into a BigInteger number
     *
     * @param string $binary
     * @return string
     */
    public function fromBinary($binary) {
        if (!$this instanceof Crypt_DiffieHellman_Math_BigInteger_Gmp) {
            $big = 0;
            $length = mb_strlen($binary, '8bit');
            for ($i = 0; $i < $length; $i++) {
                $big = $this->_math->multiply($big, 256);
                $big = $this->_math->add($big, ord($binary[$i]));
            }
            return $big;
        } else {
            return $this->_math->init(bin2hex($binary), 16); // gmp shortcut
        }
    }

    /**
     * Convert a BigInteger number into binary
     *
     * @param string $big
     * @return string
     */
    public function toBinary($big)
    {
        if (!$this instanceof Crypt_DiffieHellman_Math_BigInteger_Gmp) {
            $compare = $this->_math->compare($big, 0);
            if ($compare == 0) {
                return (chr(0));
            } else if ($compare < 0) {
                return false;
            }
            $binary = null;
            while ($this->_math->compare($big, 0) > 0) {
                $binary = chr($this->_math->modulus($big, 256)) . $binary;
                $big = $this->_math->divide($big, 256);
            }
            return $binary;
        } else {
            return pack("H*", gmp_strval($big, 16));
        }
    }
}
