<?
/*
 *
 * Copyright (c) 2015-2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/04 엄준현 생성
 * 2016/11/06 엄준현 수정(개별수정 로직 안맞던 부분 수정)
 *=============================================================================
 *
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');
include_once(INC_PATH . "/common_define/prdt_default_info_nimda.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CalculPriceListDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$sell_site     = $fb->form("sell_site");

if($sell_site == 1){
    $sell_site = "GP";
}else{
    $sell_site  = "DP";
}

$tax_yn        = $fb->form("tax_yn");
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

//$conn->debug = 1;

$param = array();

//* 종이 검색어로 상품/카테고리 종이 맵핑코드 검색
$param["cate_sortcode"]     = $cate_sortcode;
$param["paper_name"]        = $paper_name;
$param["paper_dvs"]         = $paper_dvs;
$param["paper_color"]       = $paper_color;
$param["paper_basisweight"] = $paper_basisweight;
$param["paper_affil"]       = $paper_affil;

$paper_rs = $dao->selectCatePrdtPaper($conn, $param);

if ($paper_rs->EOF) {
    $err_msg = "선택한 사이즈 계열에 해당하는 종이가 없습니다.";
    goto NOT_PRICE;
}

unset($mpcode_rs);
unset($param);

//* 맵핑코드로 가격검색
$mpcode_arr_count = count($mpcode_arr);

// 정보를 저장할 배열들
$amt_arr        = PrdtDefaultInfo::AMT[$cate_sortcode]; // 수량 배열
$title_arr      = array(); // 제목 배열
$title_info_arr = array(); // 가격수정을 위한 제목 정보 배열
$price_arr      = array(); // 가격 배열

$amt_arr = sliceAmtArr($amt_arr, $min_amt, $max_amt);
// 각 정보항목 폼
// 카테고리|종이정보|계열|규격명|페이지|기준단위
$title_form = "%s|%s %s %s %s|%s|%s %s|%s %s|%s";
// $title에 해당하는 식별값(판매채널, 카테고리, 종이, 계열, 규격명, 페이지)
$title_info_form = "%s|%s|%s!%s!%s!%s|%s|%s!%s|%s!%s";
// 일련번호|기본가격|요율|적용금액|신규가격|단면가격
$price_form      = "%s|%s|%s|%s|%s|%s";

$param["sell_site"] = $sell_site;

// 카테고리 낱장여부, 수량단위 검색
$cate_info = $dao->selectCateInfo($conn,
                                  array("sortcode" => $cate_sortcode, "sell_site" => $sell_site))->fields;
//$cate_info = $dao->selectCateInfo($conn,
                                 // array("sortcode" => $cate_sortcode))->fields;
$cate_flattyp_yn = $cate_info["flattyp_yn"];
$cate_amt_unit   = $cate_info["amt_unit"];



// 카테고리 페이지 수량
$cate_page_amt = null;
if ($cate_flattyp_yn === 'Y') {
    $cate_page_amt = PrdtDefaultInfo::PAGE_INFO["FLAT"];
} else {
    $cate_page_amt = PrdtDefaultInfo::PAGE_INFO[$cate_sortcode];
}

$amt_arr_count = count($amt_arr);

$param["cate_sortcode"]    = $cate_sortcode;
$param["cate_stan_mpcode"] = $stan_mpcode;

$sortcode_t = substr($cate_sortcode, 0, 3);
$sortcode_m = substr($cate_sortcode, 0, 6);

while (!$paper_rs->EOF) {
    $fields = $paper_rs->fields;

    $cate_mpcode = $fields["cate_mpcode"];
    $prdt_mpcode = $fields["prdt_mpcode"];

    $param["mpcode"] = $prdt_mpcode;

    $sell_price = $dao->selectPrdtPaperPriceExcel($conn, $param);
    if ($price_rs->EOF === true) {
        continue;
    }

    if ($sell_price->EOF) {
        $sell_price = 0;
    } else {
        $sell_price = $sell_price->fields["sell_price"];
    }

    // 수량_종이_할인 테이블 검색용
    $param["cate_paper_mpcode"] = $cate_mpcode;

    foreach ($cate_page_amt as $page_typ => $page_arr) {
        $page_arr_count = count($page_arr);

        for ($j = 0; $j < $page_arr_count; $j++) {
            $page = $page_arr[$j];

            if ($page === 0) {
                continue;
            }

            $title = sprintf($title_form, $cate_name
                                        , $fields["name"]
                                        , $fields["dvs"]
                                        , $fields["color"]
                                        , $fields["basisweight"]
                                        , $paper_affil
                                        , $stan_name
                                        , $stan_typ
                                        , $page_typ
                                        , $page . 'p'
                                        , $cate_amt_unit);

            $title_info = sprintf($title_info_form, $sell_site
                                                  , $cate_sortcode
                                                  , $fields["name"]
                                                  , $fields["dvs"]
                                                  , $fields["color"]
                                                  , $fields["basisweight"]
                                                  , $paper_affil
                                                  , $stan_name
                                                  , $stan_typ
                                                  , $page_typ
                                                  , $page);

            $title_arr[$title] = $title;
            $title_info_arr[$title] = $title_info;

            for ($k = 0; $k < $amt_arr_count; $k++) {
                $amt = $amt_arr[$k];
                $param["amt"] = $amt;
                $sale_info = $dao->selectAmtPaperSale($conn, $param)->fields;

                $paper_sale_seqno      = -1;
                $paper_sale_rate       = 0;
                $paper_sale_aplc_price = 0;

                if (!empty($sale_info)) {
                    $paper_sale_seqno      = $sale_info["amt_paper_sale_seqno"];
                    $paper_sale_rate       = ceilValT($sale_info["rate"]);
                    $paper_sale_aplc_price = $sale_info["aplc_price"];
                    $singleside_price = $sale_info["singleside_price"];
                    $sale_rate = doubleval($paper_sale_rate) / 100.0;
                }

                if ($cate_amt_unit === "부" || $cate_amt_unit === "권") {
                    $temp = array("pos_num"   => $pos_num,
                                  "page_num"  => $page,
                                  "amt_unit"  => $cate_amt_unit,
                                  "crtr_unit" => $fields["crtr_unit"]);
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
                            $util->calcPaperAmtByCrtrUnit($fields["crtr_unit"],
                                $cate_amt_unit,
                                $amt);
                    }
                }

                $temp_sell_price = $sell_price * $real_paper_amt;

                $sale_price = $temp_sell_price +
                              ($temp_sell_price * $sale_rate) +
                              $paper_sale_aplc_price;

                $price = sprintf($price_form, $paper_sale_seqno
                                            , $temp_sell_price
                                            , $paper_sale_rate
                                            , $paper_sale_aplc_price
                                            , $sale_price
                                            , $singleside_price);
                //echo $k . " : " . $title . " " . $amt . " " . $price . " / ";
                $price_arr[$title][$amt] = $price;
            }
        }
    }

    $paper_rs->MoveNext();
}
//echo $price_arr["독판전단지|아트지 - 백색 120g|국|A1 투터치재단|표지 2p|장"][0.05];
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
    "sale_paper_sell_site",
    "sale_paper_cate_sortcode",
    "sale_paper_info",
    "sale_paper_affil",
    "sale_paper_stan_name",
    "sale_paper_page_info"
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

NOT_PRICE:
    $conn->Close();
    echo "<tr><td>" . $err_msg . "</td></tr>";
    exit;

/*****************************************************************************
                             함수 영역
 *****************************************************************************/
/**
 * @brief 소수점 4자리 이하 반올림
 *
 * @param $val = 계산할 값
 *
 * @return 계산된 값
 */
function ceilValT($val) {
    $val = floatval($val);

    $val = round($val * 1000) / 1000;

    return $val;
}

/**
 * @brief 최소최대값에 따라 수량배열 자름
 *
 * @param $arr = 수량배열
 * @param $min = 최소값
 * @param $max = 최대값
 *
 * @return 계산된 값
 */
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
