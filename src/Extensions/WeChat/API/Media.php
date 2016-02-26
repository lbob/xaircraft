<?php
/**
 * Created by PhpStorm.
 * User: Polaris
 * Date: 2015/8/24
 * Time: 12:53
 */

namespace Xaircraft\Extensions\WeChat\API;


use Xaircraft\App;
use Xaircraft\Core\IO\Directory;
use Xaircraft\Core\IO\File;
use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Extensions\WeChat\API;
use Xaircraft\Extensions\WeChat\Application;

class Media {

    /**
     * @var Application
     */
    private $app;
    const MEDIA_IMAGE = 'image';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 素材列表
     * @param string $type
     * @param int $offset
     * @param int $count
     * @return mixed|string
     */
    public function mediaList($type = self::MEDIA_IMAGE , $offset = 0, $count = 10)
    {
        $body = array(
            "type" => $type,
            "agentid" => $this->app->getAppID(),
            "offset" => $offset,
            "count" => $count
        );
        return $this->app->post(API::MEDIA_LIST, $this->app->formatBody($body));
    }

    /**
     * 获取企业号临时素材文件
     * @param array $media_ids
     * @param string $destinationPath
     * @return array
     * @throws \Exception
     */
    public function getMediaTemporary(array $media_ids, $destinationPath = '/wechat/media/temporary/')
    {
        ExceptionHelper::ThrowIfNullOrEmpty($media_ids, '缺少media_ids');
        ExceptionHelper::ThrowIfSpaceOrEmpty(App::path('upload'), '未定义upload文件夹路径');
        $media_ids = array_unique($media_ids);

        $uris = array();
        foreach ($media_ids as $media_id) {
            $result = $this->app->request(API::MEDIA_TEMPORARY, array('media_id' => $media_id), true);
            list($headers, $stream) = explode("\r\n\r\n", $result, 2);
            $fileName = $this->getHeaderFileName($headers);

            $uris[] = $this->save($stream, $destinationPath, $fileName);
        }

        return $uris;
    }

    /**
     * 获取企业号永久素材文件
     * @param array $media_ids
     * @param string $destinationPath
     * @return array
     * @throws \Exception
     */
    public function getMediaForever(array $media_ids, $destinationPath = '/wechat/media/temporary/')
    {
        ExceptionHelper::ThrowIfNullOrEmpty($media_ids, '缺少media_ids');
        $media_ids = array_unique($media_ids);

        $uris = array();
        foreach ($media_ids as $media_id) {
            $result = $this->app->request(API::MEDIA_FOREVER, array('media_id' => $media_id, 'agentid' => $this->app->getAppID()), true);
            list($headers, $stream) = explode("\r\n\r\n", $result, 2);
            $fileName = $this->getHeaderFileName($headers);
            $uris[] = $this->save($stream, $destinationPath, $fileName);
        }

        return $uris;
    }

    /**
     * 保存文件，返回upload目录下的相对路径
     * @param $stream
     * @param $destinationPath
     * @param $fileName
     * @return string
     * @throws \Exception
     */
    private function save($stream, $destinationPath, $fileName)
    {
        ExceptionHelper::ThrowIfSpaceOrEmpty($stream, '素材文件获取失败');
        ExceptionHelper::ThrowIfSpaceOrEmpty(App::path('upload'), '未定义upload文件夹路径');

        Directory::makeDir(App::path('upload') . $destinationPath);
        ExceptionHelper::ThrowIfNotTrue(file_put_contents(App::path('upload').$destinationPath. $fileName, $stream, true), '文件保存失败');

        return $destinationPath. $fileName;
    }

    /**
     * 解析头部文件名信息
     * @param $headers
     * @return string
     */
    private function getHeaderFileName($headers)
    {
        $fileName = md5(time().rand(1, 1000)).'tmp';
        foreach(explode("\n", $headers) as $header_value) {
            if(reset(explode(':', $header_value)) == 'Content-disposition'){
                $fileNames = explode('"', end($header_pieces));
                $fileName = $fileNames[1];
                break;
            }
        }

        return $fileName;
    }
} 