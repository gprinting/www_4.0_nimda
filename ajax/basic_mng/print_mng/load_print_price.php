<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PrintMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintMngDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$name        = $fb->form("name");
$etprs_seqno = $fb->form("manu_seqno");
$brand_seqno = $fb->form("brand_seqno");
$affil       = $util->getUseAffilParam($conn, $dao, $fb);
$tax_yn      = $fb->form("tax_yn");

// 정보를 저장할 배열들
$amt_arr        = array(); // 평량 배열
$title_arr      = array(); // 제목 배열
$title_info_arr = array(); // 가격수정을 위한 제목 정보 배열
$price_arr      = array(); // 가격 배열

// 각 정보항목 폼
// 제조사|브랜드|대분류|인쇄명|계열|사이즈|기준도수|기준수량
$title_form      = "%s|%s|%s|%s|%s|%s|%s|%s";
// $title에 해당하는 식별값(제조사, 브랜드, 대분류, 인쇄명, 계열, 사이즈, 기준도수, 기준수량)
$title_info_form = "%s|%s|%s|%s|%s|%s|%s|%s";
// 일련번호|기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s|%s";

$param["name"]        = $name;
$param["affil"]       = $affil;
$param["brand_seqno"] = $brand_seqno;
$param["etprs_seqno"] = $etprs_seqno;

$price_rs = $dao->selectPrdcPrintPrice($conn, $param);

while ($price_rs && !$price_rs->EOF) {
    $manu      = $price_rs->fields["manu_name"];
    $brand     = $price_rs->fields["brand_name"];
    $top       = $price_rs->fields["top"];
    $name      = $price_rs->fields["name"];
    $affil     = $price_rs->fields["affil"];
    $size      = $price_rs->fields["size"];
    $crtr_tmpt = $price_rs->fields["crtr_tmpt"];
    $crtr_unit = $price_rs->fields["crtr_unit"];

    $amt            = $price_rs->fields["amt"];
    $price_seqno    = $price_rs->fields["price_seqno"];
    $basic_price    = $price_rs->fields["basic_price"];
    $pur_rate       = $price_rs->fields["pur_rate"];
    $pur_aplc_price = $price_rs->fields["pur_aplc_price"];
    $pur_price      = $price_rs->fields["pur_price"];

    $title = sprintf($title_form, $manu
                                , $brand
                                , $top
                                , $name
                                , $affil
                                , $size
                                , $crtr_tmpt
                                , $crtr_unit);

    $title_info = sprintf($title_info_form, $manu
                                          , $brand
                                          , $top
                                          , $name
                                          , $affil
                                          , $size
                                          , $crtr_tmpt
                                          , $crtr_unit);

    $price = sprintf($price_form, $price_seqno
                                , $basic_price
                                , $pur_rate
                                , $pur_aplc_price
                                , $pur_price);

    $amt_arr[$amt] = $amt;
    $title_arr[$title] = $title;
    $title_info_arr[$title] = $title_info;
    $price_arr[$title][$amt] = $price;

    $price_rs->MoveNext();
}

if (count($amt_arr) === 0) {
    goto NOT_PRICE;
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$amt_arr = $util->sortDvsArr($amt_arr);

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
    0 => "print_manu",
    1 => "print_brand",
    2 => "print_top",
    3 => "print_name",
    4 => "print_affil",
    5 => "print_size",
    6 => "crtr_tmpt",
    7 => "crtr_unit"
);

$htmlLib->initInfo(count($title_id_arr), 3, "수량");

$thead = $htmlLib->getPriceTheadHtml($title_arr_sort,
                                     $title_id_arr,
                                     $title_info_arr_sort,
                                     true);
$tbody = $htmlLib->getPriceTbodyHtml(count($title_arr_sort),
                                     $price_arr_sort,
                                     $amt_arr,
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
