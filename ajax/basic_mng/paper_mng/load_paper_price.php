<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PaperMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperMngDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$paper_name  = $fb->form("name");
$etprs_seqno = $fb->form("manu_seqno");
$brand_seqno = $fb->form("brand_seqno");
$paper_affil = $util->getUseAffilParam($conn, $dao, $fb);
$tax_yn      = $fb->form("tax_yn");

// 정보를 저장할 배열들
$basisweight_arr = array(); // 평량 배열
$title_arr       = array(); // 제목 배열
$title_info_arr  = array(); // 가격수정을 위한 제목 정보 배열
$price_arr       = array(); // 가격 배열

// 종이 정보 폼
$paper_info_form = "%s %s %s";
// 맵핑코드 검색용 값
$paper_title_info_form = "%s!%s!%s";

// 각 정보항목 폼
// 제조사|브랜드|종이분류|종이정보|계열|사이즈|기준수량
$title_form      = "%s|%s|%s|%s|%s|%s|%s";
// $title에 해당하는 식별값(제조사, 브랜드, 종이분류, 종이정보, 계열, 사이즈)
$title_info_form = "%s|%s|%s|%s|%s|%s|%s";
// 일련번호|기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s|%s";

$param["paper_name"]  = $paper_name;
$param["affil"]       = $paper_affil;
$param["brand_seqno"] = $brand_seqno;
$param["etprs_seqno"] = $etprs_seqno;

$price_rs = $dao->selectPrdcPaperPrice($conn, $param);

while ($price_rs && !$price_rs->EOF) {
    $paper_manu        = $price_rs->fields["manu_name"];
    $paper_brand       = $price_rs->fields["brand_name"];
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

    $price_seqno    = $price_rs->fields["price_seqno"];
    $basic_price    = $price_rs->fields["basic_price"];
    $pur_rate       = $price_rs->fields["pur_rate"];
    $pur_aplc_price = $price_rs->fields["pur_aplc_price"];
    $pur_price      = $price_rs->fields["pur_price"];

    $title = sprintf($title_form, $paper_manu
                                , $paper_brand
                                , $paper_sort
                                , $paper_info
                                , $paper_affil
                                , $paper_size
                                , $crtr_unit);

    $paper_title_info = sprintf($paper_title_info_form, $paper_name
                                                      , $paper_dvs
                                                      , $paper_color);

    $title_info = sprintf($title_info_form, $paper_manu
                                          , $paper_brand
                                          , $paper_sort
                                          , $paper_title_info
                                          , $paper_affil
                                          , $paper_size
                                          , $crtr_unit);

    $price = sprintf($price_form, $price_seqno
                                , $basic_price
                                , $pur_rate
                                , $pur_aplc_price
                                , $pur_price);

    $basisweight_arr[$paper_basisweight] = $paper_basisweight;
    $title_arr[$title] = $title;
    $title_info_arr[$title] = $title_info;
    $price_arr[$title][$paper_basisweight] = $price;

    $price_rs->MoveNext();
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
reset($title_arr);
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
    0 => "paper_manu",
    1 => "paper_brand",
    2 => "paper_sort",
    3 => "paper_info",
    4 => "paper_affil",
    5 => "paper_size",
    6 => "crtr_unit"
);

$htmlLib->initInfo(count($title_id_arr), 3, "평량");

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
$colgroup = $htmlLib->getColgroupHtml();

echo $colgroup . $thead . $tbody;

$conn->Close();
exit;

NOT_PRICE:
    $conn->Close();
    echo "<tr><td>검색된 내용이 없습니다.</td></tr>";
?>
