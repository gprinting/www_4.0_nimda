<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/ErpCommonUtil.inc');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new ErpCommonUtil();
$fb = new FormBean();
$dao = new ClaimListDAO();

$param = array();
$param["table"] = "order_claim";
$param["col"] = "sample_file_path, sample_save_file_name, sample_origin_file_name";
$param["where"]["order_claim_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$file_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE . 
             $rs->fields["sample_file_path"] . $rs->fields["sample_save_file_name"];
$file_size = filesize($file_path);

if (!is_file($file_path)) {
    $util->error('파일이 존재 하지 않습니다.');
}

$down_file_name = $rs->fields["sample_origin_file_name"];
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
