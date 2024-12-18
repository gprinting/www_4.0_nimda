<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/CateListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cateListDAO = new CateListDAO();

$param = array();
$param["sortcode"] = $fb->form("sortcode");
$param["sell_site"] = $_SERVER["SELL_SITE"];

//카테고리 별 계산방식 정보 가져옴
//1 : 전체, 2 : 합판, 3 : 독판
$rs = $cateListDAO->selectCateInfo($conn, $param);
$rate = $rs->fields["rate"];
if($rs->fields["rate"] == null) {
     $rate = $rs->fields["basic_rate"];
}

$conn->close();
echo $rs->fields["mono_dvs"] . "♪" .
    $rs->fields["flattyp_yn"] . "♪" .
    $rs->fields["amt_unit"] . "♪" .
    $rs->fields["tmpt_dvs"] . "♪" .
    $rs->fields["typset_way"] . "♪" .
    $rs->fields["outsource_etprs_cate"] . "♪" .
    $rs->fields["use_yn"]. "♪" .
    $rate;
?>
