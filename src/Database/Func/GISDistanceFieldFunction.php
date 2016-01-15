<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16-1-12
 * Time: 下午11:34
 */

namespace Xaircraft\Database\Func;


use Xaircraft\Database\FieldInfo;
use Xaircraft\Database\QueryContext;
use Xaircraft\Globals;

class GISDistanceFieldFunction extends FieldFunction
{
    private $latField;

    private $lngField;

    private $latitude;

    private $longitude;

    private $half = 180;

    public function __construct($lngField, $latField, $lng, $lat)
    {
        parent::__construct($latField);

        $this->latField = $latField;
        $this->lngField = $lngField;
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    public function getString(QueryContext $context)
    {
        $pi = Globals::PI;
        $earthRadius = Globals::EARTH_RADIUS;
        $latFieldName = $context->makeFieldInfo($this->latField)->getName($context);
        $lngFieldName = $context->makeFieldInfo($this->lngField)->getName($context);
        return "ACOS(SIN(($this->latitude * $pi) / $this->half) * SIN(($latFieldName * $pi) / $this->half) + COS(($this->latitude * $pi) / $this->half) * COS(($latFieldName * $pi) / $this->half) * COS(($this->longitude * $pi) / $this->half - ($lngFieldName * $pi) / $this->half)) * $earthRadius";
    }
}