<?php
/**
 * View Configure Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * View Configure Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  300
 */
class Component_ViewConfigure implements Component
{
    private $response;
    public function __construct(Response $res)
    {
        $this->response = $res;
    }

    public function run(Array $config)
    {
        $this->response
            ->setTemplateDir($config['templateDir'])
            ->setTemplateName($config['templateName']);
    }
}
