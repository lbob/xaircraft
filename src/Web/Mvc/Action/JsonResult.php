<?php

namespace Xaircraft\Web\Mvc\Action;
use Xaircraft\App;


/**
 * Class JsonResult
 *
 * @package XAircraft\Mvc\Action
 * @author lbob created at 2014/11/25 12:58
 */
class JsonResult extends ActionResult {

    private $object;

    public function __construct($object = null) {
        if (isset($object)) {
            $this->object = $object;
        }
    }

    public function execute()
    {
        if (isset($this->data) && !empty($this->data)) {
            $json = $this->data;
            if (isset($this->object)) {
                if (is_object($this->object))
                    $json[strtolower(get_class($this->object))] = $this->object;
                else
                    $json[] = $this->object;
            }
        } else {
            $json = $this->object;
        }
        echo json_encode($json);

        App::killModule('UbenchModule');
    }
}

 