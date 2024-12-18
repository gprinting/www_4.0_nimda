<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetStandbyListDAO();

$param = array();
$param["table"] = "amt_order_detail_sheet";
$param["col"] = "amt, state";
$param["where"]["order_detail_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);
$i = 1;

$html = "";
$param = array();
while ($rs && !$rs->EOF) {

    $param["num"] = $i;
    $param["val"] = $rs->fields["amt"];

    if ($rs->fields["state"] == '410') { 
        $html .= addSheetDiv($param);
    } else {
        $html .= addSheetDivDisabled($param);
    }

    $i++;
    $rs->moveNext();
}

$param = array();
$param["seqno"] = $fb->form("seqno");
$param["total"] = $fb->form("total");
$param["html"] = $html;
$param["num"] = intVal($i) - 1;

echo getDivideTypsetPopup($param);
$conn->close();
?>
