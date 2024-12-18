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
$param["table"] = "sheet_typset";
$param["col"] = "typset_format_seqno";
$param["where"]["sheet_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

if ($rs->fields["typset_format_seqno"]) {

    $param = array();
    $param["table"] = "typset_format";
    $param["col"] = "name";
    $param["where"]["typset_format_seqno"] = $rs->fields["typset_format_seqno"];

    $rs = $dao->selectData($conn, $param);

    echo $rs->fields["name"];

} else {
    echo "";
}
$conn->close();
?>
