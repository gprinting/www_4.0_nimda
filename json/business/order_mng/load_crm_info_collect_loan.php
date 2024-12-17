<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 수금탭 여신정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/21 이청산 생성
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

//회원 일련번호
$member_seqno                    = $fb["member_seqno"];

$param = array();
$param["member_seqno"]           = $member_seqno;

//$conn->debug = 1;

//여신한도정보
$crm_info_excpt   = $dao->selectCrmInfoExcpt($conn, $param);
//계좌정보
$crm_info_bank    = $dao->selectCrmInfoBank($conn, $param);
//미수액정보
$crm_info_stats   = $dao->selectCrmInfoStats($conn, $param);

$fields_bank      = $crm_info_bank->fields;
$fields_stats     = $crm_info_stats->fields;

//여신한도
$json  = '{';
$json .=    "\"bank_name\"            : \"%s\""; //은행이름
$json .=   ",\"ba_num\"               : \"%s\""; //계좌번호
$json .=   ",\"loan_limit_price\"     : \"%s\""; //여신한도
$json .=   ",\"loan_limit_use\"       :   %s  "; //미수액
$json .= '}';

//여신한도
$loan   = $crm_info_excpt->fields["loan_limit_price"];

//수금정보
$stats  = "";
$stats .= '[';
$stats .= '{';
$stats .=    "\"period_end_oa\"       : \"%s\""; // 수금 메모
$stats .=   ",\"carryforward_oa\"     : \"%s\""; // 영업날짜
$stats .= '}';
$stats .= ']';

echo sprintf($json, $fields_bank["bank_name"]
                  , $fields_bank["ba_num"]
                  , $loan
                  , sprintf($stats, $fields_stats["period_end_oa"]
                                  , $fields_stats["carryforward_oa"])
            );

$conn->Close();

?>
