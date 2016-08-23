<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/4
 * Time: 16:18
 *
 * @var $router \Xaircraft\Router\Router
 */

$router->mappings['wechat_pm'] = array(
    'expression' => '/wechat/pm/{controller}?/{action}?/{id}?',
    'default' => array(
        'controller' => 'home',
        'action' => 'index',
        'id' => 0
    )
);

$router->mappings['workspace_wechat_pm'] = array(
    'expression' => '/{workspace}/wechat/pm/{controller}?/{action}?/{id}?',
    'default' => array(
        'workspace' => 'workflow',
        'controller' => 'home',
        'action' => 'index',
        'id' => 0
    ),
    'pattern' => array(
        'workspace' => '[0-9a-zA-Z\_\-]+'
    )
);

$router->mappings['user'] = array(
    'expression' => '/user/{controller}?/{action}?/{id}?',
    'default' => array(
        'controller' => 'home',
        'action' => 'index',
        'id' => 0
    )
);
