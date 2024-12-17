<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-12
 * Time: 14:49
 */

class string_util {
    public static function IsNullOrEmptyString($value) {
        return (!isset($value) || trim($value) === '');
    }
}

?>