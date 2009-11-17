<?php

interface Laiz_Action_Config extends Laiz_Component
{

    public function getPathInfoAction();
    public function getDefaultAction();
    public function getBaseDir();
    public function getActionBase();
    public function getTemplateDir();
    public function getComponentActionName($actionName);
    public function getExecutionMethod();

    /**
     * if match actionName then execute run()
     *
     * @param string $actionName
     * @return bool
     */
    public function match($actionName);
}
