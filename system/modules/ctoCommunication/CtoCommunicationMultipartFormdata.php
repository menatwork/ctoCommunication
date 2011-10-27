<?php

if (!defined('TL_ROOT'))
    die('You can not access this file directly!');

/**
 * PHP version 5
 * @copyright	Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package		RequestExtended
 * @license		LGPL 
 * @filesource
 */
if (!defined('CRLF'))
    define('CRLF', "\r\n");

/**
 * Class MultipartFormdata
 *
 * Provide methods to encode MultipartFormdata content for HTTP POST requests.
 * @copyright  Christian Schiffler 2009
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    Library
 */
class CtoCommunicationMultipartFormdata
{

    /**
     * The boundary to use for form data.
     */
    protected $boundary;

    /**
     * The boundary to use for form data.
     */
    protected $fields = array();

    /**
     * Set default values
     */
    public function __construct()
    {
        $this->boundary = uniqid();
    }

    public function setField($name, $value)
    {
        $this->fields[$name] = array('value' => $value);
    }

    public function setFileField($name, $filename, $contentType='', $encoding='binary')
    {
        if (!file_exists($filename))
            return false;
        $this->fields[$name] = array('value' => 'file', 'filename' => $filename, 'contentType' => $contentType, 'encoding' => $encoding);
        return true;
    }

    public function getContentTypeHeader($nested=false)
    {
        return 'multipart/' . ($nested ? 'mixed' : 'form-data') . ', boundary=' . $this->boundary;
    }

    public function compile()
    {
        foreach ($this->fields as $name => $data)
        {
            $ret .= vsprintf("--%s\n", array($this->boundary));

            if (isset($data['filename']))
            {
                $ret .= vsprintf("Content-Disposition: form-data; name=\"%s\"; filename=\"%s\"" . CRLF, array($name, basename($data['filename'])));
                $ret .= "Content-Type: application/octet-stream" . CRLF;
                $ret .= CRLF;
                $ret .= file_get_contents($data['filename']) . CRLF;
            }
            else
            {
                $ret .= vsprintf("Content-Disposition: form-data; name=\"%s\"" . CRLF, array($name));
                $ret .= CRLF;
                $ret .= $data["value"] . CRLF;
            }
        }

        $ret .= vsprintf("--%s--\n", array($this->boundary));
        $ret .= CRLF;

        return $ret;
    }

}

?>