<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/after_mng/AfterListDAO.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterListDAO();
$MoamoaDAO = new MoamoaDAO();

$util = new CommonUtil();

$conn->StartTrans();
$check = 1;
$ordernum_arr = explode(",", $fb->form("ordernum"));
$after = $fb->form("after");

foreach ($ordernum_arr as $key => $value) {
    $order_num = $value;

    $param = array();
    $param["after_name"] = $after;
    $param["ordernum"] = $order_num;

    $rs = $dao->selectOrderAfterHistorySeqno($conn, $param);

    $order_after_history_seqno = $rs->fields["order_after_history_seqno"];
    $param = array();
    $param["table"] = "order_after_history";
    $param["col"] = "after_name, order_common_seqno";
    $param["where"]["order_after_history_seqno"] = $order_after_history_seqno;

    $after_rs = $dao->selectData($conn, $param);

    $after_name = $after_rs->fields["after_name"];
    $order_common_seqno = $after_rs->fields["order_common_seqno"];

    $param = array();
    $param["table"] = "order_common";
    $param["col"] = "order_num";
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $order_common_rs = $dao->selectData($conn, $param);
    $order_num = $order_common_rs->fields["order_num"];

    $param = array();
    $param["table"] = "order_dlvr";
    $param["col"] = "dlvr_way";
    $param["where"]["order_common_seqno"] = $order_common_seqno;
    $param["where"]["tsrs_dvs"] = "수신";

    $dlvr_way_rs = $dao->selectData($conn, $param);
    $dlvr_way = $dlvr_way_rs->fields["dlvr_way"];

    // order_state_history 인서트
    $tmp_param = array();
    $tmp_param["ordernum"] = $order_num;
    $tmp_param["state"] = "2780";
    $tmp_param["empl_id"] = $fb->getSession()["id"];
    $tmp_param["detail"] = $after_name;
    $MoamoaDAO->insertStateHistory($conn, $tmp_param);

    $tmp_param = array();
    $tmp_param["order_common_seqno"] = $order_common_seqno;
    $complete_after_rs = $dao->selectCompleteAfter($conn, $tmp_param);
    $complete_cnt = $complete_after_rs->fields["cnt"];

    $tmp_param = array();
    $tmp_param["order_common_seqno"] = $order_common_seqno;
    $all_after_rs = $dao->selectAllAfter($conn, $tmp_param);
    $all_cnt = $all_after_rs->fields["cnt"];
    if($complete_cnt >= $all_cnt) {
        $tmp_param = array();
        $tmp_param["ordernum"] = $order_num;
        $tmp_param["state"] = "2780";
        $tmp_param["empl_id"] = $fb->getSession()["id"];
        $MoamoaDAO->updateProductStatecode($conn, $tmp_param);
        $MoamoaDAO->insertStateHistory($conn, $tmp_param);

        if($dlvr_way == "01")
            $tmp_param["state"] = "3420";
        else {
            $tmp_param["state"] = "3120";
        }
        $MoamoaDAO->updateProductStatecode($conn, $tmp_param);
        $MoamoaDAO->insertStateHistory($conn, $tmp_param);
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check; 
?>
