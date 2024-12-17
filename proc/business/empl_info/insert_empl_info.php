<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 직원정보 입력
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/09/27 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/empl_info/EmplInfoDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

$fb = new FormBean();
$dao = new EmplInfoDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

//사번
$empl_num               = $fb["empl_num"];
//직원명
$empl_name              = $fb["empl_name"];
//직책
$empl_duty              = $fb["empl_duty"];
//직급
$empl_posi              = $fb["empl_posi"];
//상위부서
$empl_top_dept          = $fb["empl_top_dept"];
//하위부서
$empl_mid_dept          = $fb["empl_mid_dept"];
//이메일
$empl_mail              = $fb["empl_mail"];
//휴대폰 
$empl_cell_top          = $fb["empl_cell_top"];
//휴대폰 중간
$empl_cell_mid          = $fb["empl_cell_mid"];
//휴대폰 끝
$empl_cell_end          = $fb["empl_cell_end"];
//전화
//$empl_phone_top         = $fb["empl_phone_top"];
//전화 중간
//$empl_phone_mid         = $fb["empl_phone_mid"];
//전화 끝(내선번호)
$empl_phone_end         = $fb["empl_phone_end"];
//상태
$empl_status            = $fb["empl_status"];
//권한
$empl_auth              = $fb["empl_auth"];

//휴대폰 번호
$empl_cell = $empl_cell_top . "-" . $empl_cell_mid . "-" . $empl_cell_end;

//전화 번호
//$empl_phone = $empl_phone_top . "-" . $empl_phone_mid . "-" . $empl_phone_end;

$param = array();
$param["empl_num"]       = $empl_num;
$param["empl_name"]      = $empl_name;
$param["empl_duty"]      = $empl_duty;
$param["empl_posi"]      = $empl_posi;
$param["empl_top_dept"]  = $empl_top_dept;
$param["empl_mid_dept"]  = $empl_mid_dept;
$param["empl_mail"]      = $empl_mail;
$param["empl_cell"]      = $empl_cell;
//$param["empl_phone"]     = $empl_phone;
$param["empl_phone"]     = $empl_phone_end;
$param["empl_status"]    = $empl_status;
$param["empl_auth"]      = $empl_auth;

$conn->StartTrans();

$ins_empl = $dao->insertBasicEmpl($conn, $param);

//직원 세부정보 등록을 위한 일련번호
$empl_seqno = $conn->Insert_ID();

$ret = "";
if (!$ins_empl) {
    $ret = "직원 기본정보 입력에 실패했습니다.";
    goto ERR;
}

$param["empl_seqno"] = $empl_seqno;

$ins_empl_sub = $dao->insertSubEmpl($conn, $param);

if (!$ins_empl_sub) {
    $ret = "직원 세부정보 입력에 실패했습니다.";
    goto ERR;
}
$ret = "등록 성공";
goto END;

ERR: 
    $conn->FailTrans();
    $conn->RollbackTrans();

END:
    $conn->CompleteTrans();
    echo $ret;
    $conn->Close();
?>
