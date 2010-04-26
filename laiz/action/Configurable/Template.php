<?php
/**
 * File for app's action config.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Template method pattern for app's action classes.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Configurable_Template implements Configurable
{
    protected function getPrefix()
    {
        $className = get_class($this);
        if (preg_match('/^(.+[\\\\_])/', $className, $matches)){
            $ret = $matches[1];
        }else{
            $ret = '';
        }

        return $ret;
    }
    protected function getDefaultTopPage()
    {
        // Default name is 'Top' in Configuable_Default.
        // To make the error message comprehensible
        // when there is no tempkate, this method returns
        // another name.
        // The error message helps the validity
        // confirmation of the match method.
        return 'TopPage';
    }
    protected function getActionNameSuffix($actionName)
    {
        $ret = preg_replace('/^[^_]+_/', '', $actionName);
        if ($ret === $actionName)
            $ret = $this->getDefaultTopPage();
        else if (strlen($ret) === 0)
            $ret = $this->getDefaultTopPage();
        return $ret;
    }
    protected function getActionPrefix()
    {
        return 'Action_';
    }
    public function getTemplateDir()
    {
        $prefix = $this->getPrefix();
        $prefix = str_replace('_', '/', $prefix);
        $prefix = str_replace('\\', '/', $prefix);
        return rtrim($prefix, '/') . '/templates';
    }
    public function convertActionClassName($actionName)
    {
        $actionName = $this->getActionNameSuffix($actionName);
        $prefix = $this->getPrefix() . $this->getActionPrefix();
        return $prefix . $actionName;
    }
    public function convertTemplateName($actionName)
    {
        return $this->getActionNameSuffix($actionName);
    }
    /**
     * return default execution method of action.
     * @return string
     */
    public function getExecutionMethod()
    {
        return 'act';
    }
    public function match($actionName)
    {
        $pattern = $this->getPrefix();
        $pattern = rtrim($pattern, '\\_');
        if ($actionName === $pattern)
            return true;
        
        if (preg_match('/^' . preg_quote($pattern, '/') . '_/', $actionName))
            return true;

        return false;
    }

}
