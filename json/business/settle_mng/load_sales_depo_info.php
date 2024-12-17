<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/16 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/settle_mng/SettleMngDAO.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/define/nimda/order_mng_define.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/OrderMngUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SettleMngDAO();
$util = new CommonUtil();
$order_util = new OrderMngUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

$cpn_admin_seqno   = $fb["cpn_admin_seqno"];
$basic_from        = $fb["basic_from"];
$basic_to          = $fb["basic_to"];
$high_depar_code   = $fb["high_depar"];
$depar_code        = $fb["depar"];
$empl_name         = $fb["empl"];
//$depo_input_typ    = $fb["depo_input_typ"];
//$depo_input_detail = $fb["depo_input_detail"];
//$deal_yn           = $fb["deal_yn"];
$dlvr_dvs          = $fb["dlvr_dvs"];
$dlvr_code         = $fb["dlvr_code"];
$search_dvs        = $fb["search_dvs"];
$search_keyword    = $fb["search_keyword"];
$search_depo       = $fb["search_depo"];
$depo_dvs          = $fb["depo_dvs"];
$depo_keyword      = $fb["depo_keyword"];
$member_typ        = $fb["member_typ"];
$member_grade      = $fb["member_grade"];
$oper_sys          = $fb["oper_sys"];
$oa_yn             = $fb["oa_yn"];

$cate_top          = $fb["cate_top"];
$cate_mid          = $fb["cate_mid"];
$cate_bot          = $fb["cate_bot"];

$cate_sortcode     = $cate_top;
if (!empty($cate_mid)) {
    $cate_sortcode     = $cate_mid;
} else if (!empty($cate_bot)) {
    $cate_sortcode     = $cate_bot;
}

$order_arr = $fb["order"];


$param = array();
$param["page"]            = $page;
$param["cpn_admin_seqno"] = $cpn_admin_seqno;
$param["from"]            = $basic_from;
$param["to"]              = $basic_to;
$param["page"]            = $page;
$param["depar"]           = $depar_code;
$param["empl"]            = $empl_name;
$param["member_typ"]      = $member_typ;
$param["member_grade"]    = $member_grade;
$param["order"]           = $order_arr;

//직원 팀 검색
$pre_rs = $dao->selectEmplTeamByName($conn, $param);
$empl_name = "";
while(!$pre_rs->EOF && $pre_rs) {
    $empl_name .= $pre_rs->fields["name"];
    $empl_name .= ",";

    $pre_rs->MoveNext();
}
$empl_name = substr($empl_name, 0, -1);

// 비었을경우 확실히 빈값을 넘김
if ($empl_name == "") {
    $empl_name = "";
}

$param["empl_name"] = $empl_name;

// 배송
if (empty($dlvr_code)) {
    $dlvr_code_arr = DLVR_CODE[$dlvr_dvs];
    $dlvr_code_str = "";
    if (!empty($dlvr_code_arr)) {
        $dlvr_code_str = implode(",", $dlvr_code_arr);
    }
    $param["dlvr_code_arr"] = $dlvr_code_str;
}
$param["dlvr_typ"] = DLVR_TYP[$dlvr_dvs];
$param["dlvr_code"] = $dlvr_code;

/***** 키워드 검색 시작 *****/
// 매출일 때
if ($search_depo == "sell") {
    $param["search_depo"] = "매출";
} else if ($search_depo == "depo") {
    $param["search_depo"] = "입금";
}
/***** 키워드 검색 끝 *****/

/***** 입금유형 검색 시작 *****/
if ($depo_dvs == "virt_account") {
    //현재 계좌번호를 입력받는 방식으로, 계좌번호 입력
    $param["dvs_detail"] = $depo_keyword;
}
/***** 입금유형 검색 끝 *****/


// 종합하여 검색
$page_count = ($page - 1) * 5;
$rs = $dao->selectSettleSalesDepo($conn, $param, $page_count);

$json = "{\"thead\" : \"%s\", \"tbody\" : \"%s\", \"result_cnt\" : \"%s\"}";
$tbody_html = '';

if ($rs->EOF) {
    $thead_html = '';
    $tbody_html = "<td colspan=\"15\">검색결과없음</td>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$thead_html = '';
$thead_html = makeSalesDepoListTheadHtml(array(
    "result_cnt" => $result_cnt,
));
//$tbody_html = makeSalesDepoList($rs, $page, $order_util);
$tbody_html = makeSalesDepoListMod($rs, $page, $order_util);

FIN :
    echo sprintf($json, $util->convJsonStr($thead_html)
                      , $util->convJsonStr($tbody_html)
                      , $result_cnt);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 함수 영역
 ******************************************************************************/


/**
 * @brief CRM 영업 정보 리스트 thead 생성
 *
 * @param $rs = 검색결과
 *
 * @return thead_html
 */
function makeSalesDepoListTheadHtml($param) {
    $thead_form  = "<th class=\"th_table_accent\"></th>";
    $thead_form .= "<th class=\"th_table_accent\">총계</th>";
    $thead_form .= "<th class=\"th_table_accent\">%s</th>";
//    $thead_form .= "<th colspan=\"9\"></th>";
    $thead_form .= "<th class=\"th_table_accent\" colspan=\"12\"></th>";

    $thead_html = sprintf($thead_form, $param["result_cnt"]);

    return $thead_html;
}

/** 
 * @brief 매출액 list 생성
 *
 * @param $rs = 검색결과
 *
 * @return list
 */
function makeSalesDepoListMod($rs, $page, $order_util) {
    $tbody_form .= "<tr id=\"sales_depo_tr_%s\" ";
    $tbody_form .=     "class=\"sales_depo_tr %s\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tbody_form .=     "<td>  </td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>  </td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $tbody_html = '';

    $price_dat = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $input_typ = $fields["input_typ"];
        $typ = $order_util->selectDepoInputType($input_typ);

        if ($page_block % 2 == 0) {
            $class = "cellbg";
        } else if ($page_block % 2 == 1) {
            $class = "";
        }

        $dvs_html   = "";
        $price_d_f  = 0;
        $price_d_s  = 0;
        $price_d_t  = 0;
        $price_s_f  = 0;
        $price_s_s  = 0;
        $price_s_t  = 0;
        $depo_dvs   = $fields['dvs'];
        $depo_dvs_s = mb_substr($depo_dvs, 0, 2);
        if ($depo_dvs_s == "입금") {
            $dvs_html = "입금액"; 
            $price_d_f  = intval($fields["exist_prepay"]);
            $price_d_s  = intval($fields["depo_price"]);
            $price_d_t  = intval($fields["prepay_bal"]);
            if ($price_d_f > $price_d_t) {
                $price_d_s  = -($price_d_s);
            } 
        } else if ($depo_dvs_s = "매출") {
            $dvs_html = "매출액";
            $price_s_f  = intval($fields["sell_price"]);
            $price_s_s  = intval($fields["pay_price"]);
            $price_s_t  = intval($fields["depo_price"]);
            if ($depo_dvs == "매출감소") {
                $price_s_s = -($price_s_s);
            }
        }

        $cont_html = $fields["cont"];
        $tbody_html .= sprintf($tbody_form, $page_block
                                          , $class
                                          , $page_block++
                                          , $fields["member_name"]
                                          , substr($fields["deal_date"], 0, 10)
                                          , $dvs_html
                                          , number_format($price_s_f)
                                          , number_format($price_d_f)
                                          , number_format($price_s_s) 
                                          , number_format($price_d_s) 
                                          , number_format($price_s_t)
                                          , number_format($price_d_t)
                                          , $typ
                                          , $fields["empl_name"]
                                          , $cont_html);

        $rs->MoveNext();
    }

    return $tbody_html;

}


?>
