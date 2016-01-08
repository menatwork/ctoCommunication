<?php
/**
 * Created by PhpStorm.
 * User: stefan.heimes
 * Date: 08.01.2016
 * Time: 15:15
 */

namespace CtoCommunication\Helper;


class Config
{
    /**
     * Return the max response length.
     *
     * @return int
     */
    public function getResponseLength()
    {
        if (empty($GLOBALS['TL_CONFIG']['ctoCom_responseLength']) || $GLOBALS['TL_CONFIG']['ctoCom_responseLength'] < 10000) {
            return -1;
        } else {
            return $GLOBALS['TL_CONFIG']['ctoCom_responseLength'];
        }
    }

    /**
     * Return the default wait time for the connection.
     *
     * @return float
     */
    public function getConnectionTimeout()
    {
        return 10.0;
    }
}
