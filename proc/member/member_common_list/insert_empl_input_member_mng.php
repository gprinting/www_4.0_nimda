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

$name        = $fb["name"];
$tel_num0    = $fb["tel0"];
$tel_num1    = $fb["tel1"];
$tel_num2    = $fb["tel2"];
$tel_num     = sprintf($num_form, $tel_num0
                                , $tel_num1
                                , $tel_num2);
$exten       = $fb["exten"];
$cell_num0   = $fb["cell0"];
$cell_num1   = $fb["cell1"];
$cell_num2   = $fb["cell2"];
$cell_num    = sprintf($num_form, $cell_num0
                                , $cell_num1
                                , $cell_num2);
$job         = $fb["job"];
$depar       = $fb["depar"];
$mail        = $fb["mail"];
$pro         = $fb["pro"];

if (!$util->validateCrn($crn0, $crn1, $crn2)) {
    $success = "false";
    $msg = "올바른 사업자 번호가 아닙니다.";

    goto END;
}

$param = [];
$param["member_seqno"] = $member_seqno;
$param["name"]         = $name;
$param["cpn_tel_num"]  = $tel_num;
$param["exten"]        = $exten;
$param["cell_num"]     = $cell_num;
$param["job"]          = $job;
$param["depar"]        = $depar;
$param["mail"]         = $mail;
$param["pro"]          = $pro;

$ret = $dao->updateEmplInputMemberMngInfo($conn, $param);

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
