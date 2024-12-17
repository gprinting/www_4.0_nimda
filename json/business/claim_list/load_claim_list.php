<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 클레임관리 리스트 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/25 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

$cpn_admin      = $fb["cpn_admin"];
$basic_from     = $fb["basic_from"];
$basic_to       = $fb["basic_to"];
$depar          = $fb["depar"];
$empl           = $fb["empl"];
$member_typ     = $fb["member_typ"];
$search_dvs     = $fb["search_dvs"];
$search_keyword = $fb["search_keyword"];

$param = array();
$param["cpn_admin"]  = $cpn_admin;
$param["from"]       = $basic_from;
$param["to"]         = $basic_to;
$param["depar"]      = $depar;
$param["empl"]       = $empl;
$param["member_typ"] = $member_typ;
$param[$search_dvs]  = $search_keyword;

$page_count = ($page - 1) * 5;

//직원 팀 검색
$pre_rs = $dao->selectEmplTeam($conn, $param);
$empl_seqno = "";
while(!$pre_rs->EOF && $pre_rs) {
    $empl_seqno .= $pre_rs->fields["empl_seqno"];
    $empl_seqno .= ",";

    $pre_rs->MoveNext();
}
$empl_seqno = substr($empl_seqno, 0, -1);

// 빈값일경우 확실히 빈값을 넘김 
if ($empl_seqno == "") {
    $empl_seqno = "";
}

$param["empl_seqno"] = $empl_seqno;

//상태 일 때 
if ($search_dvs == "status" && !empty($search_keyword)) {
    $param["state"] = $search_keyword;
}

$rs = $dao->selectClaimListByCond($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"total\" : \"%s\", \"result_cnt\" : \"%s\"}";

if ($rs->EOF) {
    $list  = "<tr>";
    $list .= "<td colspan=12 style=\"text-align:center\">검색결과없음</td>";
    $list .= "</tr>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$total = '';
$total = makeClaimListTotalHtml(array(
    "result_cnt" => $result_cnt,
));
$list = makeClaimListByHtml($rs, $page);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $util->convJsonStr($total)
                      , $result_cnt);

    $conn->Close();
    exit;


/*************************************함수 영역 ********************************************/

/**
 * @brief 클레임 리스트 총계 생성
 *
 * @param $rs = 검색결과
 *
 * @return total_html
 */
function makeClaimListTotalHtml($param) {
    $total_form  = "<th class=\"th_table_accent\"></th>";
    $total_form .= "<th class=\"th_table_accent\">TOTAL</th>";
    $total_form .= "<th class=\"th_table_accent\">%s</th>";
    $total_form .= "<th class=\"th_table_accent\" colspan=\"9\"></th>";

    $total_html = sprintf($total_form, $param["result_cnt"]);

    return $total_html;
}

/**
 * @brief 클레임 리스트 생성
 *
 * @param $rs = 검색결과
 *
 * @return list_html
 */
function makeClaimListByHtml($rs, $page) {

    $list_form .= "<tr class='%s'>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td><button type=\"button\" class=\"orge btn_pu btn fix_height20 fix_width75\" onclick=\"getClaim.exec('%s', '%s');\">상세보기</button></td>";
    $list_form .= "</tr>";

    $list_html  = '';

    $page_block = ($page * 5) - 4;
    $class = "";
    $num = 0;

    while ($rs && !$rs->EOF) {

        $num = $page_block;
        if ($num % 2 == 0) {
            $class = "cellbg";
        } else if ($num % 2 == 1) {
            $class = "";
        }

        $fields = $rs->fields;

        $list_html .= sprintf($list_form, $class
                                        , $page_block++ 
                                        , $fields["regi_date"]
            //  $rs->fields["member_name"] . " <span style=\"color:blue; font-weight: bold;\">[" . $rs->fields["office_nick"] . "]</span>",
                                        , $fields["member_name"]
                                        , $fields["order_regi_date"]
                                        , $fields["order_num"]
                                        , $fields["order_title"]
                                        , $fields["claim_title"]
                                        , number_format($fields["count"])
            //  $rs->fields["dvs"],
                                        , number_format($fields["pay_price"])
                                        , $fields["state"]
                                        , $fields["agree_yn"]
                                        , $fields["order_claim_seqno"]
                                        , $page);
        $rs->moveNext();
    }

    return $list_html;
}

?>
