<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 직원정보 수정
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/10/12 이청산 생성
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


//일련번호
$empl_seqno             = $fb["empl_seqno"];
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
//내선전화
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
// 기본정보
$param["empl_seqno"]    = $empl_seqno;
$param["empl_auth"]     = $empl_auth;
$param["empl_top_dept"] = $empl_top_dept;
$param["empl_mid_dept"] = $empl_mid_dept;
$param["empl_posi"]     = $empl_posi;
$param["empl_cell"]     = $empl_cell;
$param["empl_phone"]    = $empl_phone_end;
$param["empl_status"]   = $empl_status;
$param["empl_duty"]     = $empl_duty;

// 세부정보
$param["empl_name"]     = $empl_name;
$param["empl_mail"]     = $empl_mail;
//$param["empl_phone"]    = $empl_phone;

$conn->StartTrans();

$upd_empl = $dao->updateBasicEmpl($conn, $param);

$ret = "";
if (!$upd_empl) {
    $ret = "직원 기본정보 수정에 실패했습니다.";
    goto ERR;
}

$param["empl_seqno"] = $empl_seqno;

$upd_empl_sub = $dao->updateSubEmpl($conn, $param);

if (!$upd_empl_sub) {
    $ret = "직원 세부정보 수정에 실패했습니다.";
    goto ERR;
}
$ret = "수정 성공";
goto END;

ERR:
    $conn->FailTrans();
    $conn->RollbackTrans();

END:
    $conn->CompleteTrans();
    echo $ret;
    $conn->Close();
?>
