<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/20
 * Time: 10:49
 */

namespace Xaircraft\Database\Validation;


class ValidationCollection
{
    private $validations = array();

    public function append(Validate $validate)
    {
        $this->validations[] = $validate;
    }

    public function valid($value)
    {
        if (!empty($this->validations)) {
            foreach ($this->validations as $validate) {
                /**
                 * @var Validate $validate
                 */
                if (!$validate->valid($value)) {
                    return false;
                }
            }
        }

        return true;
    }
}