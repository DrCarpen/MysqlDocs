<?php
/**
 * @author liyang <liyang@uniondrug.cn>
 * @date   2019-05-06
 */
namespace Uniondrug\Builder\Components\Build;

use Uniondrug\Builder\Tools\Console;
use Uniondrug\Builder\Tools\TemplateParser;

/**
 * Class Build
 * @package Uniondrug\Builder\Components\Build
 */
class Build
{
    /**
     * @var string
     */
    public $api;
    /**
     * @var string
     */
    public $table;
    /**
     * @var Console
     */
    public $console;
    /**
     * @var TemplateParser
     */
    public $templateParser;
    /**
     * class的类型
     * @var string
     */
    public $classType;
    // int类型包含的子类型
    protected $int = [
        'int',
        'integer',
        'tinyint',
        'smallint',
        'mediumint',
        'bigint'
    ];
    // string字符串类型包含的子类型
    protected $string = [
        'char',
        'varchar',
        'text',
        'tinytext',
        'mediumtext',
        'longtext',
        'json'
    ];
    // float类型包含的子类型
    protected $float = [
        'double',
        'float',
        'decimal'
    ];
    // 日期类型包含的子类型
    protected $time = [
        'date',
        'datetime',
        'year',
        'time'
    ];
    // 时间戳类型
    protected $timestamp = [
        'timestamp'
    ];
    /**
     * 接口名称映射表
     * @var array
     */
    protected $apiNameMapping = [
        'create' => '新增',
        'delete' => '删除',
        'update' => '修改',
        'detail' => '详情',
        'listing' => '无分页列表',
        'paging' => '分页列表'
    ];

    public function __construct($parameter)
    {
        $this->_console();
        $this->_parameter($parameter);
        $this->_setAuthorInfo();
        $this->_templateParser();
    }

    /**
     * @param $parameter
     */
    private function _parameter($parameter)
    {
        $this->api = key_exists('api', $parameter) ? $parameter['api'] : '';
        $this->table = key_exists('table', $parameter) ? $parameter['table'] : '';
    }

    private function _templateParser()
    {
        $this->templateParser = new TemplateParser();
    }

    private function _console()
    {
        $this->console = new Console();
    }

    /**
     * 获取用户名称信息
     */
    private function _setAuthorInfo()
    {
        $nameShell = 'git config --get user.name ';
        $emailShell = 'git config --get user.email';
        $name = shell_exec($nameShell);
        $email = shell_exec($emailShell);
        if ($name) {
            $this->authorName = str_replace(PHP_EOL, '', $name);
        } else {
            $this->authorName = 'developer';
        }
        if ($email) {
            $this->authorEmail = str_replace(PHP_EOL, '', $email);
        } else {
            $this->authorEmail = 'developer@uniondrug.cn';
        }
    }

    /**
     * @return string
     */
    protected function getAuthorContent()
    {
        $author = '/**'.PHP_EOL;
        $author .= ' * Created by Builder'.PHP_EOL;
        $author .= ' * @Author '.$this->authorName.' <'.$this->authorEmail.'>'.PHP_EOL;
        $author .= ' * @Date   '.date('Y-m-d').PHP_EOL;
        $author .= ' * @Time   '.date('H:i:s').PHP_EOL;
        $author .= ' */';
        return $author;
    }

    /**
     * @return string
     */
    protected function _tableName()
    {
        $nameArr = explode('_', strtolower($this->table));
        $tableName = '';
        foreach ($nameArr as $value) {
            $tableName .= ucfirst($value);
        }
        return $tableName;
    }

    /**
     * 获取类名
     * @param        $classType 类型：Controller...
     * @return string
     */
    protected function getClassName($classType)
    {
        $className = $this->_tableName();
        $apiName = $this->api ? ucfirst($this->api) : '';
        switch ($classType) {
            case 'Controller':
                $className = $className.'Controller';
                break;
            case 'Service':
                $className = $className.'Service';
                break;
            case 'Model':
                $className = $className.'Model';
                break;
            case 'Trait':
                $className = $className.'Trait';
                break;
            case 'Logic':
                $className = $apiName.'Logic';
                break;
            case 'Request':
                $className = $apiName.'Request';
                break;
            case 'Result':
                $className = $apiName.'Result';
                break;
            default:
                $className = '';
                break;
        }
        return $className;
    }

    /**
     * 查询此类型
     * @param $type
     * @return string
     */
    protected function getType($type)
    {
        switch ($type) {
            case in_array($type, $this->int):
            case in_array($type, $this->timestamp):
                return 'int';
                break;
            case in_array($type, $this->string):
            case in_array($type, $this->time):
                return 'string';
                break;
            case in_array($type, $this->float):
                return 'float';
                break;
            default:
                return 'string';
                break;
        }
    }

    /**
     * 属性列表
     * @param $columns
     * @return string
     */
    protected function getPropertyContent($columns)
    {
        $propertyTemplate = ' * @property {{DATA_TYPE}}  ${{COLUMN_NAME}}    {{COLUMN_COMMENT}}';
        $propertyTemplateContent = [];
        foreach ($columns as $key => $value) {
            $repalceList = [
                'DATA_TYPE' => $this->getType($value['dateType']),
                'COLUMN_NAME' => $value['columnName'],
                'COLUMN_COMMENT' => $value['columnComment']
            ];
            $propertyTemplateContent[] = $this->templateParser->assign($repalceList, $propertyTemplate);
        }
        return implode(PHP_EOL, $propertyTemplateContent);
    }

    /**
     * 获取文件名
     * @param $classType
     * @return string
     */
    protected function getFileName($classType)
    {
        $tableName = $this->_tableName();
        $api = $this->api ? ucfirst($this->api) : '';
        switch ($classType) {
            case 'Model':
                return $tableName.'Model.php';
                break;
            case 'Trait':
                return $tableName.'Trait.php';
                break;
            case 'Controller':
                return $tableName.'Controller.php';
                break;
            case 'Service':
                return $tableName.'Service.php';
                break;
            case 'Logic':
                return $api.'Logic.php';
                break;
            case 'Request':
                return $api.'Request.php';
                break;
            case 'Result':
                return $api.'Result.php';
                break;
            default:
                return '';
        }
    }

    protected function getValidator($type, $column)
    {
        if ($type == 'string' && $column['CHARACTER_MAXIMUM_LENGTH']) {
            $validator = 'options={minChar:1,maxChar:'.$column['CHARACTER_MAXIMUM_LENGTH'].'}';
        } else {
            $validator = '';
        }
        return $validator;
    }

    /**
     * 获取文件对应的目录
     * @param $classType
     * @return string
     */
    protected function getDocumentDirectPrefix($classType)
    {
        $tableName = $this->_tableName();
        $base = './app/';
        switch ($classType) {
            case 'Controller':
                $prifix = $base.'Controllers/';
                break;
            case 'Service':
                $prifix = $base.'Services/';
                break;
            case 'Model':
                $prifix = $base.'Models/';
                break;
            case 'Trait':
                $prifix = $base.'Structs/Traits/';
                break;
            case 'Logic':
                $prifix = $base.'Logics/'.$tableName.'/';
                break;
            case 'Request':
                $prifix = $base.'Structs/Requests/'.$tableName.'/';
                break;
            case 'Result':
                $prifix = $base.'Structs/Results/'.$tableName.'/';
                break;
        }
        return $prifix;
    }

    /**
     * 获取文件对应的基础模板
     * @param $classType
     * @return bool|string
     */
    protected function getTemplate($classType)
    {
        $templateDirect = './vendor/drcarpen/builder/src/Components/Template/Basic/';
        switch ($classType) {
            case 'Controller':
                $templateDirect = $templateDirect.'BasicController.template';
                break;
            case 'Service':
                $templateDirect = $templateDirect.'BasicService.template';
                break;
            case 'Model':
                $templateDirect = $templateDirect.'BasicModel.template';
                break;
            case 'Trait':
                $templateDirect = $templateDirect.'BasicTrait.template';
                break;
            case 'Logic':
                $templateDirect = $templateDirect.'BasicLogic.template';
                break;
            case 'Request':
                $templateDirect = $templateDirect.'BasicRequest.template';
                break;
            case 'Result':
                $templateDirect = $templateDirect.'BasicResult.template';
                break;
        }
        return file_get_contents($templateDirect);
    }

    /**
     * 获取文件对应的基础模板
     * @param $classType
     * @return bool|string
     */
    protected function getBasicTemplate($classType)
    {
        $templateDirect = './vendor/drcarpen/builder/src/Components/Template/Basic/';
        switch ($classType) {
            case 'Controller':
                $templateDirect = $templateDirect.'BasicController.template';
                break;
            case 'Service':
                $templateDirect = $templateDirect.'BasicService.template';
                break;
            case 'Model':
                $templateDirect = $templateDirect.'BasicModel.template';
                break;
            case 'Trait':
                $templateDirect = $templateDirect.'BasicTrait.template';
                break;
            case 'Logic':
                $templateDirect = $templateDirect.'BasicLogic.template';
                break;
            case 'Request':
                $templateDirect = $templateDirect.'BasicRequest.template';
                break;
            case 'Result':
                $templateDirect = $templateDirect.'BasicResult.template';
                break;
        }
        return file_get_contents($templateDirect);
    }

    /**
     * 获取分部模板
     * @param $templateName
     * @return bool|string
     */
    protected function getPartTemplate($classType)
    {
        $templateDirect = './vendor/drcarpen/builder/src/Components/Template/Part/';
        switch ($classType) {
            case 'Controller':
                $templateDirect = $templateDirect.'ControllerBody.template';
                break;
            case 'Service':
                $templateDirect = $templateDirect.'BasicService.template';
                break;
            case 'Model':
                $templateDirect = $templateDirect.'BasicModel.template';
                break;
            case 'Trait':
                $templateDirect = $templateDirect.'BasicTrait.template';
                break;
            case 'Logic':
                $templateDirect = $templateDirect.'BasicLogic.template';
                break;
            case 'Request':
                $templateDirect = $templateDirect.'BasicRequest.template';
                break;
            case 'Result':
                $templateDirect = $templateDirect.'BasicResult.template';
                break;
        }
        return file_get_contents($templateDirect);
    }

    /**
     * 读取文件
     * @param $direct
     * @return bool|string
     */
    protected function getInitFile($direct)
    {
        return file_get_contents($direct);
    }

    /**
     * @param $html
     * @param $documentDirectPrifix
     * @param $fileDirect
     */
    protected function buildFile($html, $documentDirectPrifix, $fileDirect)
    {
        if (!is_dir($documentDirectPrifix)) {
            mkdir($documentDirectPrifix, 0777, true);
        }
        if (!file_exists($fileDirect)) {
            file_put_contents($fileDirect, $html);
            $this->console->info($file.' is built');
        } else {
            $this->console->warning($file.' file is exist');
        }
    }

    /**
     * 覆盖文件
     * @param $fileDirect
     * @param $file
     */
    protected function rewriteFile($fileDirect, $file)
    {
        $this->console->warning('正在覆盖原文件Controller!');
        file_put_contents($fileDirect, $file);
    }

    /**
     * 检查文件是否存在
     * @param $direct
     * @return bool
     */
    protected function checkFileExsit($direct)
    {
        if (file_exists($direct)) {
            return true;
        }
        return false;
    }
}