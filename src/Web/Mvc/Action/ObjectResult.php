<?php

namespace Xaircraft\Web\Mvc\Action;


/**
 * Class ObjectResult
 *
 * @package XAircraft\Mvc\Action
 * @author lbob created at 2014/11/25 17:39
 */
class ObjectResult extends ActionResult {

    /**
     * @var object
     */
    private $object;

    /**
     * @param null $object object
     */
    public function __construct($object = null) {
        $this->object = $object;
    }

    public function execute()
    {
        return $this->object;
    }
}

 