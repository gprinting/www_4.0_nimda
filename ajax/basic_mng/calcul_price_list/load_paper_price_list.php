<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CalculPriceListDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$sell_site = $fb->form("sell_site");
$tax_yn    = $fb->form("tax_yn");
$paper_sort        = $fb->form("paper_sort");
$paper_name        = $fb->form("paper_name");
$paper_dvs         = $fb->form("paper_dvs");
$paper_color       = $fb->form("paper_color");
$paper_basisweight = $fb->form("paper_basisweight");
$paper_affil  = $util->getUseAffil($fb->form("paper_affil"));
$paper_size   = $fb->form("paper_size");

$param = array();

//* 종이 검색어로 상품 종이 맵핑코드 검색
$param["paper_sort"]        = $paper_sort;
$param["paper_name"]        = $paper_name;
$param["paper_dvs"]         = $paper_dvs;
$param["paper_color"]       = $paper_color;
$param["paper_basisweight"] = substr($paper_basisweight, 0, -1);
$param["paper_affil"]       = $paper_affil;
$param["paper_size"]        = $paper_size;
$param["basisweight_unit"]  = substr($paper_basisweight, -1);

$mpcode_rs = $dao->selectPrdtPaperMpcode($conn, $param);

if ($mpcode_rs->EOF === true) {
    goto NOT_PRICE;
}

$mpcode_arr = array();

$i = 0;
while ($mpcode_rs && !$mpcode_rs->EOF) {
    $mpcode_arr[$i++] = $mpcode_rs->fields["mpcode"];
    $mpcode_rs->MoveNext();
}

unset($mpcode_rs);
unset($param);

//* 맵핑코드로 가격검색
$mpcode_arr_count = count($mpcode_arr);

// 정보를 저장할 배열들
$basisweight_arr = array(); // 평량 배열
$title_arr       = array(); // 제목 배열
$title_info_arr  = array(); // 가격수정을 위한 제목 정보 배열
$price_arr       = array(); // 가격 배열

// 종이 정보 폼
$paper_info_form = "%s %s %s";
// 맵핑코드 검색용 값
$paper_title_info_form = "%s!%s!%s!%s";

// 각 정보항목 폼
// 종이분류|종이정보|계열|사이즈|기준수량
$title_form      = "%s|%s|%s|%s|%s";
// $title에 해당하는 식별값(판매채널, 종이정보, 계열, 사이즈)
$title_info_form = "%s|%s|%s|%s|-";
// 일련번호|기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s|%s";

$param["sell_site"] = $sell_site;

for ($i = 0; $i < $mpcode_arr_count; $i++) {
    $mpcode = $mpcode_arr[$i];

    $param["mpcode"] = $mpcode;

    $price_rs = $dao->selectPrdtPaperPrice($conn, $param);

    if ($price_rs->EOF === true) {
        continue;
    }

    while ($price_rs && !$price_rs->EOF) {
        $paper_sort        = $price_rs->fields["sort"];
        $paper_name        = $price_rs->fields["name"];
        $paper_dvs         = $price_rs->fields["dvs"];
        $paper_color       = $price_rs->fields["color"];
        $paper_basisweight = $price_rs->fields["basisweight"];
        $paper_affil       = $price_rs->fields["affil"];
        $paper_size        = $price_rs->fields["size"];
        $crtr_unit         = $price_rs->fields["crtr_unit"];

        $paper_info = sprintf($paper_info_form, $paper_name
                                              , $paper_dvs
                                              , $paper_color);

        $price_seqno     = $price_rs->fields["price_seqno"];
        $basic_price     = $price_rs->fields["basic_price"];
        $sell_rate       = $price_rs->fields["sell_rate"];
        $sell_aplc_price = $price_rs->fields["sell_aplc_price"];
        $sell_price      = $price_rs->fields["sell_price"];

        $title = sprintf($title_form, $paper_sort
                                    , $paper_info
                                    , $paper_affil
                                    , $paper_size
                                    , $crtr_unit);

        $paper_title_info = sprintf($paper_title_info_form, $paper_sort
                                                          , $paper_name
                                                          , $paper_dvs
                                                          , $paper_color);

        $title_info = sprintf($title_info_form, $sell_site
                                              , $paper_title_info
                                              , $paper_affil
                                              , $paper_size);

        $price = sprintf($price_form, $price_seqno
                                    , $basic_price
                                    , $sell_rate
                                    , $sell_aplc_price
                                    , $sell_price);

        $basisweight_arr[$paper_basisweight] = $paper_basisweight;
        $title_arr[$title] = $title;
        $title_info_arr[$title] = $title_info;
        $price_arr[$title][$paper_basisweight] = $price;

        $price_rs->MoveNext();
    }
}

if (count($basisweight_arr) === 0) {
    goto NOT_PRICE;
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$basisweight_arr = $util->sortDvsArr($basisweight_arr);

// 제목배열, 제목 정보배열, 가격배열 정렬
$title_arr_sort      = array();
$title_info_arr_sort = array();
$price_arr_sort      = array();

$i = 0;
foreach ($title_arr as $key => $val) {
    $title          = $val;
    $title_info     = $title_info_arr[$key];
    $price_arr_temp = $price_arr[$key];

    $title_arr_sort[$i]      = $title; // 종이정보
    $title_info_arr_sort[$i] = $title_info; // 종이정보 식별값
    $price_arr_sort[$i++]    = $price_arr_temp; // 가격정보
}

unset($title_arr);
unset($title_info_arr);
unset($price_arr);

$title_id_arr = array(
    0 => "paper_sell_site",
    1 => "paper_info",
    2 => "paper_affil",
    3 => "paper_size",
    4 => ""
);

$htmlLib->initInfo(count($title_id_arr), 2, "평량");

$thead = $htmlLib->getPriceTheadHtml($title_arr_sort,
                                     $title_id_arr,
                                     $title_info_arr_sort,
                                     true);
$tbody = $htmlLib->getPriceTbodyHtml(count($title_arr_sort),
                                     $price_arr_sort,
                                     $basisweight_arr,
                                     $tax_yn,
                                     true,
                                     false);
$colgroup = $htmlLib->getColgroupHtml(count($title_arr_sort));

echo $colgroup . $thead . $tbody;

$conn->Close();

exit;

NOT_PRICE:
    $conn->Close();
    echo "<tr><td>검색된 내용이 없습니다.</td></tr>";
?>
