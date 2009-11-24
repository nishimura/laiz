<?php
/**
 * Laiz - Web application framework
 *
 * PHP versions 5
 *
 * @package    Laiz
 * @author     Satoshi Nishimura <nishim314@gmail.com>
 * @copyright  2005-2009 Satoshi Nishimura
 */

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
                $projectDir . 'components' . PATH_SEPARATOR . 
                $laizDir . 'components' . PATH_SEPARATOR .
                ini_get('include_path'));

        /** autoload */
        require_once 'Laiz/Autoload.php';
        Laiz_Autoload::setAll();

        /** get base setting */
        Laiz_Configure::setProjectDir($projectDir);
        $configs = Laiz_Configure::get();

        /** php.ini */
        $phpIniConfigs = Laiz_Configure::get('ini');
        foreach ($phpIniConfigs as $key => $value)
            ini_set($key, $value);

        /** setting timezone by configure */
        date_default_timezone_set($configs['TIMEZONE']);
        

        /** error class used by laiz */
        if ($configs['USING_LAIZ_ERROR_UTILS']){
            require_once 'Laiz/Error/Creator.php';
        }

        /* start controller of laiz framework */
        $controller = new Laiz_Controller();
        $controller->execute();
    }
}
