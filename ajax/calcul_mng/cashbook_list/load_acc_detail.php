<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cashbookDAO = new CashbookListDAO();

//계정 상세
$param = array();
$param["table"] = "acc_detail";
$param["col"] = "name, acc_detail_seqno";
$param["where"]["acc_subject_seqno"] = $fb->form("acc_subject");
$result = $cashbookDAO->selectData($conn, $param);

//셀렉트 옵션 셋팅
$param = array();
if ($fb->form("dvs") == "1") {

    $param["flag"] = "N";

} else {

    $param["flag"] = "Y";
    $param["def"] = "전체";
    $param["def_val"] = "";

}
$param["val"] = "acc_detail_seqno";
$param["dvs"] = "name";
$detail_html = makeSelectOptionHtml($result, $param);

if ($detail_html == "") {

    $detail_html = "\n  <option value=\"\">상세없음</option>";

}

echo $detail_html;
$conn->close();
?>
