<?php

namespace Xaircraft\Web\Mvc\Action;
use Xaircraft\App;


/**
 * Class StatusResult
 *
 * @package XAircraft\Mvc\Action
 * @author lbob created at 2014/11/25 15:05
 */
class StatusResult extends ActionResult
{
    /**
     * @var int
     */
    public $statusCode = 0;

    /**
     * @var string
     */
    public $message;

    public $params;

    /**
     * @param $message string
     * @param $statusCode int
     * @param $params array
     */
    public function __construct($message, $statusCode, $params = null) {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->params = $params;
    }

    public function execute()
    {
        $json = array();
        $json['status'] = $this->statusCode;
        $json['message'] = $this->message;

        if (isset($this->data) && !empty($this->data)) {
            $json['data'] = $this->data;
        }

        if (isset($this->params)) {
            $json['data'] = array();
            if (is_array($this->params)) {
                foreach ($this->params as $key => $value) {
                    $json['data'][$key] = $value;
                }
            } else {
                $json['data'] = $this->params;
            }
        }

        echo json_encode($json);

        App::killModule('UbenchModule');
    }
}

 