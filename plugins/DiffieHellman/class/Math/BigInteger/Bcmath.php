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
 * @version     $Id: Bcmath.php 297455 2010-04-04 14:06:43Z shupp $
 * @link        http://
 */

/** Crypt_DiffieHellman_Math_BigInteger_Interface */
require_once TL_ROOT . '/plugins/DiffieHellman/class/Math/BigInteger/Interface.php';

/**
 * Support for arbitrary precision mathematics in PHP.
 *
 * Crypt_DiffieHellman_Math_BigInteger_Bcmath is a wrapper across the PHP BCMath extension.
 *
 * @category   Encryption
 * @package    Crypt_DiffieHellman
 * @subpackage BigInteger
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Crypt_DiffieHellman_Math_BigInteger_Bcmath implements Crypt_DiffieHellman_Math_BigInteger_Interface
{

    /**
     * Initialise a big integer into an extension specific type. This is not
     * applicable to BCMath.
     * @param string $operand
     * @param int $base
     * @return string
     */
    public function init($operand, $base = 10)
    {
        return $operand;
    }

    /**
     * Adds two arbitrary precision numbers
     *
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function add($left_operand, $right_operand)
    {
        return bcadd($left_operand, $right_operand);
    }

    /**
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function subtract($left_operand, $right_operand)
    {
        return bcsub($left_operand, $right_operand);
    }

    /**
     * Compare two big integers and returns result as an integer where 0 means
     * both are identical, 1 that left_operand is larger, or -1 that
     * right_operand is larger.
     * @param string $left_operand
     * @param string $right_operand
     * @return int
     */
    public function compare($left_operand, $right_operand)
    {
        return bccomp($left_operand, $right_operand);
    }

    /**
     * Divide two big integers and return result or NULL if the denominator
     * is zero.
     * @param string $left_operand
     * @param string $right_operand
     * @return string|null
     */
    public function divide($left_operand, $right_operand)
    {
        return bcdiv($left_operand, $right_operand);
    }

    /**
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function modulus($left_operand, $modulus)
    {
        return bcmod($left_operand, $modulus);
    }

    /**
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function multiply($left_operand, $right_operand)
    {
        return bcmul($left_operand, $right_operand);
    }

    /**
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function pow($left_operand, $right_operand)
    {
        return bcpow($left_operand, $right_operand);
    }

    /**
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function powmod($left_operand, $right_operand, $modulus)
    {
        return bcpowmod($left_operand, $right_operand, $modulus);
    }

    /**
     * @param string $left_operand
     * @param string $right_operand
     * @return string
     */
    public function sqrt($operand)
    {
        return bcsqrt($operand);
    }

}
