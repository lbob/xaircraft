<?php
namespace Xaircraft\Web\Http;
use Xaircraft\Core\IO\Directory;
/**
 * Class RequestFileInfo
 *
 * @package Xaircraft\Http
 * @author lbob created at 2015/4/1 19:49
 */
class RequestFileInfo {
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $tmp_name;
    /**
     * @var integer
     */
    public $error;
    /**
     * @var integer
     */
    public $size;
    /**
     * 移动文件到指定路径
     * @param $destinationPath
     * @throws \Exception
     * @return bool|mixed
     */
    public function moveUploadedFile($destinationPath)
    {
        if (!isset($destinationPath)) {
            throw new \Exception("缺少目标文件路径");
        }
        if ($this->error == UPLOAD_ERR_OK) {
            $destinationFolder = dirname($destinationPath);
            if (!Directory::makeDir($destinationFolder)) {
                throw new \Exception("创建文件夹失败", 600);
            }
            return move_uploaded_file($this->tmp_name, $destinationPath);
        }
        throw new \Exception("文件[$this->name]上传错误，错误代码：" . $this->error);
    }
}