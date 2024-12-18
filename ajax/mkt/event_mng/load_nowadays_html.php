<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$fileDAO = new FileAttachDAO();

$result = $eventDAO->selectNowadaysHtml($conn);

//파일 썸네일 추가
while ($result && !$result->EOF) {
    $param = array();
    $param["fs"] = $result->fields["file_path"] . $result->fields["save_file_name"];
    $param["req_width"] = "300";
    $param["req_height"] = "300";

    $fileDAO->makeThumbnail($param);
    $result->moveNext();
}
$result->moveFirst();

$nowadays_html = makeNowADaysHtml($result);

$fp = fopen(NOWADAYS_HTML, "w+") or die("can't open file");
fwrite($fp, $nowadays_html);
fclose($fp);

$conn->close();
?>
