<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberCommonListDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$success = "success";

$num_form = "%s-%s-%s";

$member_seqno                = $fb["member_seqno"];
$office_nick                 = $fb["office_nick"];
$cpn_admin_seqno             = $fb["cpn_admin_seqno"];
$member_dvs                  = $fb["member_dvs"];
$tel_num0                    = $fb["tel_num0"];
$tel_num1                    = $fb["tel_num1"];
$tel_num2                    = $fb["tel_num2"];
$tel_num                     = sprintf($num_form, $tel_num0
                                                , $tel_num1
                                                , $tel_num2);
$cell_num0                   = $fb["cell_num0"];
$cell_num1                   = $fb["cell_num1"];
$cell_num2                   = $fb["cell_num2"];
$cell_num                    = sprintf($num_form, $cell_num0
                                                , $cell_num1
                                                , $cell_num2);
$mail                        = $fb["mail"];
$sms_ynN                     = $fb["sms_ynN"];
$default_release             = $fb["default_release"];
$is_except                   = $fb["is_except"];
if($is_except == '' || $is_except == null) $is_except = 'N';

$hp                          = $fb["hp"];
$zipcode                     = $fb["zipcode"];
$addr                        = $fb["addr"];
$addr_detail                 = $fb["addr_detail"];

$licensee_info_seqno         = $fb["licensee_info_seqno"];
$corp_name                   = $fb["corp_name"];
$repre_name                  = $fb["repre_name"];
$crn0                        = $fb["crn0"];
$crn1                        = $fb["crn1"];
$crn2                        = $fb["crn2"];
$crn                         = sprintf($num_form, $crn0
                                                , $crn1
                                                , $crn2);
$bc                          = $fb["bc"];
$tob                         = $fb["tob"];
$fax_num0                    = $fb["licensee_fax_num0"];
$fax_num1                    = $fb["licensee_fax_num1"];
$fax_num2                    = $fb["licensee_fax_num2"];
$fax_num                     = sprintf($num_form, $fax_num0
                                                , $fax_num1
                                                , $fax_num2);
$licensee_zipcode            = $fb["licensee_zipcode"];
$licensee_addr               = $fb["licensee_addr"];
$licensee_addr_detail        = $fb["licensee_addr_detail"];

$admin_licenseeregi_info_arr = $fb["admin_licenseeregi_info"];
$empl_input_mng_info_arr     = $fb["empl_input_mng_info"];

$bank_name              = $fb["bank_name"];
$refund_ba_num          = $fb["refund_ba_num"];
$refund_bank_name       = $fb["refund_bank_name"];
$refund_name            = $fb["refund_name"];
$cashreceipt_name       = $fb["cashreceipt_name"];
$cashreceipt_cell_num   = $fb["cashreceipt_cell_num"];
$cashreceipt_card_num   = $fb["cashreceipt_card_num"];
$tax_dvs                = $fb["tax_dvs"];
$member_typ             = $fb["member_typ"];
$member_grade           = $fb["member_grade"];
$dlvr_dvs               = $fb["dlvr_dvs"];
$dlvr_code              = $fb["dlvr_code"];
$card_pay_yn            = $fb["card_pay_yn"];
$nc_release_resp        = $fb["nc_release_resp"];
$bl_release_resp        = $fb["bl_release_resp"];

$action_result          = $fb["action_result"];
$loan_pay_promi_date    = $fb["loan_pay_promi_date"];
$loan_limit_price       = $fb["loan_limit_price"];
$loan_pay_promi_dvs     = $fb["loan_pay_promi_dvs"];
$loan_pay_promi_dvs_day = $fb["loan_pay_promi_dvs_day"];

$member_mng_seqno       = $fb["member_mng_seqno"];
$depar                  = $fb["depar"];
$cm                     = $fb["cm"];
$ca                     = $fb["ca"];
$crm                    = $fb["crm"];
$cs                     = $fb["cs"];
$pro                    = $fb["pro"];

$sms_yn                    = $fb["sms_yn"];
$mailing_yn                    = $fb["mailing_yn"];

$direct_dlvr_yn = "N";
if($dlvr_dvs == "직배") {
    $direct_dlvr_yn = "Y";
    $dlvr_add_info = $fb["dlvr_code"];
} else {
    $dlvr_add_info = $dlvr_dvs;
}
$conn->StartTrans();

$param = [];

// member 테이블 - update
$param["member_seqno"]         = $member_seqno;
$param["member_name"]          = $office_nick;
$param["office_nick"]          = $office_nick;
$param["member_dvs"]           = $member_dvs;
$param["member_typ"]           = $member_typ;
$param["cpn_admin_seqno"]      = $cpn_admin_seqno;
$param["grade"]                = $member_grade;
$param["tel_num"]              = $tel_num;
$param["cell_num"]             = $cell_num;
$param["fax_num"]              = $fax_num;
$param["birth"]                = $birth;
$param["mail"]                 = $mail;
$param["mailing_yn"]           = $mailing_yn;
$param["sms_yn"]               = $sms_yn;
$param["default_release"]      = $default_release;
$param["is_except"]            = $is_except;
$param["hp"]                   = $hp;
$param["zipcode"]              = $zipcode;
$param["addr"]                 = $addr;
$param["addr_detail"]          = $addr_detail;
$param["direct_dlvr_yn"]       = $direct_dlvr_yn;
$param["dlvr_add_info"]        = $dlvr_add_info;
$param["nc_release_resp"]      = $nc_release_resp;
$param["bl_release_resp"]      = $bl_release_resp;
$param["card_pay_yn"]          = $card_pay_yn;
$param["tax_dvs"]              = $tax_dvs;
$param["cashreceipt_name"]     = $cashreceipt_name;
$param["cashreceipt_cell_num"] = $cashreceipt_cell_num;
$param["cashreceipt_card_num"] = $cashreceipt_card_num;
$param["refund_ba_num"]        = $refund_ba_num;
$param["refund_bank_name"]     = $refund_bank_name;
$param["refund_name"]          = $refund_name;
$ret = $dao->updateMemberInfo($conn, $param);
if ($ret === false) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    $success = "false";
    $msg = "회원정보 수정에 실패했습니다.";
    goto END;
}

// licensee_info 테이블 - insert duplicate key update
unset($param);
$param["licensee_info_seqno"] = $licensee_info_seqno;
$param["member_seqno"] = $member_seqno;
$param["corp_name"]    = $corp_name;
$param["repre_name"]   = $repre_name;
$param["crn"]          = $crn; 
$param["bc"]           = $bc; 
$param["tob"]          = $tob;
$param["zipcode"]      = $licensee_zipcode; 
$param["addr"]         = $licensee_addr;
$param["addr_detail"]  = $licensee_addr_detail;

$ret = $dao->updateLicenseeInfo($conn, $param);

if ($ret === false) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    $success = "false";
    $msg = "사업자 정보 수정에 실패했습니다.";
    goto END;
}

// admin_licenseeregi 테이블 - insert duplicate key update
unset($param);
$param["member_seqno"] = $member_seqno;
foreach($admin_licenseeregi_info_arr as $info) {
    $crn0 = $info["crn0"];
    $crn1 = $info["crn1"];
    $crn2 = $info["crn2"];
    $crn  = sprintf($num_form, $crn0
                             , $crn1
                             , $crn2);
    $tel0 = $info["tel0"];
    $tel1 = $info["tel1"];
    $tel2 = $info["tel2"];
    $tel  = sprintf($num_form, $tel0
                             , $tel1
                             , $tel2);
    $fax0 = $info["fax0"];
    $fax1 = $info["fax1"];
    $fax2 = $info["fax2"];
    $fax  = sprintf($num_form, $fax0
                             , $fax1
                             , $fax2);

    $param["admin_licenseeregi_seqno"] = $info["seqno"];
    $param["corp_name"]   = $info["corp_name"];
    $param["repre_name"]  = $info["repre_name"];
    $param["crn"]         = $crn;
    $param["bc"]          = $info["bc"];
    $param["tob"]         = $info["tob"];
    $param["tel_num"]     = $tel;
    $param["fax_num"]     = $fax;
    $param["zipcode"]     = $info["zipcode"];
    $param["addr"]        = $info["addr"];
    $param["addr_detail"] = $info["addr_detail"];

    $ret = $dao->updateAdminLicenseeregiInfo($conn, $param);

    if ($ret === false) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        $success = "false";
        $msg = "관리사업자 정보 수정에 실패했습니다.";
        goto END;
    }
}

// empl_input_member_mng 테이블 - insert duplicate key update
unset($param);
$param["member_seqno"] = $member_seqno;
foreach($empl_input_mng_info_arr as $info) {
    $tel0 = $info["tel0"];
    $tel1 = $info["tel1"];
    $tel2 = $info["tel2"];
    $tel  = sprintf($num_form, $tel0
                             , $tel1
                             , $tel2);
    $cell0 = $info["cell0"];
    $cell1 = $info["cell1"];
    $cell2 = $info["cell2"];
    $cell  = sprintf($num_form, $cell0
                              , $cell1
                              , $cell2);

    $param["empl_input_member_mng_seqno"] = $info["seqno"];
    $param["name"]         = $info["name"];
    $param["cpn_tel_num"]  = $tel;
    $param["exten"]        = $info["exten"];
    $param["cell_num"]     = $cell;
    $param["job"]          = $info["job"];
    $param["depar"]        = $info["depar"];
    $param["mail"]         = $info["mail"];
    $param["pro"]          = $info["pro"];

    $ret = $dao->updateEmplInputMemberMngInfo($conn, $param);

    if ($ret === false) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        $success = "false";
        $msg = "직원입력 회원담당자 정보 수정에 실패했습니다.";
        goto END;
    }
}

// excpt_member 테이블 - insert duplicate key update
/*
if ($member_typ === "예외업체") {
    unset($param);
    $param["member_seqno"]           = $member_seqno;
    $param["excpt_member_seqno"]     = $excpt_member_seqno;
    $param["action_result"]          = $action_result;
    $param["loan_pay_promi_date"]    = $loan_pay_promi_date;
    $param["loan_limit_price"]       = $loan_limit_price;
    $param["loan_pay_promi_dvs"]     = $loan_pay_promi_dvs;
    $param["loan_pay_promi_dvs_day"] = $loan_pay_promi_dvs_day;
    $ret = $dao->updateExcptMemberInfo($conn, $param);
}

if ($ret === false) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    $success = "false";
    $msg = "예외회원정보 수정에 실패했습니다.";
    goto END;
}
*/
// member_mng 테이블 - insert duplicate key update
unset($param);
$param["member_seqno"]     = $member_seqno;
$param["member_mng_seqno"] = $member_mng_seqno;
$param["resp_deparcode"]   = $depar;
//$param["cm"]               = $cm;
$param["ca"]               = $ca;
$param["crm"]              = $crm;
$param["cs"]               = $cs;
$param["pro"]              = $pro;

//$ret = $dao->updateMemberMngInfo($conn, $param);

if ($ret === false) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    $success = "false";
    $msg = "회원 담당자 수정에 실패했습니다.";
    goto END;
}

$msg = "정보를 수정했습니다.";

END:
    $conn->CompleteTrans();
    $json = "{\"success\" : \"%s\", \"msg\" : \"%s\"}";
    echo sprintf($json, $success, $msg);
    $conn->Close();
    exit;

/****************************************************************************** 
 ************ 함수영역
 ******************************************************************************/

