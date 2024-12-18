<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$organDAO = new OrganMngDAO();

//판매채널
$sell_site = $fb->form("sell_site");

//상위 부서 코드
$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_name, depar_code";
$param["where"]["depar_level"] = "1";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$high_rs = $organDAO->selectData($conn, $param);

$arr = array();
$arr["flag"] = "N";
$arr["val"] = "depar_code";
$arr["dvs"] = "depar_name";
$html = makeSelectOptionHtml($high_rs, $arr);
if (!$html) {
    
    $html = "<option value=\"\">상위부서 없음 DB에서 추가</option>";
}

echo $html;

$conn->close();
?>
