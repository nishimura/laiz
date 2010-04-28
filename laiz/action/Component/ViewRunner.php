<?php
/**
 * View Runner.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Container;

/**
 * View Runner.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  500
 */
class Component_ViewRunner implements Component
{
    private $response;
    private $container;
    public function __construct(Container $container, Response $res)
    {
        $this->response = $res;
        $this->container = $container;
    }

    public function run(Array $config)
    {
        $view = $this->container->create('laiz.view.View');
        $view->setTemplateDir($this->response->getTemplateDir());
        $view->execute($this->response);
    }
}
