<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 영업탭 정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/06/07 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();

$fb = $fb->getForm();

//일련번호
$seqno = $fb["seqno"];

$param = array();
$param["seqno"] = $seqno;

//$conn->debug = 1;
$rs = $dao->selectCrmInfoDetail($conn, $param);
$fields = $rs->fields;

//json 부분
$json  = '{';
$json .=   "\"loan_pay_promi_dvs\"          : \"%s\""; // 결제종류
$json .=  ",\"loan_pay_promi_date\"         : \"%s\""; // 결제 약속일
$json .=  ",\"loan_pay_promi_price\"        : \"%s\""; // 결제 약속금액
$json .=  ",\"memo\"                        : \"%s\""; // 상담내용
$json .=  ",\"handle_dvs\"                  : \"%s\""; // 처리여부
$json .=  ",\"cs_typ\"                      : \"%s\""; // 상담유형
$json .=  ",\"handle_date\"                 : \"%s\""; // 처리일시
$json .=  ",\"handle_typ\"                  : \"%s\""; // 처리방법
$json .=  ",\"aprvl_req_1\"                 : \"%s\""; // 담당자 승인
$json .=  ",\"aprvl_req_2\"                 : \"%s\""; // 팀장 승인
$json .=  ",\"aprvl_req_3\"                 : \"%s\""; // 본부장 승인
$json .=  ",\"aprvl_req_4\"                 : \"%s\""; // 대표이사 승인
$json .= '}';

echo sprintf($json, $fields["loan_pay_promi_dvs"]
                  , $fields["loan_pay_promi_date"]
                  , $fields["loan_pay_promi_price"]
                  , $fields["memo"]
                  , $fields["handle_dvs"]
                  , $fields["cs_typ"]
                  , $fields["handle_date"]
                  , $fields["handle_typ"]
                  , $fields["aprvl_req_1"]
                  , $fields["aprvl_req_2"]
                  , $fields["aprvl_req_3"]
                  , $fields["aprvl_req_4"]);

$conn->Close();



?>
