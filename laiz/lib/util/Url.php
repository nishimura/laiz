<?php
/**
 * Url Utility Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\util;

/**
 * Url Utility Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Url
{
    private $https;
    private $host;

    public function __construct()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $this->https = true;
        else
            $this->https = false;
        $this->host = $_SERVER['HTTP_HOST'];
    }

    public function setHttps($flag)
    {
        $this->https = $flag;
    }

    public function switchHttps()
    {
        $this->https = !$this->https;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function cutSubdomain()
    {
        $this->host = preg_replace('/^[^.]+\./', '', $this->host);
    }

    public function getRoot()
    {
        $ret = '';
        if ($this->https)
            $ret .= 'https://';
        else
            $ret .= 'http://';
        $ret .= $this->host . '/';
    }
}
