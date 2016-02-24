<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 2016/2/25
 * Time: 5:16
 */

namespace Xaircraft\Web\Mvc\Argument;


use Xaircraft\Web\Http\RequestFileInfo;

class PostFile
{
    /**
     * @var RequestFileInfo[]
     */
    private $files;

    /**
     * PostFile constructor.
     * @param RequestFileInfo[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }
}