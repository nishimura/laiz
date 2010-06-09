<?php
/**
 * View Interface Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\view;

use \laiz\action\Response;

/**
 * View Interface Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface View
{
    public function execute(Response $res);
    public function getTemplateExtension();
    public function setTemplateExtension($templateExtension);
    public function bufferedOutput(Response $res);
}
