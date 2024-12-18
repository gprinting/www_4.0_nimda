<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-05-23
 * Time: 오후 6:25
 */

class acceptorhelper {
    static $KEY = "goodprinting";
    static $CALL_WORK_COUNT_ATONCE = 20;
    static $ORDER_DETAIL_COUNT_FILE = "";
    static $ORDER_DETAIL_COUNT_PREVIEW_FILE = "";

    static function SESSION_VALID_TIME() {
        //return 5; // 1분
        return 60 * 60 * 12; // 12시간
    }

    static function REFRESH_SIGN_ATLEAST_TIME() {
        //return 30; // 30초
        return 60 * 60 * 1; // 1시간
    }

    static function chkCommonReceiptDvs($conn, $dao, $param) {
        $seqno                = $param["seqno"];
        $member_seqno         = $param["member_seqno"];
        $flattyp_yn           = $param["flattyp_yn"];
        $opt_morning_board_yn = $param["opt_morning_board_yn"];
        $opt_use_yn           = $param["opt_use_yn"];
        $after_use_yn         = $param["after_use_yn"];
        $stan_name            = $param["stan_name"];
        $file_upload_dvs      = $param["file_upload_dvs"];
        $cate_sortcode        = $param["cate_sortcode"];
        $onefile_etprs_yn     = $param["onefile_etprs_yn"];

        // 원파일 업체인지 확인
        /*
        if ($onefile_etprs_yn === 'O') {
            //echo "1";
            return "Manual";
        }
        */

        // 파일 업로드 했는지 확인
        if ($file_upload_dvs === 'N') {
            //echo "2";
            return "Manual";
        }

        // 낱장여부 확인
        if ($flattyp_yn !== 'Y') {
            //echo "3";
            return "Manual";
        }

        // 주문파일 확장자 확인
        unset($param);
        $param["member_seqno"] = $member_seqno;
        $param["order_seqno"] = $seqno;
        $file_ext = $dao->selectOrderFile($conn, $param);
        // 나중에 파일 여러개 올라올 경우 처리하도록 수정 필요함
        $file_ext = $file_ext->fields["save_file_name"];
        $file_ext = explode('.', $file_ext);
        $file_ext = strtolower($file_ext[(count($file_ext) - 1)]);

        if ($file_ext !== "ai" &&
            $file_ext !== "cdr" &&
            $file_ext !== "eps" &&
            $file_ext !== "jpe" &&
            $file_ext !== "jpg" &&
            $file_ext !== "jpeg" &&
            $file_ext !== "pdf") {
            //echo "4";
            return "Manual";
        }

        $cate_top = substr($cate_sortcode, 0, 3);
        $cate_mid = substr($cate_sortcode, 0, 6);
        if (
            // 마스터 수동
            $cate_top === "006" ||
            // 카드명함 수동
            $cate_mid === "001003" ||
            // 도무송 스티커 수동
            $cate_mid === "002002" ||
            // 광고홍보물 수동
            $cate_top === "004" ||
            // 기타인쇄 수동
            $cate_top === "008"
        ) {
            //echo "5";
            return "Manual";
        }

        // 추가후공정 있는지 확인
        if ($after_use_yn === 'Y') {
            //echo "7";
            return "Manual";
        }

        // 추가옵션 있는지 확인
        // 당일판만 들어오면 자동
        if (!$opt_morning_board_yn && $opt_use_yn === 'Y') {
            //echo "6";
            return "Manual";
        }

        // 규격 사이즈인지 확인
        /* 2016-11-18 무시
        if (strpos($stan_name, "비규격") === true) {
            //echo "8";
            return "Manual";
        }
        */

        return "Auto";
    }
}