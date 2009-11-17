<?php
/**
 * File of template method pattern for action classes.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Template method pattern for action classes.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Runner
{
    private $classes = array();
    private $request;
    private $container;
    private $merger;
    private $componentRunner;

    public function __construct(Laiz_Request $req, Laiz_Container $container
                                , Laiz_Action_Config_Merger $merger
                                , Laiz_Action_Component_Runner $componentRunner)
    {
        $this->request = $req;
        $this->container = $container;
        $this->merger = $merger;
        $this->componentRunner = $componentRunner;
    }

    public function run($actionName)
    {
        $actionConfigs = $this->container->getComponents('Laiz_Action_Config');

        $config = array();
        foreach ($actionConfigs as $actionConfig){
            if (!$actionConfig->match($actionName))
                continue;

            $coAction = $actionConfig->getComponentActionName($actionName);
            $baseDir = $actionConfig->getActionBase();
            $config = $this->parse($baseDir . '_' . $coAction, $actionConfig->getExecutionMethod());

            $viewName = $coAction;
            $templateDir = $actionConfig->getTemplateDir();
        }

        $executables = $this->container->getComponents('Laiz_Action_Executable');
        $countForWildCard = 0;
        $ret = null;
        foreach ($executables as $e){
            $countForWildCard++;
            $obj = $e->getClass();

            // 前のアクションでLaiz_Requestに入れた値もインジェクションする
            $this->request->setPropertiesByRequest($obj);

            $ret = $this->container->execMethod($obj, $e->getMethod());
            if ($ret)
                break;
        }

        // 最後にmatchして取得した アクションの $config を利用する
        $last = count($executables) === $countForWildCard;
        if (!$ret){
            if ($last && isset($config['result']['*']))
                return $this->clean()->run($config['result']['*']);

            if ($last && isset($config['view']['*']))
                return array('templateDir' => $templateDir,
                             'view' => $config['view']['*']);

            return array('templateDir' => $templateDir,
                         'view' => $viewName);
        }

        if (preg_match('/^action:/', $ret)){
            return $this->clean()->run(str_replace('action:', '', $ret));
        }

        if (isset($config['result'][$ret]))
            return $this->clean()->run($config['result'][$ret]);

        if (isset($config['view'][$ret]))
            return array('templateDir' => $templateDir,
                         'view' => $config['view'][$ret]);

        trigger_error("Not found [result] or [view] settings by return value [$ret]");
    }

    private function clean()
    {
        $this->container->deleteInterface('Laiz_Action_Executable');
        $this->container->deleteInterface('Laiz_Action_Response');
        $persistences = $this->container->getComponents('Laiz_Action_Persistence');
        foreach ($persistences as $p)
            $this->container->registerInterface($p, $this->container->getPriority($p));
        Laiz_Action_Config_Simple::$continueBase = true;
        return $this;
    }

    /**
     * If created action object then return className
     *
     * @param string $actionName
     * @param string $method
     *
     * @return string
     */
    public function parse($actionName, $method)
    {
        $req = $this->request;

        $className = $actionName;
        $configs = $this->merger->merge($className);

        // auto creating component and running by config.
        $configs = $this->componentRunner->run($configs);

        // set variables in request
        $req->setRequestsByPathInfo($configs);
        if (isset($configs['property']) || isset($configs['pathinfo'])){
            $a = new Laiz_Action_Response_Request($this->request, $configs);
            $this->container->registerInterface($a, 1); // 最優先
        }

        /*
         * file_exists with include_path
         */
        if (isset($configs['class']['class'])){
            $method = $className;
            $className = $configs['class']['class'];
        }
        $fileExists = false;
        $classPath = str_replace('_', '/', $className);
        foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path){
            if (file_exists($path . "/$classPath.php")){
                $fileExists = true;
                break;
            }
        }
        if (!$fileExists)
            return $configs;

        // creation action object and setting
        $obj = $this->container->create($className);
        $a = new Laiz_Action_Executable_Simple($obj, $method);
        $priority = $this->container->getPriority(get_class($this));
        $this->container->registerInterface($a, $priority);

        return $configs;
    }
}
