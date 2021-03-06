<?php
/**
 * @author liyang <liyang@uniondrug.cn>
 * @date   2019-05-06
 */
namespace Uniondrug\Builder\Components\Build;

/**
 * Class BuildModel
 * @package Uniondrug\Builder\Components\Build
 */
class BuildModel extends Base
{
    /**
     * @var array
     */
    public $parameter;

    /**
     * BuildModel constructor.
     * @param $parameter
     */
    public function __construct($parameter)
    {
        parent::__construct($parameter);
        $this->classType = 'Model';
        $this->parameter = $parameter;
    }

    /**
     * @param array $columns
     */
    public function build(array $columns)
    {
        // 获取文件名称
        $direct = $this->getDocumentDirectPrefix().$this->getFileName();
        $oldDirect = $this->getDocumentDirectPrefix().$this->getOldFileName();
        // 判断目录是否存在
        if (!$this->checkFileExsit($direct) && !$this->checkFileExsit($oldDirect)) {
            $init = [
                'PROPERTY_TEMPLATE_LIST' => $this->getPropertyContent($columns),
                'COLUMN_MAP' => $this->getColumnMap($columns),
                'COLUMN_COMMENT' => $this->getColumnComment($columns),
                'COLUMN_INIT'    => $this->getInitFun($this->pre.$this->table)
            ];
            // 注解列表
            $this->initBuild($direct, $init);
        }
    }

    /**
     * @param $columns
     * @return null|string|string[]
     */
    private function getColumnMap($columns)
    {
        $isUnderlineStyle = false;
        foreach ($columns as $columnValue) {
            if (preg_match('/\_/', $columnValue['columnName'])) {
                $isUnderlineStyle = true;
            }
        }
        if (!$isUnderlineStyle) {
            return '';
        }
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[] = '            \''.$column['columnName'].'\' => \''.$column['camelColumnName'].'\'';
        }
        $columnMapContent = implode(','.PHP_EOL, $columnMap);
        return $this->templateParser->assign(['COLUMN_MAP' => $columnMapContent], $this->getPartTemplate('ModeColumnMap'));
    }

    /**
     * 获取字段注释
     * @param $columns
     * @return string
     */
    private function getColumnComment($columns)
    {
        $str = '';
        foreach ($columns as $column) {
            if ($column['sitAnnotation']['sit']) {
                foreach ($column['sitAnnotation']['sit'] as $sit) {
                    $str .= $this->templateParser->assign([
                        'UPPER_UNDERLINE_CASE' => strtoupper($column['underlineColumnName']),
                        'SIT_STATUS' => $sit['sitStatus'],
                        'SIT_COMMNET' => ($column['sitAnnotation']['main'] ? $column['sitAnnotation']['main'] : $column['camelColumnName']).': '.$sit['sitComment']
                    ], $this->getPartTemplate('ModeConstant'));
                }
            }
        }
        $str .= '    private static $_unknownMessage = \'非法状态\';'.PHP_EOL;
        foreach ($columns as $column) {
            if ($column['sitAnnotation']['sit']) {
                $arrayContent = '';
                foreach ($column['sitAnnotation']['sit'] as $sit) {
                    $arrayContent .= $this->templateParser->assign([
                        'UPPER_UNDERLINE_CASE' => strtoupper($column['underlineColumnName']),
                        'SIT_CONTENT' => $sit['sitComment'],
                        'SIT_STATUS' => $sit['sitStatus'],
                    ], $this->getPartTemplate('ModeText'));
                }
                $str .= $this->templateParser->assign([
                    'LOWER_CAMEL_CASE' => $column['camelColumnName'],
                    'ARRAY_CONTENT' => $arrayContent,
                    'COLUMN_COMMENT' => $column['sitAnnotation']['main'] ? $column['sitAnnotation']['main'] : $column['camelColumnName']
                ], $this->getPartTemplate('ModeTextArray'));
            }
        }
        foreach ($columns as $column) {
            if ($column['sitAnnotation']['sit']) {
                $str .= $this->templateParser->assign([
                    'COLUMN_COMMENT' => $column['sitAnnotation']['main'] ? $column['sitAnnotation']['main'] : $column['camelColumnName'],
                    'UPPER_CAMEL_CASE' => ucfirst($column['camelColumnName']),
                    'LOWER_CAMEL_CASE' => $column['camelColumnName']
                ], $this->getPartTemplate('ModeTextFunc'));
            }
        }
        return $str;
    }


    private function getInitFun($table){
       return  $this->templateParser->assign(['TABLE_MAME' => $table ], $this->getPartTemplate('ModelInitialize'));
    }
}