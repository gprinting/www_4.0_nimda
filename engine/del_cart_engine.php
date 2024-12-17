#! /usr/local/php/bin/php -f
<?
/**
 * @file del_cart_engine.php
 *
 * @brief 장바구니(주문대기) 상태에서 1주이상 경과한 주문 삭제
 */

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . "/define/common_config.php");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EngineDAO();

$conn->debug = 1;
$rs = $dao->selectCartOrderCommon($conn);

$seqno_arr = [];

while ($rs && !$rs->EOF) {
    $order_common_seqno = $rs->fields["order_common_seqno"];

    $info_rs = $dao->selectOrderInfo($conn, $order_common_seqno);

    $conn->StartTrans();

    $param["table"] = "order_opt_history";
    $param["order_common_seqno"] = $order_common_seqno;
    $dao->deleteOrderData($conn, $param);

    $param["table"] = "order_dlvr";
    $param["order_common_seqno"] = $order_common_seqno;
    $dao->deleteOrderData($conn, $param);

    while ($info_rs && !$info_rs->EOF) {
        $fields = $info_rs->fields;

        $s_detail_seqno   = $fields["s_detail_seqno"];
        $s_detail_dvs_num = $fields["s_detail_dvs_num"];
        $b_detail_dvs_num = $fields["b_detail_dvs_num"];

        $order_detail_seqno_arr = array();
        $order_detail_dvs_num_arr = array();

        if (empty($s_detail_seqno) === false) {
            $order_detail_seqno_arr[] = $s_detail_seqno;
        }

        if (empty($s_detail_dvs_num) === false) {
            $order_detail_dvs_num_arr[] = $s_detail_dvs_num;
        }

        if (empty($b_detail_dvs_num) === false) {
            $order_detail_dvs_num_arr[] = $b_detail_dvs_num;
        }

        if (count($order_detail_seqno_arr) > 0) {
            $param["order_detail_seqno"] = $order_detail_seqno_arr;
            $dao->deleteOrderDetailCountFile($conn, $param);
        }

        if (count($order_detail_dvs_num_arr) > 0) {
            $param["order_detail_dvs_num"] = $order_detail_dvs_num_arr;
            $dao->deleteOrderAfterHistory($conn, $param);
        }
        
        $info_rs->MoveNext();
    }

    $param["table"] = "order_detail";
    $dao->deleteOrderData($conn, $param);

    $param["table"] = "order_detail_brochure";
    $dao->deleteOrderData($conn, $param);

    // 주문 파일은 기업로드된 파일 삭제후 테이블 데이터 삭제
    deleteOrderFile($conn, $dao, $order_common_seqno);

    $param["table"] = "order_file";
    $dao->deleteOrderData($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        // 에러사항 처리 필요
        $conn->FailTrans();
        $conn->RollbackTrans();
    }

    $conn->CompleteTrans();

    $ret = $dao->deleteOrderCommon($conn, $param);

    if ($conn->HasFailedTrans() === true) {
        // 에러사항 처리 필요
        $conn->FailTrans();
        $conn->RollbackTrans();
    }

    if ($delete_ret === false) {
        // 에러사항 처리 필요
    }

    $rs->MoveNext();
}

function deleteOrderFile($conn, $dao, $seqno) {
    $rs = $dao->selectOrderFile($conn, ["order_common_seqno" => $seqno]);

    $base_path = "/home/sitemgr" . SITE_NET_DRIVE;
    $file_path = $rs["file_path"] . $rs["save_file_name"];

    @unlink($base_path . $file_path);
}
?>
