<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CalculPriceListDAO();
$util = new ErpCommonUtil();
$excelLib = new PriceExcelUtil();

$sell_site = $fb->form("sell_site");
$cate_sortcode  = $fb->form("cate_sortcode");
$print_name     = $fb->form("print_name");
$print_purp_dvs = $fb->form("print_purp_dvs");
$print_affil    = $fb->form("print_affil");
$print_affil    = $util->getUseAffil($print_affil);

$param = array();

//* 상품 출력 정보 맵핑코드 검색
$info_fld_arr = array(
    "cate_name",
    "print_name",
    "purp_dvs",
    "affil",
    "crtr_unit"
);

$param["cate_sortcode"]  = $cate_sortcode;
$param["print_name"]     = $print_name;
$param["print_affil"]    = $print_affil;
$param["print_purp_dvs"] = $print_purp_dvs;

$print_rs = $dao->selectPrdtPrintMpcode($conn, $param, true);
$print_total_arr = $util->makeTotalInfoArr($print_rs, $info_fld_arr);

$mpcode_arr = $print_total_arr["mpcode"];
$info_arr   = $print_total_arr["info"];

unset($paper_rs);
unset($paper_total_arr);
unset($param);
unset($info_fld_arr);

//* 맵핑코드로 가격검색
$mpcode_arr_count = count($mpcode_arr);

if ($mpcode_arr_count === 0) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

// 정보를 저장할 배열들
$amt_arr        = array(); // 평량 배열
$title_arr      = array(); // 제목 배열
$price_arr      = array(); // 가격 배열

// 각 정보항목 폼
// 맵핑코드|판매채널|카테고리 중분류|출력명|용도구분|계열|기준단위
$title_form = "%s|%s|%s|%s|%s|%s|%s";
// 기본가격|요율|적용금액|신규가격
$price_form = "%s|%s|%s|%s";

$param["sell_site"] = $sell_site;

// 판매채널 seqno로 판매채널명 검색
$site_name = $dao->selectSellSiteName($conn, array("seqno" => $sell_site));

for ($i = 0; $i < $mpcode_arr_count; $i++) {
    $mpcode = $mpcode_arr[$i];
    $info   = $info_arr[$i];

    $sheet_name = $info["print_name"];

    $title = sprintf($title_form, $mpcode
                                , $site_name
                                , $info["cate_name"]
                                , $info["print_name"]
                                , $info["purp_dvs"]
                                , $info["affil"]
                                , $info["crtr_unit"]);

    $title_arr[$sheet_name][$title] = $title;

    $param["mpcode"] = $mpcode;
    $price_rs = $dao->selectPrdtPrintPriceExcel($conn, $param);

    if ($price_rs->EOF === true) {
        $amt_arr[''] = '';
        $price_arr[$sheet_name][$title][''] = "|||";
    }

    while ($price_rs && !$price_rs->EOF) {
        $fields = $price_rs->fields;

        $amt             = $fields["amt"];
        $basic_price     = doubleval($fields["basic_price"]) / 1.1;
        $sell_rate       = $fields["sell_rate"];
        $sell_aplc_price = doubleval($fields["sell_aplc_price"]) / 1.1;
        $sell_price      = $fields["sell_price"];

        $price = sprintf($price_form, $basic_price
                                    , $sell_rate
                                    , $sell_aplc_price
                                    , $sell_price);

        $amt_arr[$amt] = $amt;
        $price_arr[$sheet_name][$title][$amt] = $price;

        $price_rs->MoveNext();
    }
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$amt_arr = $util->sortDvsArr($amt_arr);

// 제목배열, 제목 정보배열, 가격배열 정렬
$title_arr_sort = array();
$price_arr_sort = array();

foreach ($title_arr as $sheet_name => $title_arr_temp) {
    $price_arr_temp = $price_arr[$sheet_name];

	$i = 0;
    foreach ($title_arr_temp as $key => $val) {
        $title_arr_sort[$sheet_name][$i] = $val;
        $price_arr_sort[$sheet_name][$i++] = $price_arr_temp[$key];
    }
}

unset($title_arr);
unset($price_arr);

$info_dvs_arr = array(1 => "맵핑코드",
                      2 => "판매채널",
                      3 => "카테고리",
                      4 => "인쇄명",
                      5 => "용도구분",
                      6 => "계열",
                      7 => "기준단위",
                      8 => "수량");

$price_dvs_arr = array(0 => "판매가격",
                       1 => "기본가격",
                       2 => "요율",
                       3 => "적용금액");

$excelLib->initExcelFileWriteInfo((count($info_dvs_arr) - 1),
                                  count($price_dvs_arr),
                                  1);

foreach ($title_arr_sort as $sheet_name => $title_arr) {
    $excelLib->makePriceExcelSheet($sheet_name,
                                   $info_dvs_arr,
                                   $title_arr,
                                   $price_dvs_arr,
                                   $amt_arr,
                                   $price_arr_sort[$sheet_name]);
}

$file_name = uniqid();

$file_path = $excelLib->createExcelFile($file_name);

if (is_file($file_path)) {
    echo "print_sell_price_list!" . $file_name;
} else {
    echo "FALSE";
}

$conn->Close();
?>
