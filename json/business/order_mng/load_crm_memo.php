<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 메모 리스트 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/18 이청산 생성
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

//영업-수금 구분
$dvs                    = $fb["dvs"];
//리스트 로드-일반 로드 구분
$func_dvs               = $fb["func_dvs"];
//일반 로드시 일련번호
$memo_seqno             = $fb["memo_seqno"];
//리스트 로드시 영업테이블 일련번호
$crm_biz_info_seqno     = $fb["crm_biz_info_seqno"];
//리스트 로드시 수금테이블 일련번호
$crm_collect_info_seqno = $fb["crm_collect_info_seqno"];

$param = array();
$param["dvs"]                    = $dvs;
$param["func_dvs"]               = $func_dvs;
$param["memo_seqno"]             = $memo_seqno;
$param["crm_biz_info_seqno"]     = $crm_biz_info_seqno;
$param["crm_collect_info_seqno"] = $crm_collect_info_seqno;

//$conn->debug = 1;

$page_count = ($page - 1) * 5;
$rs = '';
if ($dvs == "business") {
    if ($func_dvs == "update") {
        $rs = $dao->selectCrmBusinessMemo($conn, $param);
    } else {
        $rs = $dao->selectCrmBusinessMemoList($conn, $param, $page_count);
    }
} else if ($dvs == "collect") {
    if ($func_dvs == "update") {
        $rs = $dao->selectCrmCollectMemo($conn, $param);
    } else {
        $rs = $dao->selectCrmCollectMemoList($conn, $param, $page_count);
    }  
}

if ($func_dvs == "update") {
    $fields = $rs->fields;
    
    $json  = '{';
    $json .=   "\"memo_date\"     : \"%s\""; // 메모 날짜
    $json .=  ",\"memo_cont\"     : \"%s\""; // 메모 내용
    $json .= '}';

    echo sprintf($json, $fields["memo_date"]
                      , $fields["memo_cont"]);
    $conn->Close();
} else {
    $json = "{\"thead\" : \"%s\", \"tbody\" : \"%s\", \"result_cnt\" : \"%s\"}";
    
    if ($rs->EOF) {
        $thead_html = '';
        $tbody_html = "<td colspan=\"4\">검색결과없음</td>";
        $result_cnt = 0;
    
        goto FIN;
    }
    
    $result_cnt = '';
    if (empty($page_dvs)) {
        $result_cnt = $dao->selectFoundRows($conn);
    }
    
    $thead_html = '';
    $thead_html = makeCrmMemoListTheadHtml(array(
        "result_cnt" => $result_cnt,
    ));
    $tbody_html = makeCrmMemoListTbodyHtml($rs, $page, $dvs);
    
    FIN : 
        echo sprintf($json, $util->convJsonStr($thead_html)
                          , $util->convJsonStr($tbody_html)
                          , $result_cnt);
    
        $conn->Close();
        exit;
}

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
function makeCrmMemoListTheadHtml($param) {
    $thead_form  = "<th></th>";
    $thead_form .= "<th></th>";
    $thead_form .= "<th colspan=\"4\">총계 %s</th>";

    $thead_html = sprintf($thead_form, $param["result_cnt"]);

    return $thead_html;
}

/** 
 * @brief CRM 영업 정보 리스트 tbody 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 */
function makeCrmMemoListTbodyHtml($rs, $page, $dvs) {
    $tbody_form .= "<tr id=\"crm_memo_tr_%s\" ";
    $tbody_form .=     "class=\"crm_memo_tr %s\" ";
    $tbody_form .=     "onclick=\"%s\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td><input type=\"checkbox\" id=\"crm_memo_chk_%s\" name=\"crm_memo_chk\"></td>"; 
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $tbody_html = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        if ($dvs == "business") {
            $f_seqno = $fields["crm_biz_info_memo_seqno"];
        } else if ($dvs == "collect") {
            $f_seqno = $fields["crm_collect_info_memo_seqno"];
        }

        if ($page_block % 2 == 0) {
            $class = "cellbg";
        } else if ($page_block % 2 == 1) {
            $class = ""; 
        }

        $tbody_html .= sprintf($tbody_form, $f_seqno
                                          , $class
                                          , $f_seqno
                                          , $page_block++
                                          , $f_seqno
                                          , $fields["memo_date"]
                                          , $fields["memo_cont"]);

        $rs->MoveNext();
    }

    return $tbody_html;
}


?>
