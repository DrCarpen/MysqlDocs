<?php
/**
 * @author liyang <liyang@uniondrug.cn>
 * @date   2019-05-06
 */
namespace Uniondrug\Builder\Components\Build;

class BuildResult extends Base
{
    public function __construct($parameter)
    {
        parent::__construct($parameter);
        $this->classType = 'Result';
    }

    /**
     * @param $columns
     * @return bool
     */
    public function build($columns)
    {
        // 获取文件名称
        $direct = $this->getDocumentDirectPrefix().$this->getFileName();
        // 判断基础文件是否存在
        if ($this->checkFileExsit($direct)) {
            return false;
        }
        $this->initBuild($direct, [
            'TABLE_NAME' => $this->_tableName(),
            'EXTEND_CLASS' => $this->getExtendClass(),
            'USE_TRAIT' => $this->getUseTrait($columns),
            'RESULT_PART' => $this->getResultPart($columns)
        ]);
        // 创建Row
        if (in_array($this->api, [
            'listing',
            'page'
        ])) {
            $rowDirect = $this->getDocumentDirectPrefix().$this->getFileName(1);
            $this->api = 'row';
            $this->initBuild($rowDirect, [
                'TABLE_NAME' => $this->_tableName(),
                'EXTEND_CLASS' => $this->getExtendClass(),
                'USE_TRAIT' => $this->getUseTrait($columns),
                'RESULT_PART' => $this->getResultPart($columns)
            ]);
        }
        return true;
    }

    /**
     * 获取拓展类文件名
     * @return string
     */
    protected function getExtendClass()
    {
        if ($this->api == 'page') {
            return 'PaginatorStruct';
        } else if ($this->api == 'listing') {
            return 'ListStruct';
        } else {
            return 'Struct';
        }
    }

    /**
     * 获取trait类的文件名
     * @param $columns
     * @return string
     */
    protected function getUseTrait($columns)
    {
        if (in_array($this->api, [
            'page',
            'listing'
        ])) {
            return '';
        } else {
            if (!$columns) {
                return '';
            }
            return 'use App\Structs\Traits\\'.$this->_tableName().'Trait;';
        }
    }

    /**
     * @param $columns
     * @return bool|string
     */
    protected function getResultPart($columns)
    {
        if (in_array($this->api, [
            'page',
            'listing'
        ])) {
            return $this->getPartTemplate();
        } else {
            if (!$columns) {
                return '';
            }
            return '    use '.$this->_tableName().'Trait;';
        }
    }
}