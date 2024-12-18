<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cashbookDAO = new CashbookRegiDAO();

//부서 리스트
$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_admin_seqno, depar_name";
$param["where"]["cpn_admin_seqno"] = $fb->form("sell_site");
$param["where"]["depar_level"] = "2";
$result = $cashbookDAO->selectData($conn, $param);

//셀렉트 옵션 셋팅
$param = array();
$param["flag"] = "Y";
if ($fb->form("dvs") == "1") {

    $param["def"] = "선택";
    $param["def_val"] = "";

} else {

    $param["def"] = "전체";
    $param["def_val"] = "";
}

$param["val"] = "depar_admin_seqno";
$param["dvs"] = "depar_name";
$depar_html = makeSelectOptionHtml($result, $param);

if ($depar_html == "") {

    $depar_html = "\n  <option value=\"\">없음</option>";

}

echo $depar_html;
$conn->close();
?>
