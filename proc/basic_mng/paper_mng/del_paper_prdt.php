<?
define("INC_PATH", $_SERVER["INC"]);
/*
 *
 * Copyright (c) 2015-2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/04 엄준현 수정(기본생산 업체 물렸는지 체크, count변수 캐싱)
 *=============================================================================
 *
 */
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$basicDAO = new BasicMngCommonDAO();
$conn->StartTrans();

$check = 1;
$is_pass = false;

$seqno_set = explode(",", $fb->form("select_prdt"));
$count_seqno_set = count($seqno_set);

//$conn->debug = 1;

for ($i = 0; $i < $count_seqno_set; $i++) {
    $seqno = $seqno_set[$i];

    $is_basic_produce = $basicDAO->selectBasicProduce($conn, "paper", $seqno);

    if (!$is_pass && $is_basic_produce) {
        $is_pass = true;
    }

    if ($is_basic_produce) {
        continue;
    }

    $param = array();
    $param["table"] = "paper";
    $param["prk"] = "paper_seqno";
    $param["prkVal"] = $seqno;
    $result = $basicDAO->deleteData($conn, $param);

    if (!$result) $check = 0;
}

$ret = 2;

if ($check == 1) {
    if ($is_pass) {
        $ret = "3";
    }

    $ret = "1";
}

echo $ret;

$conn->CompleteTrans();
$conn->close();
?>

