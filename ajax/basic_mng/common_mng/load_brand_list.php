<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$basicDAO = new BasicMngCommonDAO();

//제조사
$manu_seqno = $fb->form("manu_seqno");
//타입
$type = $fb->form("type");
//검색조건(제조사 일련번호 or 검색어)
$search = $fb->form("search_str");

$param = array();
$param["manu_seqno"] = $manu_seqno;
$param["search"] = $search;

$result = $basicDAO->selectPrdcBrand($conn, $param);

if ($type == "Y") {

    $arr = [];
    $arr["flag"] = "Y";
    $arr["def"] = "브랜드(전체)";
    $arr["def_val"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "extnl_brand_seqno";

    $buff = makeSelectOptionHtml($result, $arr);

} else {

    $arr = [];
    $arr["col"] = "extnl_brand_seqno";
    $arr["val"] = "name";
    $arr["type"] = "brand";

    $buff = makeSearchList($result, $arr);
}

echo $buff;
$conn->close();
?>
