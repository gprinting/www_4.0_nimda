<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/AfterMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$afterDAO = new AfterMngDAO();

//후공정 일련번호
$after_seqno = $fb->form("after_seqno");

$param = array();
$param["after_seqno"] = $after_seqno;
$result = $afterDAO->selectPrdcAfterList($conn, $param);

//후공정명
$param = array();
$param["table"] = "after";
$param["col"] = "DISTINCT name";

$t_result = $afterDAO->selectData($conn, $param);

$arr = [];
$arr["dvs"] = "name";
$arr["val"] = "name";

$name_html = makeSelectOptionHtml($t_result, $arr);

$param = array();
$param["manu_name"] = $result->fields["manu_name"];
$param["brand"] = $result->fields["brand"];
$param["name_html"] = $name_html;
$param["depth1"] = $result->fields["depth1"];
$param["depth2"] = $result->fields["depth2"];
$param["depth3"] = $result->fields["depth3"];
$param["amt"] = $result->fields["amt"];
$param["crtr_unit"] = $result->fields["crtr_unit"];

$html = getPrdcAfterView($param);

$select_box_val = $result->fields["name"];

echo $html . "♪♥♭" . $select_box_val;

$conn->close();
?>
