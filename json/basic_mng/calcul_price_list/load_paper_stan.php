<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');
include_once(INC_PATH . '/com/nexmotion/doc/nimda/basic_mng/prdt_price_mng/PrdtPriceListPrintCond.inc');
include_once(INC_PATH . "/common_define/prdt_default_info.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new CommonUtil();

$cate_sortcode = $fb->form("cate_sortcode");
$mono_yn = $fb->form("mono_yn");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["mono_yn"] = '3';

//$conn->debug = 1;

$ret  = "{";
$ret .= " \"paper\"    : \"%s\",";
$ret .= " \"size_typ\" : \"%s\",";
$ret .= " \"amt\"      : \"%s\"";
$ret .= "}";

$paper = $dao->selectCatePaperHtml($conn, "NAME", $param);
$paper = $util->convJsonStr($paper);

$size_typ = $dao->selectCateSizeTyp($conn, $param);
$size_typ = makeOptionHtml($size_typ, "typ", "typ");
$size_typ = $util->convJsonStr($size_typ);


$amt_unit = $dao->selectCateAmtUnit($conn, $cate_sortcode);
$amt_arr = PrdtDefaultInfo::AMT[$cate_sortcode]; // 수량 배열
$amt_arr_count = count($amt_arr);

$amt_html = '';
for ($i = 0; $i < $amt_arr_count; $i++) {
    $amt = $amt_arr[$i];

    $float = 0;
    if ($amt < 1) {
        $float = 1;
    }

    $amt_html .= option(number_format($amt, $float) . ' ' . $amt_unit, $amt);
}

$amt_html = $util->convJsonStr($amt_html);

echo sprintf($ret, $paper, $size_typ, $amt_html);

$conn->Close();
?>
