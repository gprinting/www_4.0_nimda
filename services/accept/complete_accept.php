<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$fb = $fb->getForm();

$code = "0000";
$value = "succeeded.";

// 조판대기 상태값 검색
$query  = "\n SELECT state_code, erp_state_name";
$query .= "\n   FROM state_admin";
$query .= "\n  WHERE erp_state_name IN ('조판대기', '시안대기')";
$rs = $conn->Execute($query);
$state_arr = [];
while ($rs && !$rs->EOF) {
    $flds = $rs->fields;
    $state_arr[$flds["erp_state_name"]] = $flds["state_code"];
    $rs->MoveNext();
}

// 접수완료 후 반환값
$order_num = $fb["order_id"];
$receipt_num = $fb["accept_id"];
$receipt_mng = $fb["accept_mng"];
$receipt_start_date  = $fb["accept_start"];
$receipt_finish_date = $fb["accept_finish"];

if (empty($order_num)
        || empty($receipt_num)
        || empty($receipt_mng)
        || empty($receipt_start_date)
        || empty($receipt_finish_date)) {
    $code = "0004";
    $value = "param null.";
    goto END;
}

// order_common_seqno 검색
$query  = "\n SELECT order_common_seqno";
$query .= "\n   FROM order_common";
$query .= "\n  WHERE order_num = %s";
$query  = sprintf($query, $conn->param($order_num));
$seqno = $conn->Execute($query, [$order_num])->fields["order_common_seqno"];

// 옵션에 시안보기 있는지 확인
$query  = "\n SELECT opt_name";
$query .= "\n   FROM order_opt_history";
$query .= "\n  WHERE order_common_seqno = %s";
$query .= "\n    AND opt_name = '시안요청'";
$query  = sprintf($query, $conn->param($seqno));
$rs = $conn->Execute($query, [$seqno]);

$state = $state_arr["조판대기"];
if (!$rs->EOF) {
    $state = $state_arr["시안대기"];
}
unset($rs);

$conn->StartTrans();

$query  = "\n UPDATE  order_common";
$query .= "\n    SET  order_state = '%s'";
$query .= "\n        ,receipt_num = '%s'";
$query .= "\n        ,receipt_mng = '%s'";
$query .= "\n        ,receipt_start_date = '%s'";
$query .= "\n        ,receipt_finish_date = '%s'";
$query .= "\n  WHERE  order_num = '%s'";
$query  = sprintf($query, $state
                        , $receipt_num, $receipt_mng
                        , $receipt_start_date, $receipt_finish_date
                        , $order_num);
$ret = $conn->Execute($query);

if ($conn->HasFailedTrans() || $proc_ret === false) {
    $code = "0001";
    $value = "order_common failed.";
    goto END;
}

$query  = "\n UPDATE order_detail";
$query .= "\n    SET state = '%s'";
$query .= "\n  WHERE order_common_seqno = '%s'";
$query  = sprintf($query, $state, $seqno);
$ret = $conn->Execute($query);

if ($conn->HasFailedTrans() || $proc_ret === false) {
    $code = "0002";
    $value = "order_detail failed.";
    goto END;
}

$query  = "\n UPDATE order_detail_brochure";
$query .= "\n    SET state = '%s'";
$query .= "\n  WHERE order_common_seqno = '%s'";
$query  = sprintf($query, $state, $seqno);
$ret = $conn->Execute($query);

if ($conn->HasFailedTrans() || $proc_ret === false) {
    $code = "0003";
    $value = "order_detail_brchure failed.";
    goto END;
}

END:
    $conn->CompleteTrans();
    $conn->Close();

    echo sprintf("{\"result\" : {\"code\" : \"%s\", \"value\" : \"%s\"}}", $code, $value);
