<?php
/**
 * Simple Database View Interface File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \PDOStatement;

/**
 * Simple Database View Interface
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface View
{
    public function prepareStmt($sqlFile, $params = null, $replace = null);
    public function createVo($sqlFile);
    public function bind(PDOStatement $stmt, Vo $vo);
    public function getVo($sqlFile, $params = array(), $replace = null);
    public function getVos($sqlFile, $params = array(), $replace = null);
}

