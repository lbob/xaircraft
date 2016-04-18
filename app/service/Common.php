<?php
use Location\Polygon;

/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/12
 * Time: 13:49
 */
class Common
{
    public static function test()
    {
        $geofence = new Polygon();
        var_dump($geofence);
    }
}