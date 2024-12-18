<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/04/24 엄준현 수정(사이즈 유형 추가 및 로직 안맞던거 수정)
 *============================================================================
 *
 */

include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/ExcelLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new ErpCommonUtil();
$excelLib = new ExcelLib();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$sell_site     = $fb["sell_site"];
$mono_yn       = intval($fb["mono_yn"]);
$tmpt_dvs      = $fb["tmpt_dvs"];

//$conn->debug = 1;

$param = array();

//* 판매채널에 해당하는 합판/계산형 가격테이블 검색
$table_name  = "ply_price";

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
$bef_print_tmpt = $fb["bef_print_tmpt"];
$bef_print_mpcode = null;
if (empty($bef_print_tmpt) === false) {
    $param["cate_sortcode"] = $cate_sortcode;
    $param["tmpt"]          = $bef_print_tmpt;
    $param["side_dvs"] = "전면";

    $print_rs = $dao->selectCatePrintMpcode($conn, $param);

    $mpcode_arr = $util->rs2arr($print_rs, "mpcode");
    $mpcode_arr = $dao->parameterArrayEscape($conn, $mpcode_arr);
    $print_mpcode = $util->arr2delimStr($mpcode_arr);

    unset($mpcode_arr);
    unset($print_rs);

    $bef_print_mpcode = $print_mpcode;
}

$aft_print_tmpt = $fb["aft_print_tmpt"];
$aft_print_mpcode = null;
if (empty($aft_print_tmpt) === false) {
    $param["cate_sortcode"] = $cate_sortcode;
    $param["tmpt"]          = $aft_print_tmpt;
    $param["side_dvs"]      = "후면";

    $print_rs = $dao->selectCatePrintMpcode($conn, $param);

    $mpcode_arr = $util->rs2arr($print_rs, "mpcode");
    $mpcode_arr = $dao->parameterArrayEscape($conn, $mpcode_arr);
    $print_mpcode = $util->arr2delimStr($mpcode_arr);

    unset($mpcode_arr);
    unset($print_rs);

    $aft_print_mpcode = $print_mpcode;
}

$param["cate_sortcode"] = $cate_sortcode;
$param["stan_mpcode"] = $fb["output_size"];

// 종이 맵핑코드 수 만큼 가격 검색
$paper_mpcode_arr_count = count($paper_mpcode_arr);

// 정보를 저장할 배열들
$amt_arr   = array(); // 수량 배열
$title_arr = array(); // 제목 배열
$price_arr = array(); // 가격 배열

//* 선택한 카테고리에 해당하는 사이즈, 인쇄 맵핑코드, 이름을 가져온다
//* 관련정보는 있되 가격이 없는 부분이 있는 것을 생성하기 위함
$param["print_mpcode"] = $bef_print_mpcode;
$param["side_dvs"] = "전면";
$bef_info_rs = $dao->selectCatePriceInfoListExcel($conn, $param);
$param["side_dvs"] = "전면추가";
$bef_add_info_rs = $dao->selectCatePriceInfoListExcel($conn, $param);

$param["print_mpcode"] = $aft_print_mpcode;
$param["side_dvs"] = "후면";
$aft_info_rs = $dao->selectCatePriceInfoListExcel($conn, $param);
$param["side_dvs"] = "후면추가";
$aft_add_info_rs = $dao->selectCatePriceInfoListExcel($conn, $param);

// 관련정보가 존재하지 않는경우 종료
if ($bef_info_rs->EOF || $bef_add_info_rs->EOF
        $aft_info_rs->EOF || $aft_add_info_rs->EOF) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

// 카테고리명|종이정보|사이즈!사이즈유형|페이지 정보|전면인쇄도수|전면추가인쇄도수|후면인쇄도수|후면추가인쇄도수|기준수량
$title_form = "%s|%s!%s!%s!%s!%s|%s|%s!%sp!%s|%s|%s|%s|%s|%s";

unset($param["print_mpcode"]);
unset($param["side_dvs"]);
unset($param["tmpt"]);

// 모든 인쇄도수 조합 산출
while ($bef_info_rs && !$bef_info_rs->EOF) {
    $bef_print_tmpt   = $bef_info_rs->fields["print_tmpt"];
    $bef_print_mpcode = $bef_info_rs->fields["print_mpcode"];

    while ($bef_add_info_rs && !$bef_add_info_rs->EOF) {
        $bef_add_print_tmpt   = $bef_info_rs->fields["print_tmpt"];
        $bef_add_print_mpcode = $bef_info_rs->fields["print_mpcode"];

        while ($aft_info_rs && !$aft_info_rs->EOF) {
            $aft_print_tmpt = $aft_info_rs->fields["print_tmpt"];
            $aft_print_mpcode = $aft_info_rs->fields["print_mpcode"];

            while ($aft_add_info_rs && !$aft_add_info_rs->EOF) {
                $flattyp_yn = $aft_add_info_rs->fields["flattyp_yn"];
                $cate_name  = $aft_add_info_rs->fields["cate_name"];

                $stan_name   = $aft_add_info_rs->fields["stan_name"];
                $stan_typ    = $aft_add_info_rs->fields["stan_typ"];
                $stan_mpcode = $aft_add_info_rs->fields["stan_mpcode"];

                $aft_add_print_tmpt   = $aft_add_info_rs->fields["print_tmpt"];
                $aft_add_print_mpcode = $aft_add_info_rs->fields["print_mpcode"];


                //* 검색한 맵핑코드와 종이 맵핑코드를 결함해서 가격 검색
                for ($i = 0; $i < $paper_mpcode_arr_count; $i++) {
                    $paper_mpcode = $paper_mpcode_arr[$i];
                    $paper_info = $paper_info_arr[$i];

                    if (empty($paper_info["affil"]) === true) {
                        $paper_info["affil"] = '-';
                    }

                    $sheet_name = $paper_info["name"];
                    
                    $param["paper_mpcode"] = $paper_mpcode;
                    $param["stan_mpcode"]  = $stan_mpcode;
                    $param["bef_print_mpcode"] = $bef_print_mpcode;
                    $param["aft_print_mpcode"] = $aft_print_mpcode;
                    $param["bef_add_print_mpcode"] = $bef_add_print_mpcode;
                    $param["aft_add_print_mpcode"] = $aft_add_print_mpcode;

                    $price_rs = null;

                    if ($tmpt_dvs === '0') {
                        $price_rs = $dao->selectCatePriceListExcel($conn,
                                                                   $table_name,
                                                                   $param);
                    } else {
                        $param["affil"] = $paper_info["affil"];
                        $price_rs = $dao->selectCateCalcPriceListExcel($conn,
                                                                       $table_name,
                                                                       $param);
                    }

                    // 계산형 가격 테이블에서 값 없을경우 건너뜀
                    if ($price_rs->EOF === true) {
                        // 계산형 가격 테이블은 폼 생성 안하고 건너뜀
                        if ($mono_yn === 1) {
                            continue;
                        }

                        // 합판 가격 테이블에 가격이 존재하지 않을 경우
                        // 입력할 수 있도록 폼을 생성
                        $page_dvs = "내지";
                        $page     = '4';
                        if ($flattyp_yn === 'Y') {
                            $page_dvs = "표지";
                            $page     = '2';
                        }

                        $title = sprintf($title_form, $cate_name
                                                    , $paper_info["affil"]
                                                    , $paper_info["name"]
                                                    , $paper_info["dvs"]
                                                    , $paper_info["color"]
                                                    , $paper_info["basisweight"]
                                                    , $stan_name
                                                    , $stan_typ
                                                    , $page_dvs
                                                    , $page
                                                    , "페이지_상세"
                                                    , $bef_print_tmpt
                                                    , $bef_add_print_tmpt
                                                    , $aft_print_tmpt
                                                    , $aft_add_print_tmpt
                                                    , $amt_unit);
                        $title_arr[$sheet_name][$title] = $title;
                        $amt_arr[''] = '';
                        $price_arr[$title][''] = "|||";

                        continue;
                    }

                    $temp = array("paper_info"   => $paper_info,
                                  "sheet_name"   => $sheet_name,
                                  "bef_tmpt"     => $bef_print_tmpt,
                                  "bef_add_tmpt" => $bef_add_print_tmpt,
                                  "aft_tmpt"     => $aft_print_tmpt,
                                  "aft_add_tmpt" => $aft_add_print_tmpt,
                                  "amt_unit"     => $amt_unit);

                    if ($tmpt_dvs === '0') {
                        makePlyInfoArr($price_rs,
                                       $amt_arr,
                                       $title_arr,
                                       $price_arr,
                                       $temp);
                        );
                    } else {
                        makeCalcInfoArr($price_rs,
                                        $amt_arr,
                                        $title_arr,
                                        $price_arr,
                                        $temp);
                        );
                    }
                }

                $aft_add_info_rs->MoveNext();
            }

            $aft_add_info_rs->MoveFirst();
            $aft_info_rs->MoveNext();
        }

        $aft_info_rs->MoveFirst();
        $bef_add_info_rs->MoveNext();
    }

    $bef_add_info_rs->MoveFirst();
    $bef_info_rs->MoveNext();
}

unset($aft_info_rs);
unset($bef_info_rs);

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

$info_dvs_arr = array(1  => "카테고리",
                      2  => "종이",
                      3  => "사이즈",
                      4  => "페이지",
                      5  => "전면도수",
                      6  => "전면추가도수",
                      7  => "후면도수",
                      8  => "후면추가도수",
                      9  => "수량단위",
                      10 => "수량");

if ($mono_yn === 0) {
    $price_dvs_arr = array(0 => "판매가격",
                           1 => "기본금액",
                           2 => "요율",
                           3 => "적용가격");
} else {
    $price_dvs_arr = array(0 => "판매가격",
                           1 => "종이가격",
                           2 => "인쇄가격",
                           3 => "출력가격");
}

$excelLib->initExcelFileWriteInfo((count($info_dvs_arr) - 1),
                                  count($price_dvs_arr),
                                  1);

foreach ($title_arr_sort as $sheet_name => $title_arr) {
    $excelLib->makePriceExcelSheet($sheet_name,
                                   $info_dvs_arr,
                                   $title_arr,
                                   $price_dvs_arr,
                                   $amt_arr,
                                   $price_arr_sort[$sheet_name],
                                   $mono_yn);
}

$file_path = $excelLib->createExcelFile("prdt_price_list");

if (is_file($file_path)) {
    echo "prdt_price_list";
} else {
    echo "FALSE";
}

$conn->Close();
exit;

NOT_PRICE:
    $conn->Close();
    echo "NOT_PRICE";
    exit;

/******************************************************************************
                            함수 영역
 *****************************************************************************/

/**
 * @brief 가격정보 검색결과를 확정형 가격에 맞춰서 정보배열 생성
 *
 * @param &$rs = 검색결과
 * @param &$amt_arr   = 수량 배열
 * @param &$title_arr = 제목 배열
 * @param &$price_arr = 가격 배열
 * @param $param      = $title 생성용 파라미터, 
 *                      종이 정보 배열, 엑셀 시트명, 인쇄도수, 수량단위
 */
function makePlyInfoArr(&$rs,
                        &$amt_arr,
                        &$title_arr,
                        &$price_arr,
                        $param) {
    // 각 정보항목 폼
    // 카테고리명|종이정보|사이즈!사이즈유형|페이지 정보|전면인쇄도수|전면추가인쇄도수|후면인쇄도수|후면추가인쇄도수|기준수량
    $title_form = "%s|%s!%s!%s!%s!%s|%s!%s|%s!%sp!%s|%s|%s|%s|%s|%s";
    // 기본가격|요율|적용금액|신규가격
    $price_form = "%s|%s|%s|%s";

    $paper_info        = $param["paper_info"];
    $sheet_name        = $param["sheet_name"];
    $stan_name         = $param["stan_name"];
    $stan_typ          = $param["stan_typ"];
    $bef_print_tmpt    = $param["bef_tmpt"];
    $bef_addprint_tmpt = $param["bef_add_tmpt"];
    $aft_print_tmpt    = $param["aft_tmpt"];
    $aft_addprint_tmpt = $param["aft_add_tmpt"];
    $amt_unit          = $param["amt_unit"];

    while ($rs && !$rs->EOF) {
        $amt         = $rs->fields["amt"];
        $basic_price = $rs->fields["basic_price"];
        $rate        = $rs->fields["rate"];
        $aplc_price  = $rs->fields["aplc_price"];
        $new_price   = $rs->fields["new_price"];

        $page        = $rs->fields["page"];
        $page_dvs    = $rs->fields["page_dvs"];
        $page_detail = $rs->fields["page_detail"];

        if (empty($page_detail) === true) {
            $page_detail = '-';
        }

        $title = sprintf($title_form, $cate_name
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
                                    , $bef_print_tmpt
                                    , $bef_add_print_tmpt
                                    , $aft_print_tmpt
                                    , $aft_add_print_tmpt
                                    , $amt_unit);

        $price = sprintf($price_form, $basic_price
                                    , $rate
                                    , $aplc_price
                                    , $new_price);

        $title_arr[$sheet_name][$title] = $title;
        $amt_arr[$amt] = $amt;
        $price_arr[$sheet_name][$title][$amt] = $price;

        $rs->MoveNext();
    }
}

/**
 * @brief 가격정보 검색결과를 계산형 가격에 맞춰서 정보배열 생성
 *
 * @param &$rs = 검색결과
 * @param &$amt_arr   = 수량 배열
 * @param &$title_arr = 제목 배열
 * @param &$price_arr = 가격 배열
 * @param $param      = $title 생성용 파라미터, 
 *                      종이 정보 배열, 엑셀 시트명, 인쇄도수, 수량단위
 */
function makeCalcInfoArr(&$rs,
                         &$amt_arr,
                         &$title_arr,
                         &$price_arr,
                         $param) {
    // 각 정보항목 폼
    // 카테고리명|종이정보|사이즈!사이즈유형|페이지 정보|전면인쇄도수|전면추가인쇄도수|후면인쇄도수|후면추가인쇄도수|기준수량
    $title_form = "%s|%s!%s!%s!%s!%s|%s!%s|%s!%sp!%s|%s|%s|%s|%s|%s";
    // 종이가격|인쇄가격|출력금액|판매가격
    $price_form = "%s|%s|%s|%s";

    $paper_info        = $param["paper_info"];
    $sheet_name        = $param["sheet_name"];
    $stan_name         = $param["stan_name"];
    $stan_typ          = $param["stan_typ"];
    $bef_print_tmpt    = $param["bef_tmpt"];
    $bef_addprint_tmpt = $param["bef_add_tmpt"];
    $aft_print_tmpt    = $param["aft_tmpt"];
    $aft_addprint_tmpt = $param["aft_add_tmpt"];
    $amt_unit          = $param["amt_unit"];

    while ($rs && !$rs->EOF) {
        $amt          = $rs->fields["amt"];
        $paper_price  = $rs->fields["paper_price"];
        $print_price  = $rs->fields["print_price"];
        $output_price = $rs->fields["output_price"];
        $sum_price    = $rs->fields["sum_price"];

        $page        = $rs->fields["page"];
        $page_dvs    = $rs->fields["page_dvs"];
        $page_detail = $rs->fields["page_detail"];

        if (empty($page_detail) === true) {
            $page_detail = '-';
        }

        $title = sprintf($title_form, $cate_name
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
                                    , $bef_print_tmpt
                                    , $bef_add_print_tmpt
                                    , $aft_print_tmpt
                                    , $aft_add_print_tmpt
                                    , $amt_unit);

        $price = sprintf($price_form, $paper_price
                                    , $print_price
                                    , $output_price
                                    , $sum_price);

        $title_arr[$sheet_name][$title] = $title;
        $amt_arr[$amt] = $amt;
        $price_arr[$sheet_name][$title][$amt] = $price;

        $rs->MoveNext();
    }
}
?>
