<?php
/**
 * Script for Command Line.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

if (isset($_SERVER['BASE']))
    $base = $_SERVER['BASE'];
else if (isset($_SERVER['PROJECT_BASE_DIR']))
    $base = $_SERVER['PROJECT_BASE_DIR'];
else
    $base = getcwd();

if (!preg_match('@/$@', $base))
    $base .= '/';

error_reporting(E_ALL|E_STRICT);
require_once dirname(dirname(dirname(__FILE__))) . '/Laiz.php';
Laiz::laze($base);
