<?php
/**
 * Laiz - Web application framework
 *
 * PHP versions 5.3
 *
 * @package    Laiz
 * @author     Satoshi Nishimura <nishim314@gmail.com>
 * @copyright  2005-2010 Satoshi Nishimura
 */

use \laiz\autoloader\BasicLoader;
use \laiz\core\Configure;
use \laiz\core\Controller;
use \laiz\error\Creator;

/**
 * Laiz Framework Class
 *
 * @package    Laiz
 * @author     Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz
{
    static public function laze($projectDir = '../'){
        /** setting base include path */
        $laizDir = dirname(__FILE__) . '/';
        ini_set('include_path',
                $projectDir . 'app' . PATH_SEPARATOR .
                $laizDir . PATH_SEPARATOR .
                ini_get('include_path'));

        /** autoload */
        require_once 'laiz/autoloader/BasicLoader.php';
        BasicLoader::init();

        /** get base setting */
        Configure::setProjectDir($projectDir);
        $configs = Configure::get();

        /** php.ini */
        $phpIniConfigs = Configure::get('ini');
        foreach ($phpIniConfigs as $key => $value)
            ini_set($key, $value);

        /** setting timezone by configure */
        date_default_timezone_set($configs['TIMEZONE']);
        

        /** error class used by laiz */
        /** not include by command line */
        if (!isset($_SERVER['argv']) && $configs['USING_LAIZ_ERROR_UTILS']){
            require_once 'laiz/error/Creator.php';
            Creator::register();
        }

        /* start controller of laiz framework */
        $controller = new Controller();
        $controller->execute();
    }
}
