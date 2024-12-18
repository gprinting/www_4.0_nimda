<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$bulletinDAO = new BulletinMngDAO();

$param = array();
$param["start"] = "0";
$param["end"]   = "7";
$result = $bulletinDAO->selectFaqList($conn, $param);

$fp = fopen(NOTICE_HTML, "w+") or die("can't open file");
fwrite($fp, $notice_html);
fclose($fp);

$fp_m = fopen(M_NOTICE_HTML, "w+") or die("can't open file");
fwrite($fp_m, $notice_html);
fclose($fp_m);

$conn->close();
?>
