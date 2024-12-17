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

$member_seqno = $fb["member_seqno"];

$corp_name   = $fb["corp_name"];
$repre_name  = $fb["repre_name"];
$crn0        = $fb["crn0"];
$crn1        = $fb["crn1"];
$crn2        = $fb["crn2"];
$crn         = sprintf($num_form, $crn0
                                , $crn1
                                , $crn2);
$bc          = $fb["bc"];
$tob         = $fb["tob"];

$tel_num0    = $fb["tel0"];
$tel_num1    = $fb["tel1"];
$tel_num2    = $fb["tel2"];
$tel_num     = sprintf($num_form, $tel_num0
                                , $tel_num1
                                , $tel_num2);
$fax_num0    = $fb["fax0"];
$fax_num1    = $fb["fax1"];
$fax_num2    = $fb["fax2"];
$fax_num     = sprintf($num_form, $fax_num0
                                , $fax_num1
                                , $fax_num2);
$zipcode     = $fb["zipcode"];
$addr        = $fb["addr"];
$addr_detail = $fb["addr_detail"];

if (!$util->validateCrn($crn0, $crn1, $crn2)) {
    $success = "false";
    $msg = "올바른 사업자 번호가 아닙니다.";

    goto END;
}

$param = [];
$param["member_seqno"] = $member_seqno;
$param["corp_name"]   = $corp_name;
$param["repre_name"]  = $repre_name;
$param["crn"]         = $crn;
$param["bc"]          = $bc;
$param["tob"]         = $tob;
$param["tel_num"]     = $tel_num;
$param["fax_num"]     = $fax_num;
$param["zipcode"]     = $zipcode;
$param["addr"]        = $addr;
$param["addr_detail"] = $addr_detail;

$ret = $dao->updateAdminLicenseeregiInfo($conn, $param);

if ($ret === false) {
    $success = "false";
    $msg = "데이터 입력에 실패했습니다.";
    goto END;
}

$msg = "추가에 성공했습니다.";

END:
    $json = "{\"success\" : \"%s\", \"msg\" : \"%s\"}";
    echo sprintf($json, $success, $msg);
    $conn->Close();
    exit;
