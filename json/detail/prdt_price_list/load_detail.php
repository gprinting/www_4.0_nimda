<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');
include_once(INC_PATH . '/com/nexmotion/doc/nimda/basic_mng/prdt_price_mng/PrdtPriceListPrintCond.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new CommonUtil();

$cate_sortcode = $fb->form("cate_sortcode");
$mono_yn       = $fb->form("mono_yn");
$sell_site     = $fb->form("sell_site");
$etprs_dvs     = $fb->form("etprs_dvs");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["mono_yn"] = $mono_yn;

//$conn->debug = 1;

// 카테고리의 도수구분, 계산형 여부 검색
$rs = $dao->selectCateFlatMonoInfo($conn, $param);
$mono_dvs   = $rs->fields["mono_dvs"];
$tmpt_dvs   = $rs->fields["tmpt_dvs"];

$ret  = "{";
$ret .= " \"paper\"      : \"%s\",";
$ret .= " \"size_typ\"   : \"%s\",";
//$ret .= " \"size\"       : \"%s\",";
$ret .= " \"print\"      : \"%s\",";
$ret .= " \"mono_dvs\"   : \"%s\",";
$ret .= " \"tmpt_dvs\"   : \"%s\",";
$ret .= " \"amt\"        : \"%s\"";
$ret .= "}";

$paper = $dao->selectCatePaperHtml($conn, "NAME", $param);
$paper = $util->convJsonStr($paper);

$size_typ = $dao->selectCateSizeTyp($conn, $param);
$param["typ"] = $size_typ->fields["typ"];

$size_typ = makeOptionHtml($size_typ, "typ", "typ");
$size_typ = $util->convJsonStr($size_typ);

//$size = $dao->selectCateSizeHtml($conn, $param);
//$size = $util->convJsonStr($size);

$print = null;
//$conn->debug = 1;
if ($tmpt_dvs === '1') {
    $print = makePrintCondHtmlBefAft($conn, $dao, $param);
} else {
    $print = makePrintCondHtmlSingleBoth($conn, $dao, $param);
}
$print = $util->convJsonStr($print);

$amt_unit = $dao->selectCateAmtUnit($conn, $cate_sortcode);
$table_name = "ply_price";
$param["table_name"] = $table_name;
$param["amt_unit"] = $amt_unit;
$amt = $dao->selectCateAmtHtml($conn, $param);
$amt = $util->convJsonStr($amt);

echo sprintf($ret, $paper, $size_typ/*, $size*/, $print, $mono_dvs, $tmpt_dvs, $amt);

$conn->Close();
?>
