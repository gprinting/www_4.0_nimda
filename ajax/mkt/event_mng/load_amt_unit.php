<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');
include_once(INC_PATH . '/com/nexmotion/html/nimda/mkt/mkt_mng/EventMngHTML.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new ErpCommonUtil();

$cate_sortcode = $fb->form("cate_sortcode");
$sell_site = $fb->form("sell_site");
$mono_yn = intval($fb->form("mono_yn"));

$param = array();

//* 판매채널에 해당하는 합판/계산형 가격테이블 검색
$table_name = $dao->selectPriceTableName($conn, $mono_yn, $sell_site);

//* 카테고리에 해당하는 수량단위 검색
$amt_unit = $dao->selectCateAmtUnit($conn, $cate_sortcode);

//* 종이 정보가 넘어온 것이 있을 경우 종이 맵핑코드 검색

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

$param["cate_sortcode"] = $cate_sortcode;
$param["tmpt"]          = $fb->form("print_tmpt");

$print_rs = $dao->selectCatePrintMpcode($conn, $param);
$print_mpcode = $print_rs->fields["mpcode"];

unset($print_rs);
unset($param);

$param["print_mpcode"] = $print_mpcode;

$param["cate_sortcode"] = $cate_sortcode;
$param["stan_mpcode"] = $fb->form("output_size");

// 정보를 저장할 배열들
$amt_arr        = array(); // 수량 배열

$param["paper_mpcode"] = $paper_mpcode;

$price_info_rs = $dao->selectCatePriceList($conn, $table_name, $param);

while ($price_info_rs && !$price_info_rs->EOF) {
    $amt         = $price_info_rs->fields["amt"];

    $amt_arr[$amt] = $amt;

    $price_info_rs->MoveNext();
}

if (count($amt_arr) === 0) {
    goto NOT_AMT;
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$amt_arr = $util->sortDvsArr($amt_arr);
$amt_html = makeOptionNumHtml($amt_arr);

echo $amt_html . "♪♥♭" . $amt_unit;

$conn->Close();
exit;

NOT_AMT :
    $conn->Close();
    echo "<option value=\"\">수량없음</option>";
    exit;
?>
