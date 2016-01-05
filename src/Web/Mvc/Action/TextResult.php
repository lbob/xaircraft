<?php

namespace Xaircraft\Web\Mvc\Action;


/**
 * Class TextResult
 *
 * @package XAircraft\Web\Mvc\Action
 * @author lbob created at 2014/11/25 14:30
 */
class TextResult extends ActionResult {

    private $text = '';

    public function __construct($text) {
        $this->text = $text;
    }

    public function execute()
    {
        echo $this->text;
    }
}

 