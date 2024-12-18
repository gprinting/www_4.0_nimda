<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OptMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OptMngDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$name   = $fb->form("name");
$depth1 = $fb->form("depth1");
$depth2 = $fb->form("depth2");
$depth3 = $fb->form("depth3");

// 정보를 저장할 배열들
$amt_arr        = array(); // 평량 배열
$title_arr      = array(); // 제목 배열
$title_info_arr = array(); // 가격수정을 위한 제목 정보 배열
$price_arr      = array(); // 가격 배열

// 각 정보항목 폼
// 옵션명|depth1|depth2|depth3|기준수량
$title_form      = "%s|%s|%s|%s|%s";
// $title에 해당하는 식별값
// (옵션명, depth1, depth2, dpeth3, 계열, 사이즈)
$title_info_form = "%s|%s|%s|%s|%s";
// 일련번호|기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s|%s";

$param["name"]   = $name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;

$price_rs = $dao->selectPrdcOptPrice($conn, $param);

while ($price_rs && !$price_rs->EOF) {
    $name      = $price_rs->fields["name"];
    $depth1    = $price_rs->fields["depth1"];
    $depth2    = $price_rs->fields["depth2"];
    $depth3    = $price_rs->fields["depth3"];
    $amt       = $price_rs->fields["amt"];
    $crtr_unit = $price_rs->fields["crtr_unit"];

    $price_seqno    = $price_rs->fields["price_seqno"];
    $basic_price    = $price_rs->fields["basic_price"];
    $pur_rate       = $price_rs->fields["pur_rate"];
    $pur_aplc_price = $price_rs->fields["pur_aplc_price"];
    $pur_price      = $price_rs->fields["pur_price"];

    $title = sprintf($title_form, $name
                                , $depth1
                                , $depth2
                                , $depth3
                                , $crtr_unit);

    $title_info = sprintf($title_info_form, $name
                                          , $depth1
                                          , $depth2
                                          , $depth3
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
    0 => "opt_name",
    1 => "opt_depth1",
    2 => "opt_depth2",
    3 => "opt_depth3",
    4 => "crtr_unit"
);

$htmlLib->initInfo(count($title_id_arr), 3, "수량");

$thead = $htmlLib->getPriceTheadHtml($title_arr_sort,
                                     $title_id_arr,
                                     $title_info_arr_sort,
                                     true);
$tbody = $htmlLib->getPriceTbodyHtml(count($title_arr_sort),
                                     $price_arr_sort,
                                     $amt_arr,
                                     true);
$colgroup = $htmlLib->getColgroupHtml();

echo $colgroup . $thead . $tbody;

$conn->Close();
exit;

NOT_PRICE:
    $conn->Close();
    echo "<tr><td>검색된 내용이 없습니다.</td></tr>";
?>
