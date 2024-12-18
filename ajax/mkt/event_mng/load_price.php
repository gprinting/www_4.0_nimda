<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();

$cate_sortcode = $fb->form("cate_sortcode");
$sell_site = $fb->form("sell_site");
$mono_yn = intval($fb->form("mono_yn"));

//* 판매채널에 해당하는 합판/계산형 가격테이블 검색
$table_name = $dao->selectPriceTableName($conn, $mono_yn, $sell_site);

//종이 맵핑코드
$param = array();
$param["table"] = "cate_paper";
$param["col"] = "mpcode";
$param["where"]["cate_sortcode"] = $fb->form("cate_sortcode");
$param["where"]["name"] = $fb->form("paper_name");
$param["where"]["dvs"] = $fb->form("paper_dvs");
$param["where"]["basisweight"] = $fb->form("paper_basisweight");

$paper_rs = $dao->selectData($conn, $param);
$paper_mpcode = $paper_rs->fields["mpcode"];

unset($paper_rs);
unset($param);

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["tmpt"]          = $fb->form("print_tmpt");

//인쇄 맵핑코드
$print_rs = $dao->selectCatePrintMpcode($conn, $param);
$print_mpcode = $print_rs->fields["mpcode"];

unset($print_rs);
unset($param);

//종이 맵핑코드
$param["paper_mpcode"] = $paper_mpcode;
//인쇄 맵핑코드
$param["print_mpcode"] = $print_mpcode;
//카테고리 분류코드
$param["cate_sortcode"] = $cate_sortcode;
//규격 맵핑코드
$param["stan_mpcode"] = $fb->form("output_size");
$param["amt"] = $fb->form("amt");

$price_info_rs = $dao->selectCatePriceList($conn, $table_name, $param);
$basic_price = $price_info_rs->fields["basic_price"];

echo $basic_price;

$conn->Close();
exit;

NOT_PRICE :
    $conn->Close();
    echo "가격없음";
    exit;
?>
