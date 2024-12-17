<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/03 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/settle_mng/SettleMngDAO.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/define/nimda/order_mng_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SettleMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$cpn_admin_seqno   = $fb["cpn_admin_seqno"];
$basic_from        = $fb["basic_from"];
$basic_to          = $fb["basic_to"];
$high_depar_code   = $fb["high_depar"];
$depar_code        = $fb["depar"];
$empl_seqno        = $fb["empl"];
//$depo_input_typ    = $fb["depo_input_typ"];
//$depo_input_detail = $fb["depo_input_detail"];
//$deal_yn           = $fb["deal_yn"];
$dlvr_dvs          = $fb["dlvr_dvs"];
$dlvr_code         = $fb["dlvr_code"];
$search_dvs        = $fb["search_dvs"];
$search_keyword    = $fb["search_keyword"];
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

$page     = $fb["page"];
$page_dvs = $fb["page_dvs"];

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);
$page = ($page - 1) * 5;

$param = [];
$param["page_dvs"]        = $page_dvs;
$param["high_depar_code"] = $high_depar_code;
$param["depar_code"]      = $depar_code;
$param["empl_seqno"]      = $empl_seqno;
$param["dlvr_way"]        = $dlvr_dvs;
$param["info_cpn"]        = $dlvr_code;
$param[$search_dvs]       = $search_keyword;
$param["member_typ"]      = $member_typ;
$param["grade"]           = $member_grade;
$param["cate_sortcode"]   = $cate_sortcode;
$param["oper_sys"]        = $oper_sys;
$param["order"]           = $order_arr;

$param["cpn_admin_seqno"] = $cpn_admin_seqno;
$param["from"] = $basic_from;
$param["to"]   = $basic_to;
$param["input_date"] = $basic_to;

/*
$conn->debug = 1;
*/

// 담당자 총계생성
$sum = '';
if (empty($page_dvs)) {
    $sum_rs = $dao->selectSettleInfo($conn, $param, -1);
    $sum = makeOaSalesInfoSumHtml($sum_rs->fields);
    unset($sum_rs);
}

// 리스트 생성
$list_rs = $dao->selectSettleInfo($conn, $param, $page);
$conn->debug = 0;

$result_cnt = 0;
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$list = makeOaSalesInfoHtml($list_rs, $page);

$json = "{\"sum\" : \"%s\", \"list\" : \"%s\", \"result_cnt\" : \"%s\"}";

echo sprintf($json, $util->convJsonStr($sum)
                  , $util->convJsonStr($list)
                  , $result_cnt);

$conn->Close();

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief 개인별 업체별 미수/매출액 총계 부분 html
 *
 * @param fields = 검색결과 fields
 *
 * @return html
 */
function makeOaSalesInfoSumHtml($fields) {
    $form = <<<HTML
        <td class="th_table_accent">총계</td>
        <td class="th_table_accent"></td>
        <td class="th_table_accent">%s</td>
        <td class="th_table_accent">%s</td>
        <td class="th_table_accent">%s</td>
HTML;

    return sprintf($form, number_format($fields["sum_net_price"])
                        , number_format($fields["sum_depo_price"])
                        , number_format($fields["sum_period_end_oa"]));
}

/**
 * @brief 개인별 업체별 미수/매출액 담당자 부분 html
 *
 * @param html 생성용 정보배열
 *
 * @return html
 */
function makeOaSalesInfoHtml($rs, $page) {
    $form = <<<HTML
        <tr id="oa_sales_tr_%s" class="%s" onclick="loadOaSalesDetail.exec('%s');">
            <td>%s</td>
            <td>%s</td>
            <td style="text-align:right;">%s</td>
            <td style="text-align:right;">%s</td>
            <td style="text-align:right;">%s</td>
        </tr>
HTML;

    $html  = '';

    while ($rs && !$rs->EOF) {
    
        if ($page % 2 == 0) {
            $class = "";
        } else if ($page % 2 == 1) {
            $class = "cellbg";
        }

        $fields = $rs->fields;
        $html .= sprintf($form, $fields["empl_seqno"]
                              , $class
                              , $fields["empl_seqno"]
                              , ++$page
                              , $fields["name"]
                              , number_format($fields["sum_net_price"])
                              , number_format($fields["sum_depo_price"])
                              , number_format($fields["sum_period_end_oa"]));

        $rs->MoveNext();
    }

    return $html;
}
