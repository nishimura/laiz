<?php
/**
 * File for app's action config.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Template method pattern for app's action classes.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Laiz_Action_Config_Simple implements Laiz_Action_Config
{
    protected $classes = array();
    protected $PATH_INFO_ACTION = 1;
    protected $DEFAULT_ACTION = 'Top';
    protected $ACTION_METHOD = 'act';
    static public $continueBase = true;

    protected function getMatchString()
    {
        $className = get_class($this);
        $a = explode('_', $className, 2);
        if (isset($a[0]))
            return $a[0];
        else
            return '';
    }

    public function getPathInfoAction()
    {
        return $this->PATH_INFO_ACTION;
    }

    public function getDefaultAction()
    {
        return $this->DEFAULT_ACTION;
    }

    /**
     * If action name match then return true. Default is class's prefix.
     * Separator: '/' or '_'
     * 
     * @param string $actionName 
     * @return bool
     */
    public function match($actionName)
    {
        if (!self::$continueBase)
            return false;

        // $className = get_class($this);
        // $a = explode('_', $className, 2);
        $pattern = $this->getMatchString();
        if ($pattern){
            if ($actionName === $pattern)
                $ret = true;
            else if (preg_match('/^' . preg_quote($pattern, '/') . '_/',
                                $actionName))
                $ret = true;
            else
                $ret = false;
        }else{
            $ret = false;
        }

        // If run actions other than Base then Laiz Base action is not active.
        if ($ret)
            self::$continueBase = false;

        return $ret;
    }

    /**
     * return base directory of components. default is class's prefix.
     * @return string
     */
    public function getBaseDir()
    {
        $className = get_class($this);
        $a = explode('_', $className, 2);
        if (isset($a[0]))
            return $a[0];
        else
            return '';
    }

    public function getActionBase()
    {
        return get_class($this);
    }

    /**
     * return template directory of components.
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->getBaseDir() . '/templates';
    }

    public function getComponentActionName($actionName)
    {
        $ret = preg_replace('/^'.preg_quote($this->getMatchString(), '/').'/',
                            '', $actionName);
        $ret = ltrim($ret, '_');
        if (strlen(trim($ret)) === 0)
            $ret = $this->getDefaultAction();

        return $ret;
    }

    /**
     * return default execution method of action.
     * @return string
     */
    public function getExecutionMethod()
    {
        return $this->ACTION_METHOD;
    }
}
