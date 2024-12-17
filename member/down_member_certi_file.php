<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new MemberCommonListDAO();

$fb = $fb->getform();

$member_certi_seqno = $fb["seqno"];

$param = [];
$param["member_certi_seqno"] = $member_certi_seqno;

$info = $dao->selectMemberCertiInfo($conn, $param);

$path = $_SERVER["SiteHome"] . SITE_NET_DRIVE . $info["file_path"] . '/' . $info["save_file_name"];
$file_size = filesize($path);

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $info["origin_file_name"] . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile($path);
