<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$check = 1;

$overto_seqno = $fb->form("overto_seqno");

//골라담기 이벤트 대표이미지 파일 삭제
$param = array();
$param["table"] = "overto_repre_file";
$param["prk"] = "overto_event_seqno";
$param["prkVal"] = $overto_seqno;
$rs = $eventDAO->deleteData($conn, $param);
if (!$rs) 
    $check = 0;


$param = array();
$param["seqno"] = $overto_seqno;
$result = $eventDAO->selectOvertoDetailSeq($conn, $param);

//골라담기 이벤트 상세에 해당하는 파일 삭제
while($result && !$result->EOF) {

    $detail_seqno = $result->fields["overto_event_detail_seqno"];

    $param = array();
    //골라담기 이벤트 파일 삭제
    $param["table"] = "overto_detail_file";
    $param["prk"] = "overto_event_detail_seqno";
    $param["prkVal"] = $detail_seqno;

    $rs = $eventDAO->deleteData($conn, $param);
    if (!$rs) $check = 0;

    $result->moveNext();
}

//골라담기 이벤트 상세 삭제
$param["table"] = "overto_event_detail";
$param["prk"] = "overto_event_seqno";
$param["prkVal"] = $fb->form("overto_seqno");
    
$result = $eventDAO->deleteData($conn, $param);
if (!$result) $check = 0;

//골라담기 이벤트 그룹 삭제
$param = array();
$param["table"] = "overto_event";
$param["prk"] = "overto_event_seqno";
$param["prkVal"] = $fb->form("overto_seqno");
    
$result = $eventDAO->deleteData($conn, $param);

if (!$result) $check = 0;

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
