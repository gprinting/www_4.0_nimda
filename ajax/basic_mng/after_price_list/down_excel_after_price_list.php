<?
define("INC_PATH", $_SERVER["INC"]);
ini_set('memory_limit','-1');
ini_set('max_execution_time', 4000);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/AfterPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterPriceListDAO();
$util = new ErpCommonUtil();
$excelUtil = new PriceExcelUtil();

$cate_sortcode = $fb->form("cate_sortcode");
$sell_site = $fb->form("sell_site");

$after_name = $fb->form("after_name");
$depth1     = $fb->form("dep1_val");
$depth2     = $fb->form("dep2_val");
$depth3     = $fb->form("dep3_val");
$size       = $fb->form("size");

$param = array();

//* 넘어온 정보에 해당하는 후공정 맵핑코드 검색
$info_fld_arr = array(
    "basic_yn",
    "after_name",
    "depth1",
    "depth2",
    "depth3",
    "size",
    "crtr_unit"
);

$param["cate_sortcode"] = $cate_sortcode;
$param["after_name"] = $after_name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;
$param["size"] = $size;

$aft_rs = $dao->selectCateAftInfo($conn, "SEQ", $param);
$aft_total_arr = $util->makeTotalInfoArr($aft_rs, $info_fld_arr);
$aft_mpcode_arr = $aft_total_arr["mpcode"];
$aft_info_arr   = $aft_total_arr["info"];

unset($aft_rs);
unset($aft_total_arr);
unset($param);
unset($info_fld_arr);

//* 후공정 맵핑코드만큼 가격 검색
$aft_mpcode_arr_count = count($aft_mpcode_arr);

if ($aft_mpcode_arr_count === 0) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

// 정보를 저장할 배열들
$amt_arr   = array(); // 수량 배열
$title_arr = array(); // 제목 배열
$price_arr = array(); // 가격 배열

// 각 정보항목 폼
// 맵핑코드|판매채널|카테고리명|기본여부|후공정명|depth1|depth2|depth3|사이즈|기준수량
$title_form = "%s|%s|%s|%s|%s|%s|%s|%s|%s|%s";
// 기본가격|요율|적용금액|신규가격
$price_form = "%s|%s|%s|%s";

$cate_name = $dao->selectCateName($conn, $cate_sortcode);
$site_name = $dao->selectSellSiteName($conn, array("seqno" => $sell_site));

for ($i = 0; $i < $aft_mpcode_arr_count; $i++) {
    $aft_mpcode = $aft_mpcode_arr[$i];
    $aft_info   = $aft_info_arr[$i];
	
	$sheet_name = $aft_info["after_name"];

    $title = sprintf($title_form, $aft_mpcode
                                , $site_name
                                , $cate_name
                                , $aft_info["basic_yn"]
                                , $aft_info["after_name"]
                                , $aft_info["depth1"]
                                , $aft_info["depth2"]
                                , $aft_info["depth3"]
                                , $aft_info["size"]
                                , $aft_info["crtr_unit"]);
								
	$title_arr[$sheet_name][$title] = $title;
	
    $param["after_mpcode"] = $aft_mpcode;
    $param["sell_site"] = $sell_site;

    $price_rs = $dao->selectCateAftPriceList($conn, $param);

    if ($price_rs->EOF === true) {
        $amt_arr[''] = '';
        $price_arr[$sheet_name][$title][''] = "|||";
    }

    while ($price_rs && !$price_rs->EOF) {
        $fields = $price_rs->fields;

        $amt             = $fields["amt"];
        $basic_price     = doubleval($fields["basic_price"]);// / 1.1;
        $sell_rate       = $fields["sell_rate"];
        $sell_aplc_price = doubleval($fields["sell_aplc_price"]);// / 1.1;
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

$info_dvs_arr = array(1  => "맵핑코드",
                      2  => "판매채널",
                      3  => "카테고리",
                      4  => "기본후공정",
                      5  => "후공정명",
                      6  => "Depth1",
                      7  => "Depth2",
                      8  => "Depth3",
                      9  => "사이즈",
                      10 => "기준단위",
                      11 => "수량");

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

$file_name = uniqid();

$file_path = $excelUtil->createExcelFile($file_name);

if (is_file($file_path)) {
        echo "aft_sell_price_list!" . $file_name;
} else {
        echo "FALSE";
}

$conn->Close();
?>
