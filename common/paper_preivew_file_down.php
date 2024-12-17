<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/ErpCommonUtil.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/PaperInfoMngDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new PaperInfoMngDAO();

$param = array();
$param["paper_preview_seqno"] = $fb->form("seqno");

$rs = $dao->selectPaperPreviewInfo($conn, $param);

$file_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE .
             $rs->fields["file_path"] . '/' . $rs->fields["save_file_name"];
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

flush();
readfile($file_path);
?>
