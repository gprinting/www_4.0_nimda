<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 주문정보 리스트 확장시 상세정보 html 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/10 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . '/common_define/prdt_default_info.inc');
include_once(INC_PATH . '/common_lib/CommonUtil.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();

$fb = $fb->getForm();

$order_num = $fb["order_num"];

$param = array();
$param["order_num"] = $order_num;

//$conn->debug = 1;
// 주문공통일련번호, 카테고리일련번호, 수량, 페이지수량
$base_info = $dao->selectOrderInfoBase($conn, $param);
// 카테고리명(상품명), 낱장여부
$cate_info = $dao->selectData($conn, array(
    "col"   => "cate_name, flattyp_yn",
    "table" => "cate",
    "where" => array(
        "sortcode" => $base_info["cate_sortcode"]
    )
))->fields;
$base_info["cate_name"]  = $cate_info["cate_name"];
$base_info["flattyp_yn"] = $cate_info["flattyp_yn"];
// 낱장여부에 따른 각 주문 상세정보
$detail_info = null;
// 규격명, 도수명, 종이명, 총도수
$param["order_common_seqno"] = $base_info["order_common_seqno"];
if ($cate_info["flattyp_yn"] === 'Y') {
    $detail_info = $dao->selectOrderInfoDetailSheet($conn, $param);
} else {
    $detail_info = $dao->selectOrderInfoDetailBrochure($conn, $param);
}

unset($param);
$param["conn"] = &$conn;
$param["dao"]  = &$dao;
$param["base_info"]   = $base_info;
$param["detail_info"] = $detail_info;

$ret  = "<td colspan=\"8\" style=\"border:1px solid #ccc;padding:30px;\">";
$ret .= makeOrderInfoDetailHtml($param);
$ret .= "</td>";

echo $ret;

$conn->Close();
exit;

/******************************************************************************
 * 함수영역
 ******************************************************************************/

/**
 * @brief 주문정보 상세정보 html 생성
 *
 * @param $param = 검색결과들
 *
 * @return html
 */
function makeOrderInfoDetailHtml($param) {
    $ret = '';

    $base_info = $param["base_info"];
    $detail_info = $param["detail_info"];
    $flattyp_yn = $base_info["flattyp_yn"];

    $temp = array();
    $temp["cate_name"] = $base_info["cate_name"];
    $temp["amt"] = $base_info["amt"] . $base_info["amt_unit_dvs"];

    if ($flattyp_yn === 'Y') {
        $temp["page"]       = $detail_info->fields["page_amt"];
        $temp["paper_name"] = $detail_info->fields["paper_name"];
        $temp["stan_name"]  = $detail_info->fields["stan_name"];
        $temp["tmpt_name"]  = $detail_info->fields["tot_tmpt"];
    }
    $ret .= makeBaseInfoHtml($temp);
    $ret .= makeDetailInfoHtml($param);
    $ret .= makeOptionInfoHtml($param);
    $ret .= makeSumInfoHtml($base_info);

    return $ret;
}

/**
 * @brief 기본정보 html 생성
 *
 * @detail 상품명, 종이, 규격, 인쇄도수, 페이지, 수량
 *
 * @param $param = 정보값
 *
 * @return html
 */
function makeBaseInfoHtml($param) {
    $html  = "<table class=\"information\">";
    $html .=     "<colgroup>";
    $html .=     "<col style=\"50px;\">";
    $html .=     "<col style=\"70px;\">";
    $html .=     "<col style=\"100px;\">";
    $html .=     "<col style=\"80px;\">";
    $html .=     "<col style=\"50px;\">";
    $html .=     "<col style=\"50px;\">";
    $html .=     "<col style=\"50px;\">";
    $html .=     "<col style=\"80px;\">";
    $html .=     "<col style=\"60px;\">";
    $html .=     "</colgroup>";
    $html .=     "<tbody class=\"center\">";
    $html .=         "<tr class=\"tr_top\">";
    $html .=             "<th rowspan=\"9\" style=\"vertical-align:middle;background:#ddd;border:1px solid #bababa;\">기본정보</th>";
    $html .=             "<th>구분</th>";
    $html .=             "<th>내용</th>";
    $html .=             "<th>단가</th>";
    $html .=             "<th>도수</th>";
    $html .=             "<th>대수</th>";
    $html .=             "<th>연수</th>";
    $html .=             "<th>금액</th>";
    $html .=             "<th>비고</th>";
    $html .=         "</tr>";
    if (!empty($param["cate_name"])) {
        $html .=         "<tr>";
        $html .=             "<td>상품명</td>";
        $html .=             "<td>" . $param["cate_name"] . "</td>"; // #1 cate_name
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=         "</tr>";
    }
    if (!empty($param["paper_name"])) {
        $html .=         "<tr>";
        $html .=             "<td>종이</td>";
        $html .=             "<td>" . $param["paper_name"] . "</td>"; // #2 paper_name
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=         "</tr>";
    }
    if (!empty($param["stan_name"])) {
        $html .=         "<tr>";
        $html .=             "<td>규격</td>";
        $html .=             "<td>" . $param["stan_name"] . "</td>"; // #3 stan_name
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=         "</tr>";
    }
    if (!empty($param["tmpt_name"])) {
        $html .=         "<tr>";
        $html .=             "<td>인쇄도수</td>";
        $html .=             "<td>" . $param["tmpt_name"] . "</td>"; // #4 tmpt_name
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=         "</tr>";
    }
    if (!empty($param["page"])) {
        $html .=         "<tr>";
        $html .=             "<td>페이지</td>";
        $html .=             "<td>" . $param["page"] . "</td>"; // #5 page
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=         "</tr>";
    }
    if (!empty($param["amt"])) {
        $html .=         "<tr>";
        $html .=             "<td>수량</td>";
        $html .=             "<td>" . $param["amt"] . $param["amt_unit_dvs"] . "</td>"; // #6 amt
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=             "<td></td>";
        $html .=         "</tr>";
    }
    $html .=     "</tbody>";
    $html .= "</table>";

    return $html;
}

/**
 * @brief 상세정보 html 생성
 *
 * @detail 표지/내지의 종이비, 출력비, 인쇄비 및 후공정
 *
 * @param $param = 정보
 *
 * @return html
 */
function makeDetailInfoHtml($param) {
    $util = new CommonUtil();

    $conn = $param["conn"];
    $dao  = $param["dao"];
    $base_info   = $param["base_info"];
    $detail_info = $param["detail_info"];

    $mono_yn    = $base_info["mono_yn"];
    $flattyp_yn = $base_info["flattyp_yn"];

    $calcUtil = null;
    $pos_num_arr =
        PrdtDefaultInfo::POSITION_NUMBER[$base_info["cate_sortcode"]];
    if ($mono_yn === 'Y') {
        include_once(INC_PATH . "/common_lib/CalcPriceUtil.inc");
    }

    $detail_form  = "<table class=\"information\">";
    $detail_form .=     "<colgroup>";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"70px;\">";
    $detail_form .=     "<col style=\"100px;\">";
    $detail_form .=     "<col style=\"80px;\">";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"80px;\">";
    $detail_form .=     "<col style=\"60px;\">";
    $detail_form .=     "</colgroup>";
    $detail_form .=     "<tbody class=\"center\">";
    $detail_form .=         "<tr class=\"tr_top\">";
    $detail_form .=             "<th rowspan=\"5\" style=\"vertical-align:middle;background:#ddd;border:1px solid #bababa;\">%s</th>"; // #0
    $detail_form .=             "<th>구분</th>";
    $detail_form .=             "<th>내용</th>";
    $detail_form .=             "<th>단가</th>";
    $detail_form .=             "<th>도수</th>";
    $detail_form .=             "<th>대수</th>";
    $detail_form .=             "<th>연수</th>";
    $detail_form .=             "<th>금액</th>";
    $detail_form .=             "<th>비고</th>";
    $detail_form .=         "</tr>";
    $detail_form .=         "<tr>";
    $detail_form .=         "<td>종이(지대)</td>";
    $detail_form .=             "<td>%s</td>"; // #1
    $detail_form .=             "<td>%s</td>"; // #2
    $detail_form .=             "<td></td>";
    $detail_form .=             "<td></td>";
    $detail_form .=             "<td>%s</td>"; // #3
    $detail_form .=             "<td>%s</td>"; // #4
    $detail_form .=             "<td></td>";
    $detail_form .=         "</tr>";
    $detail_form .=         "<tr>";
    $detail_form .=             "<td>출력비</td>";
    $detail_form .=             "<td>%s</td>"; // #5
    $detail_form .=             "<td>%s</td>"; // #6
    $detail_form .=             "<td>%s</td>"; // #7
    $detail_form .=             "<td>%s</td>"; // #8
    $detail_form .=             "<td>%s</td>"; // #9
    $detail_form .=             "<td>%s</td>"; // #10
    $detail_form .=             "<td></td>";
    $detail_form .=         "</tr>";
    $detail_form .=         "<tr>";
    $detail_form .=         "<td>인쇄비</td>";
    $detail_form .=             "<td>%s</td>"; // #11
    $detail_form .=             "<td>%s</td>"; // #12
    $detail_form .=             "<td>%s</td>"; // #13
    $detail_form .=             "<td>%s</td>"; // #14
    $detail_form .=             "<td>%s</td>"; // #15
    $detail_form .=             "<td>%s</td>"; // #16
    $detail_form .=             "<td></td>";
    $detail_form .=         "</tr>";
    $detail_form .=     "</tbody>";
    $detail_form .= "</table>";

    $detail_after_form .= "<table class=\"information\">";
    $detail_after_form .=     "<colgroup>";
    $detail_after_form .=     "<col style=\"50px;\">";
    $detail_after_form .=     "<col style=\"70px;\">";
    $detail_after_form .=     "<col style=\"100px;\">";
    $detail_after_form .=     "<col style=\"80px;\">";
    $detail_after_form .=     "<col style=\"50px;\">";
    $detail_after_form .=     "<col style=\"50px;\">";
    $detail_after_form .=     "<col style=\"50px;\">";
    $detail_after_form .=     "<col style=\"80px;\">";
    $detail_after_form .=     "<col style=\"60px;\">";
    $detail_after_form .=     "</colgroup>";
    $detail_after_form .=     "<tbody class=\"center\">";
    $detail_after_form .=       "<tr class=\"tr_top\">";
    $detail_after_form .=           "<th rowspan=\"%s\" style=\"vertical-align:middle;background:#ddd;border:1px solid #bababa;\">%s<br>후공정</th>";
    $detail_after_form .=           "<th>구분</th>";
    $detail_after_form .=           "<th>내용</th>";
    $detail_after_form .=           "<th>단가</th>";
    $detail_after_form .=           "<th>도수</th>";
    $detail_after_form .=           "<th>대수</th>";
    $detail_after_form .=           "<th>연수</th>";
    $detail_after_form .=           "<th>금액</th>";
    $detail_after_form .=           "<th>비고</th>";
    $detail_after_form .=       "</tr>";
    $detail_after_form .=       "%s";
    $detail_after_form .=     "</tbody>";
    $detail_after_form .= "</table>";

    $detail_after_tr_form .=     "<tr>";
    $detail_after_tr_form .=         "<td>%s</td>";
    $detail_after_tr_form .=         "<td></td>";
    $detail_after_tr_form .=         "<td></td>";
    $detail_after_tr_form .=         "<td></td>";
    $detail_after_tr_form .=         "<td></td>";
    $detail_after_tr_form .=         "<td></td>";
    $detail_after_tr_form .=         "<td>%s</td>";
    $detail_after_tr_form .=         "<td></td>";
    $detail_after_tr_form .=     "</tr>";

    $ret = '';
    $temp = array();
    while ($detail_info && !$detail_info->EOF) {
        $fields = $detail_info->fields;
        // 종이 연수
        $paper_r_cnt = doubleval($fields["amt"]);
        if (!empty($pos_num_arr) && $flattyp_yn === 'Y') {
            $paper_r_cnt =
                $util->calcPaperAmtByCrtrUnit($fields["amt_unit_dvs"],
                                              'R',
                                              $fields["amt"]);
        } else if(!empty($pos_num_arr)) {
            $paper_r_cnt =
                $util->getPaperRealPrintAmt(
                    array(
                        "amt"       => $fields["amt"],
                        "pos_num"   => $pos_num_arr[$fields["stan_name"]],
                        "page_num"  => $fields["page_amt"],
                        "amt_unit"  => $fields["amt_unit_dvs"],
                        "crtr_unit" => 'R'
                    )
                );
        }
        $paper_r_cnt = number_format($paper_r_cnt);
        // 기계 대수
        $machine_count = 1;
        if ($mono_yn === 'Y') {
            $machine_count_arr =
                CalcPriceUtil::getMachineCount($fields["page_amt"],
                                               $pos_num_arr[$fields["stan_name"]]);
            $machine_count =
                $machine_count_arr["hong"] +
                CalcPriceUtil::getDonMachineCount($machine_count_arr["hong"]);

            $machine_count = number_format($machine_count);
        }

        // 종이, 출력, 인쇄 가격
        $paper_price  = number_format($fields["paper_price"]);
        $output_price = number_format($fields["output_price"]);
        $print_price  = number_format($fields["print_price"]);
        $paper_sum_price  = number_format($fields["paper_sum_price"]);
        $output_sum_price = number_format($fields["output_sum_price"]);
        $print_sum_price  = number_format($fields["print_sum_price"]);

        $detail_html = sprintf($detail_form, $fields["typ"] // #0
                                           , $fields["paper_name"]// #1
                                           , $paper_price // #2
                                           , $paper_r_cnt . $fields["amt_unit_dvs"] // #3
                                           , $paper_sum_price // #4

                                           , $fields["stan_name"] // #5
                                           , $output_price // #6
                                           , $fields["tot_tmpt"] // #7
                                           , $machine_count // #8
                                           , $paper_r_cnt . $fields["amt_unit_dvs"] // #9
                                           , $output_sum_price // #10

                                           , $fields["print_tmpt_name"] // #11
                                           , $print_price // #12
                                           , $fields["tot_tmpt"] // #13
                                           , $machine_count // #14
                                           , $paper_r_cnt . $fields["amt_unit_dvs"] // #15
                                           , $print_sum_price); // #16
        $ret .= $detail_html;

        // 후공정 검색
        $temp["order_detail_dvs_num"] = $fields["order_detail_dvs_num"];
        $after_info = $dao->selectOrderInfoAfter($conn, $temp);
        $after_count = $after_info->RecordCount();

        $after_html = '';
        while ($after_info && !$after_info->EOF) {
            $after_name  = $after_info->fields["after_name"];
            $after_price = $after_info->fields["price"];

            $after_html .=
                sprintf($detail_after_tr_form, $after_name
                                             , number_format($after_price));

            $after_info->MoveNext();
        }
        $ret .= sprintf($detail_after_form, $after_count + 2
                                          , $fields["typ"]
                                          , $after_html);

        $detail_info->MoveNext();
    }

    return $ret;
}

/**
 * @brief 합계 html 생성
 *
 * @detail 정상가, 총계
 *
 * @param $param = 기본_정보
 *
 * @return html
 */
function makeSumInfoHtml($param) {
    $sell_price = doubleval($param["sell_price"]) +
                  doubleval($param["add_after_price"]) +
                  doubleval($param["add_opt_price"]);
    $sale_price = intval($param["grade_sale_price"]) +
                  intval($param["member_sale_price"]) +
                  //intval($param["cp_price"]) +
                  //intval($param["event_price"]) +
                  intval($param["use_point_price"]);

    $html  = "<div class=\"sum\">";
    $html .=     "<div class=\"right_sum\">";
    $html .=         "<div class=\"right_panel\">";
    $html .=             "<ul class=\"ul_order_info_sum\">";
    $html .=                 "<li>공급가 <input type=\"text\" value=\"%s\" class=\"input_order_info_sum\">&nbsp;+&nbsp;</li>";
    $html .=                 "<li>할인가 <input type=\"text\" value=\"%s\" class=\"input_order_info_sum\" style=\"color:red;\"></li>";
    $html .=                 "<li><span>&nbsp;= 총계 (VAT포함) </span><input type=\"text\" value=\"%s\" class=\"input_order_info_sum\" style=\"font-weight:700\"></li>";
    $html .=             "</ul>";
    $html .=         "</div>";
    $html .=     "</div>";
    $html .= "</div>";

    return sprintf($html, number_format($sell_price)
                        , number_format($sale_price)
                        , number_format($sell_price + $sale_price));
}

/**
 * @brief 옵션 html 생성
 *
 * @param $param = 기본_정보
 *
 * @return html
 */
function makeOptionInfoHtml($param) {
    $conn = $param["conn"];
    $dao  = $param["dao"];

    $order_common_seqno = $param["base_info"]["order_common_seqno"];

    $detail_form .= "<table class=\"information\">";
    $detail_form .=     "<colgroup>";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"70px;\">";
    $detail_form .=     "<col style=\"100px;\">";
    $detail_form .=     "<col style=\"80px;\">";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"50px;\">";
    $detail_form .=     "<col style=\"80px;\">";
    $detail_form .=     "<col style=\"60px;\">";
    $detail_form .=     "</colgroup>";
    $detail_form .=     "<tbody class=\"center\">";
    $detail_form .=         "<tr class=\"tr_top\">";
    $detail_form .=             "<th rowspan=\"%s\" style=\"vertical-align:middle;background:#ddd;border:1px solid #bababa;\">옵션</th>";
    $detail_form .=             "<th>구분</th>";
    $detail_form .=             "<th>내용</th>";
    $detail_form .=             "<th>단가</th>";
    $detail_form .=             "<th>도수</th>";
    $detail_form .=             "<th>대수</th>";
    $detail_form .=             "<th>연수</th>";
    $detail_form .=             "<th>금액</th>";
    $detail_form .=             "<th>비고</th>";
    $detail_form .=         "</tr>";
    $detail_form .=         "%s";
    $detail_form .=     "</tbody>";
    $detail_form .= "</table>";

    $detail_tr_form .=     "<tr>";
    $detail_tr_form .=         "<td>%s</td>";
    $detail_tr_form .=         "<td></td>";
    $detail_tr_form .=         "<td></td>";
    $detail_tr_form .=         "<td></td>";
    $detail_tr_form .=         "<td></td>";
    $detail_tr_form .=         "<td></td>";
    $detail_tr_form .=         "<td>%s</td>";
    $detail_tr_form .=         "<td></td>";
    $detail_tr_form .=     "</tr>";

    $temp = array();
    $temp["order_common_seqno"] = $order_common_seqno;
    $opt_info = $dao->selectOrderInfoOption($conn, $temp);
    $opt_count = $opt_info->RecordCount();

    $html = '';
    while ($opt_info && !$opt_info->EOF) {
        $opt_name  = $opt_info->fields["opt_name"];
        $opt_price = $opt_info->fields["price"];

        $html .=
            sprintf($detail_tr_form, $opt_name
                                   , number_format($opt_price));

        $opt_info->MoveNext();
    }
    $ret .= sprintf($detail_form, $opt_count + 2
                                , $html);

    return $ret;
}
?>
