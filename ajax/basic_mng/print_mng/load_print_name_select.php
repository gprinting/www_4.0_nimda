<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PrintMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintMngDAO();

//검색어
$manu_seqno  = $fb->form("manu_seqno");
$brand_seqno = $fb->form("brand_seqno");

$param = array();
$param["etprs_seqno"] = $manu_seqno;
$param["brand_seqno"] = $brand_seqno;

$rs = $dao->selectPrdcPrintName($conn, $param);

$option = "<option value=\"\">전체</option>";

while ($rs && !$rs->EOF) {
    $name = $rs->fields["name"];

    $option .= sprintf("<option value=\"%s\">%s</option>", $name, $name);

    $rs->MoveNext();
}

echo $option;

$conn->close();
?>
