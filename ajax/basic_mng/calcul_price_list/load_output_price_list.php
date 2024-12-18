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
$output_name        = $fb->form("output_name");
$output_board_dvs   = $fb->form("output_board_dvs");

//$conn->debug = 1; 

$param = array();

//* 상품 출력 정보 맵핑코드 검색
$param["output_name"]        = $output_name;
$param["output_board_dvs"]   = $output_board_dvs;

$mpcode_rs = $dao->selectPrdtOutputMpcode($conn, $param);

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
$amt_arr = array(); // 평량 배열
$title_arr       = array(); // 제목 배열
$title_info_arr  = array(); // 가격수정을 위한 제목 정보 배열
$price_arr       = array(); // 가격 배열

// 각 정보항목 폼
// 출력명|판구분|기준수량
$title_form      = "%s|%s|판";
// $title에 해당하는 식별값(판매채널, 맵핑코드)
$title_info_form = "%s|%s|-";
// 일련번호|기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s|%s";

$param["sell_site"] = $sell_site;

for ($i = 0; $i < $mpcode_arr_count; $i++) {
    $mpcode = $mpcode_arr[$i];

    $param["mpcode"] = $mpcode;

    $price_rs = $dao->selectPrdtOutputPrice($conn, $param);

    if ($price_rs->EOF === true) {
        continue;
    }

    while ($price_rs && !$price_rs->EOF) {
        $output_name      = $price_rs->fields["output_name"];
        $output_board_dvs = $price_rs->fields["output_board_dvs"];

        $price_seqno     = $price_rs->fields["price_seqno"];
        $amt             = $price_rs->fields["board_amt"];
        $basic_price     = $price_rs->fields["basic_price"];
        $sell_rate       = $price_rs->fields["sell_rate"];
        $sell_aplc_price = $price_rs->fields["sell_aplc_price"];
        $sell_price      = $price_rs->fields["sell_price"];

        $title = sprintf($title_form, $output_name
                                    , $output_board_dvs
                                    , $board_size);

        $title_info = sprintf($title_info_form, $sell_site
                                              , $mpcode);

        $price = sprintf($price_form, $price_seqno
                                    , $basic_price
                                    , $sell_rate
                                    , $sell_aplc_price
                                    , $sell_price);

        $amt_arr[$amt] = $amt;
        $title_arr[$title] = $title;
        $title_info_arr[$title] = $title_info;
        $price_arr[$title][$amt] = $price;

        $price_rs->MoveNext();
    }
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
    0 => "output_sell_site",
    1 => "output_mpcode",
    2 => "",
    3 => ""
);

$htmlLib->initInfo(count($title_id_arr), 2, "수량");

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
$colgroup = $htmlLib->getColgroupHtml(count($title_arr_sort));

echo $colgroup . $thead . $tbody;

$conn->Close();

exit;

NOT_PRICE :
    $conn->Close();
    echo "<tr><td>검색된 내용이 없습니다.</td></tr>";
?>
