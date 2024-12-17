<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$order_common_seqno = $fb->form("order_common_seqno");
$opt_seqno = $fb->form("opt_seqno");

$opt_seqno_arr = explode(",", $opt_seqno);

$conn->StartTrans();

foreach ($opt_seqno_arr as $value) {

    $param = array();
    $param["table"] = "opt";
    $param["col"] = "name, depth1, depth2, depth3";
    $param["where"]["opt_seqno"] = $value;
  
    $sel_rs = $dao->selectData($conn, $param);

    $param = array();
    $param["table"] = "order_opt_history";
    $param["col"] = "order_opt_history_seqno";
    $param["where"]["opt_name"] = $sel_rs->fields["name"];
    $param["where"]["depth1"] = $sel_rs->fields["depth1"];
    $param["where"]["depth2"] = $sel_rs->fields["depth2"];
    $param["where"]["depth3"] = $sel_rs->fields["depth3"];
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $sel_rs2 = $dao->selectData($conn, $param);

    if ($sel_rs2->EOF == 1) {
        $param = array();
        $param["table"] = "order_opt_history";
        $param["col"]["opt_name"] = $sel_rs->fields["name"];
        $param["col"]["depth1"] = $sel_rs->fields["depth1"];
        $param["col"]["depth2"] = $sel_rs->fields["depth2"];
        $param["col"]["depth3"] = $sel_rs->fields["depth3"];
        $param["col"]["price"] = 0;
        $param["col"]["order_common_seqno"] = $order_common_seqno;
        $param["col"]["basic_yn"] = "Y";
        $param["col"]["detail"] = "지시서 수정";

        $rs = $dao->insertData($conn, $param);

        if (!$rs) {
            $check = 0;
        }
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
