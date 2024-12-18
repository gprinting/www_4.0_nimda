<?php
/**
 * 광고천하 접수파일 다운로드
 */

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/api/AoAdchunhaDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/define/nimda/api_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new AoAdchunhaDAO();

$msg = '';

$seqno = $_GET["seqno"] ?? $_POST["seqno"];
$hash  = $_GET["hash"] ?? $_POST["hash"];

if (empty($seqno)) {
    $msg = "file seqno empty";
    goto ERR;
}

$file_rs = $dao->selectAoOrderFileInfo(
    $conn,
    ["order_detail_count_file_seqno" => $seqno]
);

//$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$file_path   = $file_rs["file_path"];
$save_name   = $file_rs["save_file_name"];
$origin_name = $file_rs["origin_file_name"];
$size        = $file_rs["size"];

$full_path   = $file_path . DIRECTORY_SEPARATOR  . $save_name;

if (!password_verify($hash, $save_name)) {
    $msg = "hash is not verified";
    goto ERR;
}

if (!is_file($full_path)) {
    $msg = "file not exist";
    goto ERR;
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$origin_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $size");

ob_clean();
flush();
readfile($full_path);

exit;

ERR:
    echo json_encode([
         "success" => "false"
        ,"msg"     => $msg
    ]);
    exit;
