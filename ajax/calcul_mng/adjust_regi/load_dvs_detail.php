<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/settle/AdjustRegiDAO.inc');


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$adjustDAO = new AdjustRegiDAO();

//계정 상세
$param = array();
$param["table"] = "input_dvs_detail";
$param["col"] = "name, input_dvs_detail_seqno";
$param["where"]["input_dvs_name"] = $fb->form("dvs");
/*
if ($fb->form("dvs") == "충전") {
    $param["where"]["discount_yn"] = "Y";
}
*/

$result = $adjustDAO->selectData($conn, $param);

//셀렉트 옵션 셋팅
$param["flag"] = "N";
$param["val"] = "name";
$param["dvs"] = "name";
$detail_html = makeSelectOptionHtml($result, $param);

if ($detail_html == "") {

    $detail_html = "\n  <option value=\"\">상세없음</option>";

}

echo $detail_html;
?>
