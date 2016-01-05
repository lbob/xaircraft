<?php

namespace Xaircraft\Web\Mvc;

use Xaircraft\App;
use Xaircraft\DI;
use Xaircraft\Exception\WebException;
use Xaircraft\Globals;
use Xaircraft\Web\Http\Request;

/**
 * Class Widgets
 *
 * @package Xaircraft\Web\Mvc
 * @author lbob created at 2014/12/9 11:21
 */
class Widgets {
    const ENV_WIDGETS_FILE_EXT = 'phtml';
    const LAYOUT_BASE_PATH = '/views/widgets/';

    public $data;
    /**
     * @var \Xaircraft\Web\Http\Request
     */
    public $req;

    private $widgets;

    public function __construct($widgets)
    {
        $this->widgets = $widgets;
        $this->req = DI::get(Request::class);
    }

    /**
     * @param $widgetsName
     * @return Widgets
     */
    public static function make($widgetsName)
    {
        $request = DI::get(Request::class);

        if (!$widgetsName) {
            throw new WebException(
                $request->param('controller'), $request->param('action'), "Invalid widgets name");
        } else {
            $filePath = self::getFilePath($widgetsName);
            if (is_file($filePath) && is_readable($filePath)) {
                return new Widgets($filePath);
            } else {
                throw new WebException(
                    $request->param('controller'),
                    $request->param('action'),
                    "Can't find widgets file $filePath");
            }
        }
    }

    private static function getFilePath($layoutName)
    {
        $filePath  = str_replace('.', '/', $layoutName);
        $extension = App::environment(Globals::ENV_MVC_VIEW_FILE_EXTENSION);
        if (!isset($extension) || $extension === '') {
            $extension = self::ENV_WIDGETS_FILE_EXT;
        }
        return App::path('app')
        . self::LAYOUT_BASE_PATH . $filePath . '.'
        . $extension;
    }

    public function render()
    {
        if (isset($this->widgets)) {
            extract($this->data);
            require $this->widgets;
        }
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

    public function html()
    {
        //return new Html($this);
    }
}

 