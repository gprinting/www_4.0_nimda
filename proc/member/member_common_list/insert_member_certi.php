<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberCommonListDAO();
$fileDAO = new FileAttachDAO();

$fb = $fb->getform();

$member_seqno       = $fb["member_seqno"];
$member_certi_seqno = $fb["file_seqno"];

$param = [];

if (!empty($member_certi_seqno)) {
    $param["member_certi_seqno"] = $member_certi_seqno;
    $info = $dao->selectMemberCertiInfo($conn, $param);

    $path = $_SERVER["SiteHome"] . SITE_NET_DRIVE . $info["file_path"] . '/' . $info["save_file_name"];
    @unlink($path);
}

$file = $_FILES["file"];

unset($param);
$param["origin_file_name"] = $file["name"];
$param["file_path"]        = SITE_DEFAULT_MEMBER_CERTI_FILE;
$param["tmp_name"]         = $file["tmp_name"];

$ret = $fileDAO->upLoadFile($param);

unset($param);
$param["member_seqno"]       = $member_seqno;
$param["member_certi_seqno"] = $member_certi_seqno;
$param["file_path"]        = $ret["file_path"];
$param["save_file_name"]   = $ret["save_file_name"];
$param["origin_file_name"] = $file["name"];

$ret = $dao->updateMemberCertiInfo($conn, $param);

if ($ret === false) {
    $success = "false";
    $msg = "데이터 입력에 실패했습니다.";
    goto END;
}

$msg = "추가에 성공했습니다.";
$file_name = $file["name"];

END:
    $json = "{\"success\" : \"%s\", \"msg\" : \"%s\", \"file_name\" : \"%s\"}";
    echo sprintf($json, $success, $msg, $file_name);
    $conn->Close();
