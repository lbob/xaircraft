<?php

namespace Xaircraft\Web\Mvc;
use Xaircraft\App;
use Xaircraft\DI;
use Xaircraft\Exception\WebException;
use Xaircraft\Globals;
use Xaircraft\Web\Http\Request;
use Xaircraft\Web\Http\Response;


/**
 * Class View
 *
 * @package Xaircraft\Mvc
 * @author lbob created at 2014/11/20 14:56
 */


class View
{
    const ENV_VIEW_FILE_EXT = 'phtml';
    const VIEW_BASE_PATH = '/views/';

    public $view;
    public $data;
    /**
     * @var \Xaircraft\Web\Http\Request
     */
    public $req;
    /**
     * @var \Xaircraft\Web\Http\Response
     */
    public $response;

    /**
     * @var array
     */
    private $pjaxContainers;

    public function __construct($view)
    {
        $this->view = $view;
        $this->req = DI::get(Request::class);
        $this->response = DI::get(Response::class);
    }

    public static function make($viewName = null)
    {
        $request = DI::get(Request::class);

        if (!$viewName) {
            throw new WebException(
                $request->param('controller'), $request->param('action'), "Invalid view name.");
        } else {
            $viewFilePath = self::getFilePath($viewName);
            if (is_file($viewFilePath) && is_readable($viewFilePath)) {
                return new View($viewFilePath);
            } else {
                throw new WebException(
                    $request->param('controller'),
                    $request->param('action'),
                    "Can't find view file [$viewFilePath]");
            }
        }
    }

    public function with($key, $value = null)
    {
        $this->data[$key] = $value;
        return $this;
    }

    private static function getFilePath($viewName)
    {
        $filePath  = str_replace('.', '/', $viewName);
        $extension = App::environment(Globals::ENV_MVC_VIEW_FILE_EXTENSION);
        if (!isset($extension) || $extension === '') {
            $extension = self::ENV_VIEW_FILE_EXT;
        }
        return App::path('app')
        . self::VIEW_BASE_PATH . $filePath . '.'
        . $extension;
    }

    public function __call($method, $parameters)
    {
        if (starts_with($method, 'with')) {
            return $this->with(snake_case(substr($method, 4)), $parameters[0]);
        }
        throw new \BadMethodCallException("方法 [$method] 不存在！");
    }

    public function render()
    {
        if (isset($this->view)) {
            $this->data['title'] = isset($this->data['title']) ? $this->data['title'] : "Undefined title";
            extract($this->data);
            require $this->view;
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

    public function beginPjax($id, $linkSelector = null, $formSelector = null, $scrollTo = null, array $options = null)
    {
        if (!isset($this->pjaxContainers)) {
            $this->pjaxContainers = array();
        }
        if (isset($id)) {
            if (!isset($this->pjaxContainers[$id])) {
                $pjaxContainer = new PjaxContainer($this);
            } else {
                $pjaxContainer = $this->pjaxContainers[$id];
            }
            if (isset($linkSelector))
                $pjaxContainer->linkSelector = $linkSelector;
            if (isset($formSelector))
                $pjaxContainer->formSelector = $formSelector;
            if (isset($scrollTo))
                $pjaxContainer->scrollTo = $scrollTo;
            if (isset($options))
                $pjaxContainer->clientOptions = $options;
            $pjaxContainer->begin($id);
            $this->pjaxContainers[$id] = $pjaxContainer;
        }
    }

    public function endPjax($id)
    {
        if (isset($id)) {
            if (isset($this->pjaxContainers[$id])) {
                $this->pjaxContainers[$id]->end();
            }
        }
    }

    public function registerJs($js)
    {
        echo '<script type="text/javascript">jQuery(document).ready(function(){' . $js . '});</script>';
    }
}

 