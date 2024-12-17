#! /usr/local/php/bin/php -f
<?
include_once('/home/sitemgr/nimda/common_lib/CommonUtil.php');
include_once('/home/sitemgr/nimda/engine/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');

/** 
 * @brief 이전3개월 실적에 따른 회원 등급 변경 
 */ 

function main() {

    $connectionPool = new ConnectionPool();
    $conn = $connectionPool->getPooledConnection();
    $dao = new EngineDAO();
    $util = new CommonUtil();

    $conn->StartTrans();

    $prdt_arr = array();
    $today = date("Y-m-d");
    $now_time = date("H");

    if ($now_time == "11") {
        $prdt_arr[0] = "master";

    } else if ($now_time == "14") {
        $prdt_arr[0] = "card_nc";
        $prdt_arr[1] = "green_bag";

    } else if ($now_time == "16") {
        $prdt_arr[0] = "magnet";

    } else if ($now_time == "17") {
        $prdt_arr[0] = "master";

    } else if ($now_time == "18") {
        $prdt_arr[0] = "menu_plate";
    } else {
        exit;
    }

    $param = array();
    $param["table"] = "state_admin";
    $param["col"] = "state_code";
    $param["where"]["erp_state_name"] = "인쇄중";

    $state = $dao->selectData($conn, $param)->fields["state_code"];

    foreach ($prdt_arr as $key => $value) {

        $param = array();
        $param["outsource_etprs_cate"] = $value;
        $param["receipt_finish_date"] = $today. " " . $now_time;

        $sel_rs = $dao->selectOutsourceOrderCommon($conn, $param);

        while ($sel_rs && !$sel_rs->EOF) {

            $param = array();
            $param["table"] = "order_detail";
            $param["col"]["state"] = $state;
            $param["prk"] = "order_detail_seqno";
            $param["prkVal"] = $sel_rs->fields["order_detail_seqno"];

            $rs = $dao->updateData($conn, $param);

            if (!$rs) {
                $check = 0;
            }

            $param = array();
            $param["table"] = "order_common";
            $param["col"]["order_state"] = $state;
            $param["prk"] = "order_common_seqno";
            $param["prkVal"] = $sel_rs->fields["order_common_seqno"];

            $rs = $dao->updateData($conn, $param);

            if (!$rs) {
                $check = 0;
            }

            $sel_rs->moveNext();
        }
    }

    if ($conn->HasFailedTrans() === true) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        $conn->close();
    }

    $conn->CompleteTrans();

    $conn->close();
}

main();
?>
