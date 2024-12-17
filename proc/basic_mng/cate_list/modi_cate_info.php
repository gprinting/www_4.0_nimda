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
$param["mono_dvs"] = $fb->form("mono_dvs");
$param["cate_name"] = $fb->form("cate_name");
$param["flattyp_yn"] = $fb->form("flattyp_yn");
$param["typset_way"] = $fb->form("typset_way");
$param["outsource_etprs_cate"] = $fb->form("outsource_etprs_cate");
$param["use_yn"] = $fb->form("use_yn");
$param["amt_unit"] = $fb->form("amt_unit");
$param["tmpt_dvs"] = $fb->form("tmpt_dvs");
$param["channel_price_rate"] = $fb->form("channel_price_rate");
$param["sell_site"] = $_SERVER["SELL_SITE"];

//카테고리 별 계산방식 정보 가져옴
//1 : 전체, 2 : 합판, 3 : 독판
$conn->StartTrans();
$rs = $cateListDAO->updateCalculWay($conn, $param);
$conn->CompleteTrans();

$cateListDAO->deletePriceRate($conn, $param);
$cateListDAO->insertPriceRate($conn, $param);

if ($rs === false) {
    echo "false";
} else { 
    echo "true";
}

$conn->close();
?>
