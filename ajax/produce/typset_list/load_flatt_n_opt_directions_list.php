<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$param = array();
$param["table"] = "order_opt_history";
$param["col"] = "order_opt_history_seqno, opt_name ,depth1 ,depth2 ,depth3";
$param["where"]["order_common_seqno"] = $fb->form("order_common_seqno");

$rs = $dao->selectData($conn, $param);
$list = "";
$i = 1;

while ($rs && !$rs->EOF) {

    if ($i % 2 == 0) {
        $class = "cellbg";
    } else if ($i % 2 == 1) {
        $class = "";
    }

    $order_opt_history_seqno = $rs->fields["order_opt_history_seqno"];

    $param = array();
    $param["table"] = "opt";
    $param["col"] = "opt_seqno, name ,depth1 ,depth2 , depth3 ,amt ,crtr_unit";
    $param["where"]["search_check"] = $rs->fields["opt_name"] . "|" . 
        $rs->fields["depth1"] . "|" . $rs->fields["depth2"] . "|" . $rs->fields["depth3"];

    $param["class"] = $class;
    $param["order_opt_history_seqno"] = $order_opt_history_seqno;
    $sel_rs = $dao->selectData($conn, $param);
    $list .= makeOptOpListHtml($sel_rs, $param);
    $i++;
    $rs->moveNext();
}

echo $list;
$conn->close();
?>
