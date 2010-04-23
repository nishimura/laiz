<?php

namespace laiz\action;

use \laiz\builder\Aggregatable;

interface Configurable extends Aggregatable
{
    public function getTemplateDir();
    public function convertActionClassName($actionName);
    public function convertTemplateName($actionName);
    public function getExecutionMethod();

    /**
     * if match actionName then execute run()
     *
     * @param string $actionName
     * @return bool
     */
    public function match($actionName);
}
