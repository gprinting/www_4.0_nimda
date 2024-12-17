<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 리스트 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/13 이청산 생성
 * 2017/07/20 이청산 수정 
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

//회원 일련번호
$member_seqno   = $fb["member_seqno"];
$from           = $fb["from"];
$to             = $fb["to"];
$crm_info_depar = $fb["crm_info_depar"];
$crm_info_empl  = $fb["crm_info_empl"];
$member_name    = $fb["member_name"];
$cs_type        = $fb["cs_type"];
$crm_dvs        = $fb["crm_dvs"];

if ($crm_info_depar) {
    $pre_rs = $dao->selectCrmDeparEmpl($conn, $crm_info_depar);

    while ($pre_rs && !$pre_rs->EOF) {
        $pre_fields = $pre_rs->fields; 

        $empl_arr[] = $pre_fields["name"]; 

        $pre_rs->MoveNext();
    }
}

$param = array();
$param["member_seqno"]   = $member_seqno;
$param["from"]           = $from;
$param["to"]             = $to;
$param["crm_info_empl"]  = $crm_info_empl;
$param["member_name"]    = $member_name;
$param["cs_type"]        = $cs_type;
$param["crm_dvs"]        = $crm_dvs;
$param["page"]           = $page;
$param["crm_info_depar"] = $dao->arr2paramStr($conn, $empl_arr);

//$conn->debug = 1;

$page_count = ($page - 1) * 5;

$msg_total = "";
if ($crm_dvs == "business") {
    $msg_total = $dao->selectCrmBusinessMessageTotal($conn, $param);
    $rs = $dao->selectCrmInfoBusinessList($conn, $param, $page_count);
} else if ($crm_dvs == "collect") {
    $msg_total = $dao->selectCrmCollectMessageTotal($conn, $param);
    $rs = $dao->selectCrmInfoCollectList($conn, $param, $page_count);
}

$json = "{\"thead\" : \"%s\", \"tbody\" : \"%s\", \"result_cnt\" : \"%s\", \"message_tot\" : \"%s\"}";

if ($rs->EOF) {
    $thead_html = '';
    $tbody_html = "<td colspan=\"8\">검색결과없음</td>";
    $result_cnt = 0;
    $msg_total[0]  = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$thead_html = '';
$thead_html = makeCrmInfoListTheadHtml(array(
    "result_cnt" => $result_cnt,
    "msg_total"  => $msg_total[0]
));
if ($crm_dvs == "business") {
    $tbody_html = makeCrmInfoBusinessListTbodyHtml($rs, $page);
} else if ($crm_dvs == "collect") {
    $tbody_html = makeCrmInfoCollectListTbodyHtml($rs, $page);
}

FIN : 
    echo sprintf($json, $util->convJsonStr($thead_html)
                      , $util->convJsonStr($tbody_html)
                      , $result_cnt
                      , $msg_total[0]);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief CRM 영업 정보 리스트 thead 생성
 *
 * @param $rs = 검색결과
 *
 * @return thead_html
 */
function makeCrmInfoListTheadHtml($param) {
    $thead_form  = "<th class=\"th_table_accent\"></th>";
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:center\">상담건수</th>";
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:center\">%s</th>";
    $thead_form .= "<th class=\"th_table_accent\" colspan=\"4\"></th>";
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:center\">%s건</th>";

    $thead_html = sprintf($thead_form, $param["result_cnt"]
                                     , $param["msg_total"]);

    return $thead_html;
}

/** 
 * @brief CRM 영업 정보 리스트 tbody 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 */
function makeCrmInfoBusinessListTbodyHtml($rs, $page) {
    $tbody_form .= "<tr id=\"crm_info_business_tr_%s\" ";
    $tbody_form .=     "class=\"crm_info_business_tr %s\" ";
    $tbody_form .=     "onclick=\"loadCrmInfoBusiness.exec(%s)\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $tbody_html = '';

    $page_block = ($page * 5) - 4;
    $num = 0;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $num = $page_block;
        if ($num % 2 == 0) {
            $class = "cellbg";
        } else if ($num % 2 == 1) {
            $class = "";
        }

        $cs_cont_val = explode('!', $fields["cs_cont"]);
        $cs_cont_vis = implode(",", $cs_cont_val);

        $tbody_html .= sprintf($tbody_form, $fields["crm_biz_info_seqno"]
                                          , $class
                                          , $fields["crm_biz_info_seqno"]
                                          , $page_block++
                                          , $fields["cs_date"]
                                          , $fields["member_name"]
                                          , $fields["cs_indu"]
                                          , $fields["empl_name"]
                                          , $fields["cs_promi_date"]
                                          , substr($cs_cont_vis, 0, -1)
                                          , $fields["msg_cnt"] . "건");

        $rs->MoveNext();
    }

    return $tbody_html;
}

/** 
 * @brief CRM 수금 정보 리스트 tbody 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 */
function makeCrmInfoCollectListTbodyHtml($rs, $page) {
    $tbody_form .= "<tr id=\"crm_info_collect_tr_%s\" ";
    $tbody_form .=     "class=\"crm_info_collect_tr %s\" ";
    $tbody_form .=     "onclick=\"loadCrmInfoCollect.exec(%s,%s)\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $tbody_html = '';

    $page_block = ($page * 5) - 4;
    $num = 0;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $num = $page_block;
        if ($num % 2 == 0) {
            $class = "cellbg";
        } else if ($num % 2 == 1) {
            $class = "";
        }

        $tbody_html .= sprintf($tbody_form, $fields["crm_collect_info_seqno"]
                                          , $class 
                                          , $fields["crm_collect_info_seqno"]
                                          , $fields["member_seqno"]
                                          , $page_block++
                                          , substr($fields["cs_date"], 0,10)
                                          , $fields["member_name"]
                                          , "수금"
                                          , $fields["empl_name"]
                                          , substr($fields["loan_pay_promi_date"], 0,10)
                                          , $fields["memo"]
                                          , $fields["msg_cnt"] . "건");

        $rs->MoveNext();
    }

    return $tbody_html;
}

?>
