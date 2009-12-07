<?php
/**
 * Simple Database View Interface File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Simple Database View Interface
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Laiz_Db_View
{
    public function prepareStmt($sqlFile, $params = null, $replace = null);
    public function createVo($sqlFile);
    public function bind(PDOStatement $stmt, Laiz_Db_Vo $vo);
    public function getVo($sqlFile, $params = array(), $replace = null);
    public function getVos($sqlFile, $params = array(), $replace = null);
}

