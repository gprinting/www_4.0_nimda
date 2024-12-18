<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cashbookDAO = new CashbookRegiDAO();

//입출금경로 상세
$param = array();
$param["path"] = $fb->form("depo_path");
$result = $cashbookDAO->selectPathDetail($conn, $param);

//셀렉트 옵션 셋팅
$param = array();
$param["flag"] = "N";
$param["val"] = "name";
$param["dvs"] = "name";
$detail_html = makeSelectOptionHtml($result, $param);

if ($detail_html == "") {

    $detail_html = "\n  <option value=\"\">상세없음</option>";

}

echo $detail_html;
$conn->close();
?>
