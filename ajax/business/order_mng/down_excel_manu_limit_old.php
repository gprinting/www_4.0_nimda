<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 생산투입한도설정 엑셀 다운로드
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/05 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$member_seqno = $fb["seqno"];

$param = array();
$param["member_seqno"] = $member_seqno;

//$conn->debug = 1;

$rs = $dao->selectManuLimitForExcel($conn, $param);

$excel_head_form = makeManuLimitExcelHeadData();

//$excel_data = $excel_head_form;

//$excel_data = makeExcel($excel_head_form, $file);

$conn->Close();
exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/
function makeManuLimitExcelHeadData() {
    $excel_head_arr = array();
    array_push($excel_head_arr, "No.");
    array_push($excel_head_arr, "회원명");
    array_push($excel_head_arr, "조정일자");
    array_push($excel_head_arr, "생산투입한도금액");
    array_push($excel_head_arr, "입금약속일");
    array_push($excel_head_arr, "담당자");
    array_push($excel_head_arr, "조정상품");
    array_push($excel_head_arr, "상세메모");
    array_push($excel_head_arr, "출고담당");
    array_push($excel_head_arr, "입금여부");

    return $excel_head_arr;
}

function makeExcel($arr_data, $filename = "test.csv") {

    $file_dir = $_SERVER["DOCUMENT_ROOT"]. "/down_excel/test.csv";
    $fp = fopen($file_dir, 'w');

    fputcsv($fp, $arr_data);
    fclose($fp);

    header("Content-type: text/csv");
    header("Content-disposition: attachment; filename = test.csv");
    readfile("$file_dir");
}

function makeManuLimitExcelBodyData($rs) {

    /*while ($rs && !$rs->EOF) {
        $fields = $rs->fields;
        print_r($fields);
        $rs->MoveNext();
    } */
}

?>
