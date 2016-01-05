<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/1/4
 * Time: 16:18
 *
 * @var $router \Xaircraft\Router\Router
 */

$router->mappings['user'] = array(
    'expression' => '/user/{controller}?/{action}?/{id}?',
    'default' => array(
        'controller' => 'home',
        'action' => 'index',
        'id' => 0
    )
);
