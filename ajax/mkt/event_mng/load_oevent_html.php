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

/*
    여기 수정하는 경우에는
    /home/dprinting/nimda/engine/oevent_update.inc
    파일도 수정 필요

    수정후
    /home/dprinting/nimda/engine/stopDaemon.sh 
    /home/dprinting/nimda/engine/startDaemon.sh 
    스크립트 실행 필요
*/
$result = $eventDAO->selectOeventHtml($conn);

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

$oevent_html = makeOeventHtml($result);

$fp = fopen(OEVENT_HTML, "w+") or die("can't open file");
fwrite($fp, $oevent_html);
fclose($fp);

$conn->close();
?>
