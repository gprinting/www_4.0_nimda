<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 신규회원정보 정보 검색해서 html 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/05 이청산 생성
 * 2017/09/07 이청산 수정
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
$page = ($page - 1) * 5;
$page_dvs = $fb["page_dvs"];

$cpn_admin_seqno = $fb["cpn_admin_seqno"];
$member_name = $fb["member_name"];
$from = $fb["from"];
$to   = $fb["to"];

$param = array();
$param["cpn_admin_seqno"] = $cpn_admin_seqno;
$param["from"]            = $from;
$param["to"]              = $to;
$param["page_dvs"]        = $page_dvs;

$sell_site = $dao->selectCpnAdmin($conn, $param)->fields["sell_site"];
$depar_rs  = $dao->selectDeparAdminList($conn, $param);
$member_rs = $dao->selectNewMemberList($conn, $param, $page);

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$depar_arr = [];
while ($depar_rs && !$depar_rs->EOF) {
    $fields = $depar_rs->fields;

    $depar_arr[$fields["depar_code"]] = $fields["depar_name"];

    $depar_rs->MoveNext();
}

$html  = "<tr class=\"%s\">";
$html .=     "<td>%s</td>"; // no
$html .=     "<td>%s</td>"; // 판매채널
$html .=     "<td>%s</td>"; // 이름
$html .=     "<td>%s</td>"; // 등급
$html .=     "<td>%s</td>"; // 가입일
$html .=     "<td>%s</td>"; // 주문일
$html .=     "<td>%s</td>"; // 접수팀
$html .=     "<td>%s</td>"; // 배송방법
$html .=     "<td>%s</td>"; // 담당자
$html .=     "<td><button tyle=\"button\" onclick=\"showMemberDetail('%s');\" class=\"btn_yellow\">수정</button></td>";
$html .= "</tr>";

$tbody_html = '';
while ($member_rs && !$member_rs->EOF) {
    $fields = $member_rs->fields;
    
    if ($page % 2 == 0) {
        $class = "";
    } else if ($page % 2 == 1) {
        $class = "cellbg";
    }

    $tbody_html .= sprintf($html, $class
                                , ++$page
                                , $sell_site
                                , $fields["member_name"]
                                , $fields["grade"]
                                , $fields["first_join_date"]
                                , $fields["first_order_date"]
                                , $depar_arr[$fields["resp_deparcode"]]
                                , $fields["dlvr_code"]
                                , $fields["ibm_name"] . '/' . $fields["mac_name"]
                                , $fields["member_seqno"]);

    $member_rs->MoveNext();
}

$json  = '{';
$json .=   "\"result_cnt\" : \"%s\",";
$json .=   "\"tbody\"      : \"%s\"";
$json .= '}';

echo sprintf($json, $result_cnt
                  , $util->convJsonStr($tbody_html));

$conn->Close();
