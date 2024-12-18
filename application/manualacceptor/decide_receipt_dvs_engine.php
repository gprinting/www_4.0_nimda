<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-08
 * Time: 오전 10:30
 */

define("INC_PATH", "/home/sitemgr/inc");
define("CYPRESS", "/home/sitemgr/nimda/cypress");
include_once(CYPRESS . '/process/common/ConnectionPool.php');
include_once(INC_PATH . '/com/dprinting/ManualAcceptorDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new ManualAcceptorDAO();

$rs = $dao->selectNotDecidedReceiptDvsOrders($conn);

while($rs && !$rs->EOF) {
    $param = array();
    $param['order_common_seqno'] = $rs->fields['order_common_seqno'];
    $param['receipt_dvs'] = "Auto";

    $seqno = $rs->fields['order_common_seqno'];
    $member_seqno = $rs->fields['member_seqno'];
    $flattyp_yn = $rs->fields['flattyp_yn'];
    $opt_use_yn = $rs->fields['opt_use_yn'];
    $after_use_yn = $rs->fields['after_use_yn'];
    $stan_name = $rs->fields['stan_name'];
    $file_upload_dvs = $rs->fields['file_upload_dvs'];
    $cate_sortcode = $rs->fields['cate_sortcode'];
    $onefile_etprs_yn = $rs->fields['onefile_etprs_yn'];

    if ($file_upload_dvs === 'N') {
        //echo "2";
        $param['receipt_dvs'] = "Manual";
    }

// 낱장여부 확인
    if ($flattyp_yn !== 'Y') {
        //echo "3";
        $param['receipt_dvs'] = "Manual";
    }

// 나중에 파일 여러개 올라올 경우 처리하도록 수정 필요함
    $file_ext = $rs->fields["save_file_name"];
    $file_ext = explode('.', $file_ext);
    $file_ext = strtolower($file_ext[(count($file_ext) - 1)]);

    if ($file_ext !== "ai" &&
        $file_ext !== "cdr" &&
        $file_ext !== "eps" &&
        $file_ext !== "jpe" &&
        $file_ext !== "jpg" &&
        $file_ext !== "jpeg" &&
        $file_ext !== "pdf"
    ) {
        //echo "4";
        $param['receipt_dvs'] = "Manual";
    }

    $cate_top = substr($rs->fields["cate_sortcode"], 0, 3);
    $cate_mid = substr($rs->fields["cate_sortcode"], 0, 6);
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
        $param['receipt_dvs'] = "Manual";
    }

// 추가후공정 있는지 확인
    if ($after_use_yn === 'Y') {
        //echo "7";
        $param['receipt_dvs'] = "Manual";
    }

// 추가옵션 있는지 확인
// 당일판만 들어오면 자동
    if (!$dao->ReceiptDvsOptionCheck($conn, $param)) {
        $param['receipt_dvs'] = "Manual";
    }

    $dao->updateReceiptDvs($conn, $param);

    $rs->MoveNext();
}
