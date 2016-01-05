<?php

namespace Xaircraft\Web\Mvc;

use Xaircraft\App;
use Xaircraft\DI;
use Xaircraft\Exception\WebException;
use Xaircraft\Globals;
use Xaircraft\Web\Mvc\Action\ViewResult;
use Xaircraft\Web\Http\Request;

/**
 * Class Layout
 *
 * @package Xaircraft\Mvc
 * @author lbob created at 2014/12/9 9:13
 */
class Layout {

    const ENV_LAYOUT_FILE_EXT = 'phtml';
    const LAYOUT_BASE_PATH = '/views/layout/';

    public $data;
    /**
     * @var \Xaircraft\Web\Http\Request
     */
    public $req;
    /**
     * @var ViewResult
     */
    private $viewResult;

    private $layout;

    public function __construct($layout, $viewResult)
    {
        $this->layout = $layout;
        $this->viewResult = $viewResult;
        $this->req = DI::get(Request::class);
    }

    public function make($layoutName, $viewResult)
    {
        if (!$layoutName) {
            throw new WebException(
                $this->req->param('controller'),
                $this->req->param('action'), "Invalid layout name");
        } else {
            $layoutFilePath = self::getFilePath($layoutName);
            if (is_file($layoutFilePath) && is_readable($layoutFilePath)) {
                return new Layout($layoutFilePath, $viewResult);
            } else {
                throw new WebException(
                    $this->req->param('controller'),
                    $this->req->param('action'),
                    "Can't find layout file $layoutFilePath");
            }
        }
    }

    public function renderBody()
    {
        if (isset($this->viewResult)) {
            $this->viewResult->execute();
        }
    }

    public function renderPage($viewName)
    {
        $viewResult       = new ViewResult($viewName);
        $viewResult->data = $this->data;
        $viewResult->execute();
    }

    public function renderWidgets($widgetsName)
    {
        /**
         * @var $widgets Widgets
         */
        $widgets = Widgets::make($widgetsName);
        $widgets->data = $this->data;
        $widgets->render();
    }

    private static function getFilePath($layoutName)
    {
        $filePath  = str_replace('.', '/', $layoutName);
        $extension = App::environment(Globals::ENV_MVC_VIEW_FILE_EXTENSION);
        if (!isset($extension) || $extension === '') {
            $extension = self::ENV_LAYOUT_FILE_EXT;
        }
        return App::path('app')
        . self::LAYOUT_BASE_PATH . $filePath . '.'
        . $extension;
    }

    public function render()
    {
        if (isset($this->layout)) {
            $this->data['title'] = isset($this->data['title']) ? $this->data['title'] : "Undefined title";
            extract($this->data);
            require $this->layout;
        }
    }

    public function html()
    {
        //return new Html($this);
    }
}

 