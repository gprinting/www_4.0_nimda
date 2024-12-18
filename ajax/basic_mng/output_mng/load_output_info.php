<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OutputMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$outputDAO = new OutputMngDAO();

//출력 일련번호
$output_seqno = $fb->form("output_seqno");

$param = array();
$param["output_seqno"] = $output_seqno;

$result = $outputDAO->selectPrdcOutputList($conn, $param);

//출력 대분류
$param = array();
$param["table"] = "produce_sort";
$param["col"] = "sort";
$param["where"]["produce_dvs"] = "2";

$t_result = $outputDAO->selectData($conn, $param);

$arr = [];
$arr["dvs"] = "sort";
$arr["val"] = "sort";

$top_html = makeSelectOptionHtml($t_result, $arr);

$param = array();
$param["manu_name"] = $result->fields["manu_name"];
$param["brand"] = $result->fields["brand"];
$param["top_html"] = $top_html;
$param["name"] = $result->fields["name"];
$param["board"] = $result->fields["board"];
$param["wid_size"] = $result->fields["wid_size"];
$param["vert_size"] = $result->fields["vert_size"];
$param["crtr_unit"] = $result->fields["crtr_unit"];

$html = getPrdcOutputView($param);

$select_box_val = $result->fields["top"] . "♪♡♭" . $result->fields["affil"];

echo $html . "♪♥♭" . $select_box_val;

$conn->close();
?>
