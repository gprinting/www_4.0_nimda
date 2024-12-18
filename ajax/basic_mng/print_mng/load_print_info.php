<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PrintMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$printDAO = new PrintMngDAO();

//인쇄 일련번호
$print_seqno = $fb->form("print_seqno");

$param = array();
$param["print_seqno"] = $print_seqno;

$result = $printDAO->selectPrdcPrintList($conn, $param);

//인쇄 대분류
$param = array();
$param["table"] = "produce_sort";
$param["col"] = "sort";
$param["where"]["produce_dvs"] = "3";

$t_result = $printDAO->selectData($conn, $param);

$arr = [];
$arr["dvs"] = "sort";
$arr["val"] = "sort";

$top_html = makeSelectOptionHtml($t_result, $arr);

$param = array();
$param["manu_name"] = $result->fields["manu_name"];
$param["brand"] = $result->fields["brand"];
$param["top_html"] = $top_html;
$param["name"] = $result->fields["name"];
$param["crtr_tmpt"] = $result->fields["crtr_tmpt"];
$param["wid_size"] = $result->fields["wid_size"];
$param["vert_size"] = $result->fields["vert_size"];
$param["crtr_unit"] = $result->fields["crtr_unit"];

$html = getPrdcPrintView($param);

$select_box_val = $result->fields["top"] . "♪♡♭" . $result->fields["affil"];

echo $html . "♪♥♭" . $select_box_val;

$conn->close();
?>
