<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 수금탭 정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/23 엄준현 생성
 * 2017/06/01 이청산 수정
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
$member_seqno           = $fb["member_seqno"];
//CRM 수금정보 일련번호
$crm_collect_info_seqno = $fb["crm_collect_info_seqno"];

$param = array();
$param["member_seqno"]           = $member_seqno;
$param["crm_collect_info_seqno"] = $crm_collect_info_seqno;

//$conn->debug = 1;

//계좌정보
$crm_info_bank    = $dao->selectCrmInfoBank($conn, $param);
//나머지 수금 정보
$crm_info_collect = $dao->selectCrmInfoDetail($conn, $param);

$fields         = $crm_info_bank->fields;
$fields_collect = $crm_info_collect->fields;


//json 부분
$json  = '{';
$json .=    "\"bank_name\"            : \"%s\""; //은행이름
$json .=   ",\"ba_num\"               : \"%s\""; //계좌번호
$json .=   ",\"collect\"              :   %s  "; //수금 정보
$json .= '}';

//수금정보
$coll  = "";
$coll .= '[';
$coll .= '{';
$coll .=    "\"memo\"                 : \"%s\""; // 수금 메모
$coll .=   ",\"cs_date\"              : \"%s\""; // 영업날짜
$coll .=   ",\"empl_name\"            : \"%s\""; // 담당자
$coll .=   ",\"loan_pay_promi_dvs\"   : \"%s\""; // 결제종류
$coll .=   ",\"loan_pay_promi_date\"  : \"%s\""; // 결제약속일
$coll .=   ",\"loan_pay_promi_price\" : \"%s\""; // 결제약속금액
$coll .=   ",\"loan_limit_price\"     : \"%s\""; // 여신한도
$coll .=   ",\"loan_limit_use\"       : \"%s\""; // 여신한도 소진금액
$coll .=   ",\"handle_dvs\"           : \"%s\""; // 여신한도 소진금액
$coll .=   ",\"handle_date\"          : \"%s\""; // 여신한도 소진금액
$coll .= '}';
$coll .= ']';

echo sprintf($json, $fields["bank_name"]
                  , $fields["ba_num"]
                  , sprintf($coll, $fields_collect["memo"]
                                 , $fields_collect["cs_date"]
                                 , $fields_collect["empl_name"]
                                 , $fields_collect["loan_pay_promi_dvs"]
                                 , $fields_collect["loan_pay_promi_date"]
                                 , $fields_collect["loan_pay_promi_price"]
                                 , $fields_collect["loan_limit_price"]
                                 , $fields_collect["loan_limit_use"]
                                 , $fields_collect["handle_dvs"]
                                 , $fields_collect["handle_date"])
            );

$conn->Close();

?>
