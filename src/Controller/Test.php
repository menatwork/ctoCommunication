<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 19.12.2017
 * Time: 18:09
 */

namespace MenAtWork\CtoCommunicationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Test
 *
 * @package MenAtWork\CtoCommunicationBundle\Controller
 */
class Test
{
    public function __construct()
    {

    }

    public function test()
    {

    }

    public function execute(Request $request)
    {
        $request->get('key');

        return new JsonResponse(['status' => 'okay']);
    }
}
