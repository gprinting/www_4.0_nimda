<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2017/03/15 엄준현 생성
 *============================================================================
 *
 */

include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new ErpCommonUtil();
$excelUtil = new PriceExcelUtil();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$sell_site     = $fb["sell_site"];
$mono_yn       = intval($fb["mono_yn"]);
$etprs_dvs     = $fb["etprs_dvs"];
$min_amt       = $fb["min_amt"];
$max_amt       = $fb["max_amt"];
$tax_yn        = $fb["tax_yn"];
$member_seqno  = $fb["member_seqno"];

$param = array();

//$conn->debug = 1;

//* 회원명, 사내닉네임 검색
$param["sell_site"] = $sell_site;
$param["member_seqno"] = $member_seqno;
$member_info = $dao->selectCndMember($conn, $param)->fields;
unset($param);

//* 해당 회원의 등급할인율 검색
$param["cate_sortcode"] = $cate_sortcode;
$param["grade"] = $member_info["grade"];
$grade_sale_rate = $dao->selectMemberGradeSale($conn, $param)->fields["rate"];
unset($param);

//* 판매채널에 해당하는 합판/계산형 가격테이블 검색
$table_name = "ply_price";

//* 카테고리에 해당하는 수량단위 검색
$amt_unit = $dao->selectCateAmtUnit($conn, $cate_sortcode);

//* 종이 정보가 넘어온 것이 있을 경우 종이 맵핑코드 검색
$info_fld_arr = array(
    "name",
    "dvs",
    "color",
    "basisweight"
);

// 합판가격은 계열 상관 없으므로 계산형일 때 만 추가
if ($mono_yn === 1) {
    $info_fld_arr[] = "affil";
}

$param["cate_sortcode"] = $cate_sortcode;
if (empty($fb["paper_name"]) === false) {
    // 종이 정보가 있을경우 파라미터 추가
    $param["name"]        = $fb["paper_name"];
    $param["dvs"]         = $fb["paper_dvs"];
    $param["color"]       = $fb["paper_color"];
    $param["basisweight"] = $fb["paper_basisweight"];
}
$paper_rs = $dao->selectPaperInfoAll($conn, $param);

$paper_total_arr = $util->makeTotalInfoArr($paper_rs, $info_fld_arr);
$paper_mpcode_arr = $paper_total_arr["mpcode"];
$paper_info_arr = $paper_total_arr["info"];

unset($paper_rs);
unset($paper_total_arr);
unset($param);
unset($info_fld_arr);

//* 인쇄도수가 선택된 경우 인쇄방식 맵핑코드 검색
if (empty($fb["bef_print_tmpt"]) === false) {
    $param["cate_sortcode"] = $cate_sortcode;
    $param["tmpt"]          = $fb["bef_print_tmpt"];

    $print_rs = $dao->selectCatePrintMpcode($conn, $param);

    $mpcode_arr = $util->rs2arr($print_rs, "mpcode");
    $mpcode_arr = $dao->parameterArrayEscape($conn, $mpcode_arr);
    $print_mpcode = $util->arr2delimStr($mpcode_arr);

    unset($mpcode_arr);
    unset($print_rs);
    unset($param);

    $param["print_mpcode"] = $print_mpcode;
}

$param["cate_sortcode"] = $cate_sortcode;
$param["stan_mpcode"] = $fb["output_size"];

// 종이 맵핑코드 수 만큼 가격 검색
$paper_mpcode_arr_count = count($paper_mpcode_arr);

// 정보를 저장할 배열들
$amt_arr   = array(); // 수량 배열
$title_arr = array(); // 제목 배열
$price_arr = array(); // 가격 배열

// 관련정보가 존재하지 않는경우 종료
if ($info_rs->EOF === true) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

// 회원명!사내닉네임|코팅명함|-!스노우지!-!백색!250g|86*52!투터치재단|표지!2p!|단면칼라4도|-|-|-|장
$title_form = "%s!%s|%s|%s!%s!%s!%s!%s|%s!%s|%s!%sp!%s|%s|-|-|-|%s";
// 판매가격|요율|적용금액|할인금액
$price_form = "%s|%s|%s|%s";

// 확정형 가격일 때는 종이 계열 관계없이 종류만 따짐
$dup_chk = array();

$param["min_amt"] = $min_amt;
$param["max_amt"] = $max_amt;

for ($i = 0; $i < $paper_mpcode_arr_count; $i++) {
    $paper_mpcode = $paper_mpcode_arr[$i];
    $paper_info   = $paper_info_arr[$i];

    $sheet_name = $paper_info["name"];

    if (empty($paper_info["affil"]) === true) {
        $paper_info["affil"] = '-';
    }

    $param["paper_mpcode"] = $paper_mpcode;

    $rs = $dao->selectCateMemberSalePriceList($conn,
                                              $table_name,
                                              $param,
                                              $tmpt_dvs);

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $amt_member_cate_sale_seqno =
            empty($fields["amt_member_cate_sale_seqno"]) ?
                '-1' : $fields["amt_member_cate_sale_seqno"];

        $new_price   = intval($fields["new_price"]);
        $new_price = $util->calcPrice($grade_sale_rate, $new_price);

        $amt         = $fields["amt"];
        $rate        = doubleval($fields["rate"]);
        $aplc_price  = intval($fields["aplc_price"]);

        // 할인금액 계산
        $sale_rate = $rate / 100.0;
        $sale_price = $new_price + ($new_price * $sale_rate) + $aplc_price;

        $cate_name = $fields["cate_name"];

        $page        = $fields["page"];
        $page_dvs    = $fields["page_dvs"];
        $page_detail = $fields["page_detail"];

        $bef_print_name     = $fields["bef_print_name"];
        $bef_add_print_name = $fields["bef_add_print_name"];
        $aft_print_name     = $fields["aft_print_name"];
        $aft_add_print_name = $fields["aft_add_print_name"];

        $stan_name = $fields["stan_name"];
        $stan_typ  = $fields["stan_typ"];

        if (empty($bef_add_print_name) === true) {
            $bef_add_print_name = '-';
        }
        if (empty($aft_print_name) === true) {
            $aft_print_name = '-';
        }
        if (empty($aft_add_print_name) === true) {
            $aft_add_print_name = '-';
        }

        $title = sprintf($title_form, $member_info["member_name"]
                                    , $member_info["office_nick"]
                                    , $cate_name
                                    , $paper_info["affil"]
                                    , $paper_info["name"]
                                    , $paper_info["dvs"]
                                    , $paper_info["color"]
                                    , $paper_info["basisweight"]
                                    , $stan_name
                                    , $stan_typ
                                    , $page_dvs
                                    , $page
                                    , $page_detail
                                    , $bef_print_name
                                    , $amt_unit);

        $price = sprintf($price_form, $new_price
                                    , $rate
                                    , $aplc_price
                                    , $sale_price);

        $amt_arr[$amt] = $amt;
        $title_arr[$sheet_name][$title] = $title;
        $price_arr[$sheet_name][$title][$amt] = $price;

        $rs->MoveNext();
    }
}

/*
print_r($amt_arr);
print_r($title_arr);
print_r($price_arr);
exit;
*/

if (count($amt_arr) === 0) {
    goto NOT_PRICE;
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

/*
print_r($title_arr_sort);
print_r($price_arr_sort);
exit;
*/

$info_dvs_arr = array(1  => "회원정보",
                      2  => "카테고리",
                      3  => "종이",
                      4  => "사이즈",
                      5  => "페이지",
                      6  => "전면도수",
                      7  => "전면추가도수",
                      8  => "후면도수",
                      9  => "후면추가도수",
                      10 => "수량단위",
                      11 => "수량");

$price_dvs_arr = array(0 => "할인가격",
                       1 => "판매가격",
                       2 => "요율 (%)",
                       3 => "적용금액 (\\)");

$excelUtil->initExcelFileWriteInfo((count($info_dvs_arr) - 1),
                                   count($price_dvs_arr),
                                   1);

foreach ($title_arr_sort as $sheet_name => $title_arr) {
    $excelUtil->makePriceExcelSheet($sheet_name,
                                    $info_dvs_arr,
                                    $title_arr,
                                    $price_dvs_arr,
                                    $amt_arr,
                                    $price_arr_sort[$sheet_name],
                                    $mono_yn);
}

$file_name = uniqid();

$file_path = $excelUtil->createExcelFile($file_name);

if (is_file($file_path)) {
    echo "member_sale_price_list!" . $file_name;
} else {
    echo "FALSE";
}

$conn->Close();
exit;

NOT_PRICE :
    $conn->Close();
    echo "NOT_INFO";
    exit;
?>
