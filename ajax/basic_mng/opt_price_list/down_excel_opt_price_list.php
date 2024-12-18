<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/OptPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OptPriceListDAO();
$util = new ErpCommonUtil();
$excelUtil = new PriceExcelUtil();

$cate_sortcode = $fb->form("cate_sortcode");
$sell_site = $fb->form("sell_site");

$opt_name = $fb->form("opt_name");
$depth1     = $fb->form("dep1_val");
$depth2     = $fb->form("dep2_val");
$depth3     = $fb->form("dep3_val");

$param = array();

//* 넘어온 정보에 해당하는 후공정 맵핑코드 검색
$info_fld_arr = array(
    "opt_name",
    "depth1",
    "depth2",
    "depth3"
);

$param["cate_sortcode"] = $cate_sortcode;
$param["opt_name"] = $opt_name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;

$opt_rs = $dao->selectCateOptInfo($conn, "SEQ", $param);
$opt_total_arr = $util->makeTotalInfoArr($opt_rs, $info_fld_arr);
$opt_mpcode_arr = $opt_total_arr["mpcode"];
$opt_info_arr   = $opt_total_arr["info"];

unset($opt_rs);
unset($opt_total_arr);
unset($param);
unset($info_fld_arr);

//* 후공정 맵핑코드만큼 가격 검색
$opt_mpcode_arr_count = count($opt_mpcode_arr);

if ($opt_mpcode_arr_count === 0) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

// 정보를 저장할 배열들
$amt_arr   = array(); // 수량 배열
$title_arr = array(); // 제목 배열
$price_arr = array(); // 가격 배열

// 각 정보항목 폼
// 맵핑코드|판매채널|카테고리명|옵션명|depth1|depth2|depth3
$title_form = "%s|%s|%s|%s|%s|%s|%s";
// 기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s";

$cate_name = $dao->selectCateName($conn, $cate_sortcode);
$site_name = $dao->selectSellSiteName($conn, array("seqno" => $sell_site));

for ($i = 0; $i < $opt_mpcode_arr_count; $i++) {
    $opt_mpcode = $opt_mpcode_arr[$i];
    $opt_info   = $opt_info_arr[$i];
	
	$sheet_name = $opt_info["opt_name"];

    $title = sprintf($title_form, $opt_mpcode
                                , $site_name
                                , $cate_name
                                , $opt_info["opt_name"]
                                , $opt_info["depth1"]
                                , $opt_info["depth2"]
                                , $opt_info["depth3"]);
								
	$title_arr[$sheet_name][$title] = $title;

    $param["opt_mpcode"] = $opt_mpcode;
    $param["sell_site"] = $sell_site;	

	$price_info_rs = $dao->selectCateOptPriceList($conn, $param);

    if ($price_info_rs->EOF === true) {
        $amt_arr[''] = '';
        $price_arr[$sheet_name][$title][''] = "|||";
    }

    while ($price_info_rs && !$price_info_rs->EOF) {
        $price_seqno = $price_info_rs->fields["price_seqno"];
        $amt         = $price_info_rs->fields["amt"];
        $basic_price = $price_info_rs->fields["basic_price"];
        $rate        = $price_info_rs->fields["sell_rate"];
        $aplc_price  = $price_info_rs->fields["sell_aplc_price"];
        $new_price   = $price_info_rs->fields["sell_price"];

        $price = sprintf($price_form, $basic_price
                                    , $rate
                                    , $aplc_price
                                    , $new_price);

        

        $amt_arr[$amt] = $amt;
        $price_arr[$sheet_name][$title][$amt] = $price;

        $price_info_rs->MoveNext();
    }
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$amt_arr = $util->sortDvsArr($amt_arr);

// 제목배열, 가격배열 정렬
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
                      4 => "옵션명",
                      5 => "Depth1",
                      6 => "Depth2",
                      7 => "Depth3",
                      8 => "수량");

$price_dvs_arr = array(0 => "판매가격",
                       1 => "기준가격",
                       2 => "요율",
                       3 => "적용금액");

$excelUtil->initExcelFileWriteInfo((count($info_dvs_arr) - 1),
                                  count($price_dvs_arr),
                                  1);

foreach ($title_arr_sort as $sheet_name => $title_arr) {
    $excelUtil->makePriceExcelSheet($sheet_name,
                                   $info_dvs_arr,
                                   $title_arr,
                                   $price_dvs_arr,
                                   $amt_arr,
                                   $price_arr_sort[$sheet_name]);
}

$file_path = $excelUtil->createExcelFile("opt_sell_price_list");

if (is_file($file_path)) {
        echo "opt_sell_price_list";
} else {
        echo "FALSE";
}

$conn->Close();
?>
