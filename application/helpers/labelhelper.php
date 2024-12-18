<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-12-04
 * Time: 오후 6:44
 */

class labelhelper {
    static $KEY = "goodprinting";
    static $ORDER_DETAIL_COUNT_FILE = "";
    static $ORDER_DETAIL_COUNT_PREVIEW_FILE = "";
    static function SESSION_VALID_TIME() {
        return 60 * 60 * 12; // 12시간
    }

    static function REFRESH_SIGN_ATLEAST_TIME() {
        return 60 * 60 * 1; // 1시간
    }

    static function GET_LABELID_BY_SIZE($width, $height) {
        // 가로와 세로가 모두 작으면서 비슷한 크기
        if($width <= 198 && $height <= 136) {
            return "198_136";
        } else if($width <= 80 && $height <= 44) {
            return "80_44";
        } else if($width <= 55 && $height <= 33) {
            return "55_33";
        } else if($width <= 54 && $height <= 34) {
            return "54_34";
        } else if($width <= 55 && $height <= 24) {
            return "55_24";
        } else if($width <= 50 && $height <= 20) {
            return "50_20";
        } else if($width <= 32 && $height <= 32) {
            return "32_32";
        } else if($width <= 26 && $height <= 26) {
            return "26_26";
        } else {
            return null;
        }
    }
}