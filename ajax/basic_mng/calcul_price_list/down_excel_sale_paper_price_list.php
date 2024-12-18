<?
define("INC_PATH", $_SERVER["INC"]);
/*
 *
 * Copyright (c) 2015-2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/04 엄준현 생성
 * 2016/11/06 엄준현 수정(로직 안맞던 부분 수정)
 *=============================================================================
 *
 */
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');
include_once(INC_PATH . "/common_define/prdt_default_info_nimda.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CalculPriceListDAO();
$util = new ErpCommonUtil();
$excelLib = new PriceExcelUtil();

$sell_site     = $fb->form("sell_site");


if($sell_site == 1){
    $sell_site = "GP";
}else{
    $sell_site  = "DP";
}

$cate_sortcode = $fb->form("cate_sortcode");
$cate_name     = $fb->form("cate_name");
$paper_name        = $fb->form("paper_name");
$paper_dvs         = $fb->form("paper_dvs");
$paper_color       = $fb->form("paper_color");
$paper_basisweight = $fb->form("paper_basisweight");
$paper_affil       = $fb->form("paper_affil");
$stan_mpcode = $fb->form("stan_mpcode");
$stan_typ    = $fb->form("stan_typ");
$stan_name   = $fb->form("stan_name");
$pos_num     = $fb->form("pos_num");
$min_amt     = doubleval($fb->form("min_amt"));
$max_amt     = doubleval($fb->form("max_amt"));

$param = array();

//* 종이 검색어로 상품/카테고리 종이 맵핑코드 검색
$info_fld_arr = array(
    "name",
    "dvs",
    "color",
    "basisweight",
    "affil",
    "size",
    "crtr_unit"
);

$param["cate_sortcode"]     = $cate_sortcode;
$param["paper_name"]        = $paper_name;
$param["paper_dvs"]         = $paper_dvs;
$param["paper_color"]       = $paper_color;
$param["paper_basisweight"] = $paper_basisweight;
$param["paper_affil"]       = $paper_affil;

$paper_rs = $dao->selectCatePrdtPaper($conn, $param);

if ($paper_rs->EOF) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

$paper_total_arr = makeTotalInfoArr($paper_rs, $info_fld_arr);
$paper_cate_mpcode_arr = $paper_total_arr["cate_mpcode"];
$paper_prdt_mpcode_arr = $paper_total_arr["prdt_mpcode"];
$paper_info_arr   = $paper_total_arr["info"];

unset($paper_rs);
unset($paper_total_arr);
unset($param);
unset($info_fld_arr);

//* 상품 종이 맵핑코드로 각 종이 판매가격검색

// 정보를 저장할 배열들
$amt_arr   = PrdtDefaultInfo::AMT[$cate_sortcode]; // 수량 배열
$title_arr = array(); // 제목 배열
$price_arr = array(); // 가격 배열


$amt_arr = sliceAmtArr($amt_arr, $min_amt, $max_amt);

// 각 정보항목 폼
// 판매채널|카테고리|종이정보|계열|규격명|페이지|기준단위
$title_form = "%s|%s|%s!%s!%s!%s|%s|%s!%s|%s!%s|%s";
// 기본가격|요율|적용금액|할인가격
$price_form = "%s|%s|%s|%s|%s";

$param["sell_site"] = $sell_site;

// 판매채널 seqno로 판매채널명 검색
$site_name = $dao->selectSellSiteName($conn,
                                      array("seqno" => $sell_site));
// 카테고리 낱장여부, 수량단위 검색
$cate_info = $dao->selectCateInfo($conn,
                                  array("sortcode" => $cate_sortcode, "sell_site"=> $sell_site))->fields;
//$cate_info = $dao->selectCateInfo($conn,
  //                                array("sortcode" => $cate_sortcode))->fields;
$cate_flattyp_yn = $cate_info["flattyp_yn"];
$cate_amt_unit   = $cate_info["amt_unit"];

// 카테고리 페이지 수량
$cate_page_amt = null;
if ($cate_flattyp_yn === 'Y') {
    $cate_page_amt = PrdtDefaultInfo::PAGE_INFO["FLAT"];
} else {
    $cate_page_amt = PrdtDefaultInfo::PAGE_INFO[$cate_sortcode];
}

$mpcode_arr_count = count($paper_cate_mpcode_arr);
$amt_arr_count    = count($amt_arr);

$param["cate_sortcode"]    = $cate_sortcode;
$param["cate_stan_mpcode"] = $stan_mpcode;

$sortcode_t = substr($cate_sortcode, 0, 3);
$sortcode_m = substr($cate_sortcode, 0, 6);

for ($i = 0; $i < $mpcode_arr_count; $i++) {
    $paper_cate_mpcode = $paper_cate_mpcode_arr[$i];
    $paper_prdt_mpcode = $paper_prdt_mpcode_arr[$i];
    $paper_info        = $paper_info_arr[$i];

    $sheet_name = $paper_info["name"];

    // 상품종이 판매가격 검색
    $param["mpcode"] = $paper_prdt_mpcode;
    $sell_price = $dao->selectPrdtPaperPriceExcel($conn, $param);

    if ($sell_price->EOF) {
        $sell_price = 0;
    } else {
        $sell_price = $sell_price->fields["sell_price"];
        $sell_price = round(doubleval($sell_price) / 1.1);
    }

    // 수량_종이_할인 테이블 검색용
    $param["cate_paper_mpcode"] = $paper_cate_mpcode;

    foreach ($cate_page_amt as $page_typ => $page_arr) {
        $page_arr_count = count($page_arr);

        for ($j = 0; $j < $page_arr_count; $j++) {
            $page = $page_arr[$j];

            if ($page === 0) {
                continue;
            }

            $title = sprintf($title_form, $site_name
                                        , $cate_name
                                        , $paper_info["name"]
                                        , $paper_info["dvs"]
                                        , $paper_info["color"]
                                        , $paper_info["basisweight"]
                                        , $paper_affil
                                        , $stan_name
                                        , $stan_typ
                                        , $page_typ
                                        , $page . 'p'
                                        , $cate_amt_unit);

            $title_arr[$sheet_name][$title] = $title;

            for ($k = 0; $k < $amt_arr_count; $k++) {
                $amt = $amt_arr[$k];

                $param["amt"] = $amt;
                $sale_info = $dao->selectAmtPaperSale($conn, $param)->fields;

                $paper_sale_rate       = 0;
                $paper_sale_aplc_price = 0;
                $singleside_price = 0;

                if (!empty($sale_info)) {
                    $paper_sale_rate       = $sale_info["rate"];
                    $paper_sale_aplc_price = $sale_info["aplc_price"];
                    $singleside_price = $sale_info["singleside_price"];

                    $sale_rate = doubleval($paper_sale_rate) / 100.0;
                }

                if ($cate_amt_unit === "부" || $cate_amt_unit === "권") {
                    $temp = array("pos_num"   => $pos_num,
                                  "page_num"  => $page,
                                  "amt_unit"  => $cate_amt_unit,
                                  "crtr_unit" => $paper_info["crtr_unit"]);
                    if ($sortcode_m === "006001") {
                        $temp["amt"] = PrdtDefaultInfo::MST_GROUP * $amt;
                    } else {
                        $temp["amt"] = $amt;
                    }

                    $real_paper_amt = $util->getPaperRealPrintAmt($temp);
                } else {
                    if($cate_sortcode == "005002001") {
                        $real_paper_amt = $amt;
                    } else {
                        $real_paper_amt =
                            $util->calcPaperAmtByCrtrUnit($paper_info["crtr_unit"],
                                $cate_amt_unit,
                                $amt);
                    }
                }

                $temp_sell_price = $sell_price * $real_paper_amt;

                $sale_price = $temp_sell_price +
                              ($temp_sell_price * $sale_rate) +
                              $paper_sale_aplc_price;

                $price = sprintf($price_form, $temp_sell_price
                                            , $paper_sale_rate
                                            , $paper_sale_aplc_price
                                            , $singleside_price
                                            , $sale_price);

                $price_arr[$sheet_name][$title][$amt] = $price;
            }
        }
    }
}

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

//㈜굿프린팅|일반지 독판전단|아트지!-!백색!150g|국|A4|표지!2|R

$info_dvs_arr = array(1 => "판매채널",
                      2 => "카테고리",
                      3 => "종이",
                      4 => "계열",
                      5 => "사이즈",
                      6 => "페이지",
                      7 => "기준단위",
                      8 => "수량");

$price_dvs_arr = array(0 => "양면가격",
                       1 => "기본가격",
                       2 => "요율",
                       3 => "적용금액",
                       4 => "단면가격");

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
    echo "amt_paper_sale_price_list!" . $file_name;
} else {
    echo "FALSE";
}

$conn->Close();
exit;

/******************************************************************************
                            함수 영역
 *****************************************************************************/

/**
 * @brief 각 가격정보 검색결과를 가격검색 및 제목생성에
 * 사용할 수 있도록 가공하는 함수,
 * ErpCommonUtil 하고 방식이 틀려서 복붙해서 만듬
 *
 * @detail 반환되는 배열은 mpcode 배열과 info 배열이다
 * cate_mpcode 배열은 정보입력에 사용되고
 * prdt_mpcode 배열은 가격검색에 사용되고
 * info 배열은 엑셀 가격정보 셀 생성에 사용된다
 *
 * @param $rs           = 검색결과
 * @param $info_fld_arr = $rs에서 info로 생성할 필드명
 *
 * @return 가공된 배열
 */
function makeTotalInfoArr($rs, $info_fld_arr) {
    $ret = array(
        "cate_mpcode" => array(), // 카테고리 종이 맵핑코드 배열, 정보입력시 사용
        "prdt_mpcode" => array(), // 상품 종이 맵핑코드 배열, 가격검색시 사용
        "info"   => array()  // 종이 정보 배열, 제목 생성시 사용
    );

    $info_fld_arr_count = count($info_fld_arr);

    $i = 0;
    while (!$rs->EOF) {
        for ($j = 0; $j < $info_fld_arr_count; $j++) {
            $info_fld = $info_fld_arr[$j];
            $val = $rs->fields[$info_fld];

            if (empty($val)) {
                $val = '-';
            }

            $ret["info"][$i][$info_fld] = $val;
        }

        $ret["cate_mpcode"][$i]   = $rs->fields["cate_mpcode"];
        $ret["prdt_mpcode"][$i++] = $rs->fields["prdt_mpcode"];

        $rs->MoveNext();
    }

    return $ret;
}

function sliceAmtArr($arr, $min, $max) {
    $ret = array();
    $arr_count = count($arr);

    for ($i = 0; $i < $arr_count; $i++) {
        $amt = $arr[$i];

        if ($min > 0 && $amt < $min) {
            continue;
        }

        if ($max > 0 && $amt > $max) {
            continue;
        }

        $ret[] = $amt;
    }

    return $ret;
}
?>
