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
$param["col"] = "typset_num";
$param["where"]["sheet_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

$param = array();
$param["typset_num"] = $typset_num;
$rs = $dao->selectPrintDirectionsView($conn, $param);

$param = array();
$param["table"] = "paper_op";
$param["col"] = "COUNT(*) AS cnt";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

if ($sel_rs->fields["cnt"] < 1) {
    $process_yn = "N";
} else {
    $process_yn = "Y";
}

$param = array();
$param["table"] = "produce_process_flow";
$param["col"]["paper_yn"] = $process_yn;
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$up_rs = $dao->updateData($conn, $param);

echo $rs->fields["manu_name"] . "♪" . 
     $rs->fields["extnl_brand_seqno"] . "♪" .
     $process_yn;

$conn->close();
?>
