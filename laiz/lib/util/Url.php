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
    private $pathInfo;

    public function __construct()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $this->https = true;
        else
            $this->https = false;

        if (isset($_SERVER['HTTP_HOST']))
            $this->host = $_SERVER['HTTP_HOST'];
        else
            $this->host = gethostname();

        if (isset($_SERVER['PATH_INFO']))
            $this->pathInfo = $_SERVER['PATH_INFO'];
        else
            $this->pathInfo = '/';
    }

    public function setHttps($flag)
    {
        $this->https = $flag;
        return $this;
    }

    public function switchHttps()
    {
        $this->https = !$this->https;
        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function cutSubdomain()
    {
        $this->host = preg_replace('/^[^.]+\./', '', $this->host);
        return $this;
    }

    public function addSubdomain($domain)
    {
        $this->host = $domain . '.' . $this->host;
        return $this;
    }

    public function getPrefix()
    {
        $ret = '';
        if ($this->https)
            $ret .= 'https://';
        else
            $ret .= 'http://';
        $ret .= $this->host;
        return $ret;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getRoot()
    {
        return $this->getPrefix() . '/';
    }

    public function getPath()
    {
        return $this->getPrefix() . $this->pathInfo;
    }

    public function setAction($action)
    {
        $this->pathInfo = preg_replace('@^/[^/]+@', '/' . $action, $this->pathInfo);
        return $this;
    }
}
