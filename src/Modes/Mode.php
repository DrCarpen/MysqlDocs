<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2020/7/9
 * Time: 12:06 AM
 */
namespace Uniondrug\Builder\Modes;

use Uniondrug\Builder\Tools\Console;
use Uniondrug\Builder\Tools\Model;

/**
 * Class Mode
 * @package Uniondrug\Builder\Modes
 */
class Mode
{
    /**
     * 数据表字段
     * @var array
     */
    public $columns;
    /**
     * @var Console
     */
    public $console;
    /**
     * @var string
     */
    public $table;
    /**
     * @var array
     */
    public $parameter;
    /**
     * @var array
     */
    protected $dbConfig;

    public function __construct(array $parameter, $dbConfig)
    {
        $this->console = new Console();
        // 初始化数据库配置
        $this->dbConfig = $dbConfig;
        // 初始化全局变量
        $this->_setParameter($parameter);
        // 获取数据库的字段
        if ($dbConfig) {
            $this->_getColumns();
        }
    }

    /**
     * 配置参数
     * @param $parameter
     */
    protected function _setParameter($parameter)
    {
        $this->parameter = $parameter;
        $this->table = key_exists('table', $parameter) ? $parameter['table'] : '';
    }

    /**
     * 获取表字段
     */
    private function _getColumns()
    {
        $model = new Model($this->dbConfig);
        $columns = $model->getColumns();
        foreach ($columns as $columnKey => $column) {
            $columns[$columnKey]['camelColumnName'] = $this->getLowerCamelCase($column['columnName']);
            $columns[$columnKey]['underlineColumnName'] = $this->getUnderlineCase($column['columnName']);
        }
        $this->columns = $columns;
    }

    /**
     * 获取小驼峰字段
     * @param      $str
     * @param bool $ucfirst
     * @return mixed|string
     */
    private function getLowerCamelCase($str, $ucfirst = false)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', lcfirst($str));
        return $ucfirst ? ucfirst($str) : $str;
    }

    /**
     * @param        $camelCaps
     * @param string $separator
     * @return string
     */
    private function getUnderlineCase($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1".$separator."$2", $camelCaps));
    }
}