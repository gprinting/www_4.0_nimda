<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/ErpCommonUtil.inc');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/oto_inq_mng/OtoInqMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new ErpCommonUtil();
$fb = new FormBean();
$dao = new OtoInqMngDAO();

$param = array();
$param["table"] = "oto_inq_reply_file";
$param["col"] = "file_path, save_file_name, origin_file_name";
$param["where"]["oto_inq_reply_file_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

//$file_path = INC_PATH . $rs->fields["file_path"] . $rs->fields["save_file_name"];
$file_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE . 
             $rs->fields["file_path"] . $rs->fields["save_file_name"];
$file_size = filesize($file_path);

if (!is_file($file_path)) {
    $util->error('파일이 존재 하지 않습니다.');
}

$down_file_name = $rs->fields["origin_file_name"];
if ($util->isIe() === true) {
    $down_file_name = $util->utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile($file_path);
?>
