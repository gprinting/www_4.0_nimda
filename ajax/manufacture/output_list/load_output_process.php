<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OutputListDAO();


$from_date = $fb->form("date_from");
$from_time = "";
$to_date = $fb->form("date_to");
$to_time = "";

if ($from_date) {
    $from_time = $fb->form("time_from");
    $from = $from_date . " " . "00:00:00";
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to") + 1;
    $to = $to_date . " " . "23:59:59";
}

$param = array();
$param["preset_cate"] = $fb->form("preset_cate");
$param["typset_num"] = $fb->form("typset_num");
$param["state"] = $fb->form("state");
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["date_cnd"] = $fb->form("date_cnd");
$param["from"] = $from;
$param["to"] = $to;
$param["dvs"] = "SEQ";

$param["state"] = "2220";
$rs = $dao->selectOutputProcess($conn, $param);
$list1 = makeOutputProcessHtml($rs, $param);

$param["state"] = "2230";
$rs = $dao->selectOutputProcess($conn, $param);
$list2 = makeOutputProcessHtml($rs, $param);

$param["state"] = "2280";
$rs = $dao->selectOutputProcess($conn, $param);
$list3 = makeOutputProcessHtml($rs, $param);

echo $list1 . "♪" . $list2 . "♪" . $list3;
$conn->close();
?>
