<?
define("INC_PATH", $_SERVER["INC"]);
/*
 *
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/02/28 엄준현 생성
 *=============================================================================
 *
 */
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$sell_site     = $fb["sell_site"];
$mono_yn       = intval($fb["mono_yn"]);
$tmpt_dvs      = $fb["tmpt_dvs"];
$min_amt       = $fb["min_amt"];
$max_amt       = $fb["max_amt"];
$tax_yn        = $fb["tax_yn"];
$member_seqno  = $fb["member_seqno"];

//$conn->debug = 1;

$param = array();

//* 회원명, 사내닉네임 검색
$param["sell_site"] = $sell_site;
$param["member_seqno"] = $member_seqno;
$member_info = $dao->selectCndMember($conn, $param)->fields;
unset($param);

//* 해당 회원의 등급할인율 검색
$param["cate_sortcode"] = $cate_sortcode;
$param["grade"] = $member_info["grade"];
$grade_sale_rate = $dao->selectMemberGradeSale($conn, $param)->fields["rate"];
$grade_sale_rate = doubleval($grade_sale_rate) * -1;
unset($param);

//* 판매채널에 해당하는 계산형 가격테이블 검색
$etprs_dvs = "new";
if (intval($member_info["grade"]) < 7) {
    $etprs_dvs = "exist";
}
$table_name = "ply_price";

//* 카테고리에 해당하는 수량단위 검색
$amt_unit = $dao->selectCateAmtUnit($conn, $cate_sortcode);

//* 종이 정보가 넘어온 것이 있을 경우 종이 맵핑코드 검색
$paper_info_arr = null;
$param["cate_sortcode"] = $cate_sortcode;
if (empty($fb["paper_name"]) === false) {
    // 종이 정보가 있을경우 파라미터 추가
    $param["name"]        = $fb["paper_name"];
    $param["dvs"]         = $fb["paper_dvs"];
    $param["color"]       = $fb["paper_color"];
    $param["basisweight"] = $fb["paper_basisweight"];
}
$paper_rs = $dao->selectPaperInfoAll($conn, $param);

$paper_total_arr = makePaperTotalInfoArr($paper_rs);
$paper_mpcode_arr = $paper_total_arr["mpcode"];
$paper_info_arr   = $paper_total_arr["info"];

unset($paper_rs);
unset($paper_total_arr);
unset($param);

//* 인쇄도수가 선택된 경우 인쇄방식 맵핑코드 검색
$bef_print_tmpt = $fb["bef_print_tmpt"];
$bef_print_mpcode = null;
if (empty($bef_print_tmpt) === false) {
    $param["cate_sortcode"] = $cate_sortcode;
    $param["tmpt"]          = $bef_print_tmpt;

    // 전/후면 도수일 때만 처리
    if ($tmpt_dvs === '1') {
        $param["side_dvs"] = "전면";
    }

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

unset($param);

$param["cate_sortcode"] = $cate_sortcode;
$param["bef_print_mpcode"] = $bef_print_mpcode;
$param["aft_print_mpcode"] = $aft_print_mpcode;
$param["stan_mpcode"] = $fb["output_size"];

//* 종이 맵핑코드 수 만큼 가격 검색
$paper_mpcode_arr_count = count($paper_mpcode_arr);

if ($paper_mpcode_arr_count === 0) {
    goto NOT_PRICE;
}

// 정보를 저장할 배열들
$amt_arr        = array(); // 수량 배열
$title_arr      = array(); // 제목 배열
$title_info_arr = array(); // 제목 정보 배열
$price_arr      = array(); // 가격 배열

// 각 정보항목 폼
// 회원정보|카테고리명|종이정보|사이즈 사이즈유형|페이지구분 페이지수 페이지상세|전면인쇄도수|전면추가인쇄도수|후면인쇄도수|후면추가인쇄도수|기준수량
$title_form = "%s (%s)|%s|%s|%s %s|%s %sp %s|%s|%s|%s|%s|%s";
// $title에 해당하는 식별값들
// 회원 일련번호|카테고리 분류코드|종이 정보|규격 맵핑코드|페이지 정보|전면도수맵핑코드|-|후면도수맵핑코드|-|기준단위
$title_info_form = "%s|%s|%s|%s!%s|%s!%s!%s|%s|-|%s|-|%s";
// 일련번호|판매가격|요율|적용금액|할인금액
$price_form = "%s|%s|%s|%s|%s";

// 확정형 가격일 때는 종이 계열 관계없이 종류만 따짐
$dup_chk = array();

$param["min_amt"] = $min_amt;
$param["max_amt"] = $max_amt;
$param["member_seqno"] = $member_seqno;

for ($i = 0; $i < $paper_mpcode_arr_count; $i++) {
    $paper_mpcode = $paper_mpcode_arr[$i];
    $paper_info   = $paper_info_arr[$i];

    $param["paper_mpcode"] = $paper_mpcode;

    if ($dup_chk[$paper_info] === null) {
       $dup_chk[$paper_info] = true;
    } else {
        continue;
    }

    $rs = $dao->selectCateMemberSalePriceList($conn,
                                              $table_name,
                                              $param,
                                              $tmpt_dvs);

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $amt_member_cate_sale_seqno =
            empty($fields["amt_member_cate_sale_seqno"]) ?
                '-1' : $fields["amt_member_cate_sale_seqno"];

        $amt = $fields["amt"];

        $new_price        = intval($fields["new_price"]);
        $grade_sale_price = $util->calcPrice($grade_sale_rate, $new_price);
        $new_price       += $grade_sale_price;

        $rate       = doubleval($fields["rate"]);
        $aplc_price = intval($fields["aplc_price"]);

        // 할인금액 계산
        $sale_price = $new_price +
                      $util->calcPrice($rate, $new_price) +
                      $aplc_price;
        //$sale_rate = $rate / 100.0;
        //$sale_price = $new_price + ($new_price * $sale_rate) + $aplc_price;

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
                                    , $paper_info
                                    , $stan_name
                                    , $stan_typ
                                    , $page_dvs
                                    , $page
                                    , $page_detail
                                    , $bef_print_name
                                    , $bef_add_print_name
                                    , $aft_print_name
                                    , $aft_add_print_name
                                    , $amt_unit);

        $title_info = sprintf($title_info_form, $member_seqno
                                              , $cate_sortcode
                                              , $paper_mpcode
                                              , $stan_name
                                              , $stan_typ
                                              , $page_dvs
                                              , $page
                                              , $page_detail
                                              , $bef_print_name
                                              , $aft_print_name
                                              , $amt_unit);

        $price = sprintf($price_form, $amt_member_cate_sale_seqno
                                    , $new_price
                                    , $rate
                                    , $aplc_price
                                    , $sale_price);

        $amt_arr[$amt] = $amt;
        $title_arr[$title] = $title;
        $title_info_arr[$title] = $title_info;
        $price_arr[$title][$amt] = $price;

        $rs->MoveNext();
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
unset($price_arr);

$title_id_arr = array(
    "sale_member_seqno",
    "sale_cate_name",
    "sale_paper_info",
    "sale_cate_size",
    "sale_page_info",
    "sale_bef_print_tmpt",
    "sale_bef_print_add_tmpt",
    "sale_aft_print_tmpt",
    "sale_aft_print_add_tmpt",
    "sale_cate_amt_unit"
);

/*
print_r($title_arr_sort);
print_r($price_arr_sort);
exit;
*/

$type = 8;
$modi_flag = true;

$htmlLib->initInfo(count($title_id_arr), $type, "수량");

$colgroup = $htmlLib->getColgroupHtml();
$thead = $htmlLib->getPriceTheadHtml($title_arr_sort,
                                     $title_id_arr,
                                     $title_info_arr_sort,
                                     $modi_flag);
$tbody = $htmlLib->getPriceTbodyHtml(count($title_arr_sort),
                                     $price_arr_sort,
                                     $amt_arr,
                                     $tax_yn,
                                     $modi_flag);

echo $colgroup . $thead . $tbody;

$conn->Close();
exit;

NOT_PRICE :
    $conn->Close();
    echo "<tr><td>검색된 내용이 없습니다.</td></tr>";
    exit;

/******************************************************************************
                            함수 영역
 *****************************************************************************/

/**
 * @brief 종이 정보 검색결과를 가격검색 및 제목생성에
 * 사용할 수 있도록 가공하는 함수
 *
 * @param $rs = 검색결과
 *
 * @return 가공된 배열
 */
function makePaperTotalInfoArr($rs) {
    $ret = array(
        "mpcode" => array(), // 종이 맵핑코드 배열, 가격검색시 사용
        "info"   => array()  // 종이 정보 배열, 제목 생성시 사용
    );

    $info_form = "%s %s %s %s";

    $i = 0;
    while ($rs && !$rs->EOF) {
        $mpcode      = $rs->fields["mpcode"];

        $name        = $rs->fields["name"];
        $dvs         = $rs->fields["dvs"];
        $color       = $rs->fields["color"];
        $basisweight = $rs->fields["basisweight"];
        $affil       = $rs->fields["affil"];

        $ret["mpcode"][$i] = $mpcode;
        $ret["info"][$i++] = sprintf($info_form, $name
                                               , $dvs
                                               , $color
                                               , $basisweight);
        $rs->MoveNext();
    }

    return $ret;
}
?>
