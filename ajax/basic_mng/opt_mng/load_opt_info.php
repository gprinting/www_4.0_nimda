<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OptMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$optDAO = new OptMngDAO();

//옵션 일련번호
$opt_seqno = $fb->form("opt_seqno");

$param = array();
$param["opt_seqno"] = $opt_seqno;

$result = $optDAO->selectPrdcOptList($conn, $param);

//옵션명
$param = array();
$param["table"] = "opt";
$param["col"] = "DISTINCT name";

$t_result = $optDAO->selectData($conn, $param);

$arr = [];
$arr["dvs"] = "name";
$arr["val"] = "name";

$name_html = makeSelectOptionHtml($t_result, $arr);

$param = array();
$param["name_html"] = $name_html;
$param["depth1"] = $result->fields["depth1"];
$param["depth2"] = $result->fields["depth2"];
$param["depth3"] = $result->fields["depth3"];
$param["amt"] = $result->fields["amt"];
$param["crtr_unit"] = $result->fields["crtr_unit"];
$param["basic_price"] = $result->fields["basic_price"];
$param["pur_rate"] = $result->fields["pur_rate"];
$param["pur_aplc_price"] = $result->fields["pur_aplc_price"];
$param["pur_price"] = $result->fields["pur_price"];
$param["pur_price"] = $result->fields["pur_price"];

$html = getPrdcOptView($param);

$select_box_val = $result->fields["name"];

echo $html . "♪♥♭" . $select_box_val;

$conn->close();
?>
