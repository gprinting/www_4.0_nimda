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
 * 2017/07/27 이청산 수정(컬럼추가)
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/OrderMngUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();
$OrderMngUtil = new OrderMngUtil();

$fb = $fb->getForm();

// 파일관련
$path = $_SERVER["DOCUMENT_ROOT"] . "/down_excel/";
$name = uniqid() . ".csv";

$fd = fopen($path . $name, 'w');

if (!$fd) {
    echo "<script>alert('파일생성실패');</script>";
    exit;
}

$member_seqno = $fb["seqno"];

$param = array();
$param["member_seqno"] = $member_seqno;

$conn->debug = 1;
$csv_form = "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\r\n";

//$csv_head = makeManuLimitExcelHeadData();
$csv_head = sprintf($csv_form, iconv("UTF-8", "EUC-KR", "No.")
                             , iconv("UTF-8", "EUC-KR", "회원명")
                             , iconv("UTF-8", "EUC-KR", "조정일자")
                             , iconv("UTF-8", "EUC-KR", "생산투입한도금액")
                             , iconv("UTF-8", "EUC-KR", "거래날짜")
                             , iconv("UTF-8", "EUC-KR", "입금약속일")
                             , iconv("UTF-8", "EUC-KR", "담당자")
                             , iconv("UTF-8", "EUC-KR", "조정상품")
                             , iconv("UTF-8", "EUC-KR", "상세메모")
                             , iconv("UTF-8", "EUC-KR", "출고담당")
                             , iconv("UTF-8", "EUC-KR", "입금여부"));
fwrite($fd, $csv_head);

$rs = $dao->selectManuLimitForExcel($conn, $param);

$i = 1;
$csv_body = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    // 입금여부
    $depo_yn  = $fields["depo_yn"];
    $depo_fin = $OrderMngUtil->selectDepoYnForExcel($depo_yn);
        
    // 조정상품
    $limit_cate     = $fields["limit_cate"]; 
    $limit_cate_fin = $OrderMngUtil->selectLimitCate($limit_cate);

    // 엑셀 내용
    $body = sprintf($csv_form, $i++
                             , iconv("UTF-8", "EUC-KR", $fields["member_name"])
                             , iconv("UTF-8", "EUC-KR", $fields["regi_date"])
                             , iconv("UTF-8", "EUC-KR", $fields["limit_price"])
                             , iconv("UTF-8", "EUC-KR", $fields["deal_date"])
                             , iconv("UTF-8", "EUC-KR", $fields["depo_promi_date"])
                             , iconv("UTF-8", "EUC-KR", $fields["regi_empl"])
                             , iconv("UTF-8", "EUC-KR", $limit_cate_fin)
                             , iconv("UTF-8", "EUC-KR", $fields["memo"])
                             , iconv("UTF-8", "EUC-KR", $fields["release_empl"])
                             , iconv("UTF-8", "EUC-KR", $depo_fin));
    $csv_body .= $body;
    fwrite($fd, $body);

    $rs->MoveNext();
}

//fwrite($fd, $csv_head . $csv_body);
//엑셀파일명
$file_name = "생산투입한도_". $fields["member_name"] . ".csv";

header("Pragma: public");
header("Expires: 0");
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=". $file_name ."");
// 파일생성 안하고 다운
//echo $csv_head . $csv_body;

// 파일생성 하고 다운
ob_clean();
flush();
readfile($path . $name);

unlink($path . $name);

$conn->Close();
exit;
?>
