<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$fileDAO = new FileAttachDAO();

$param = array();
$param["table"] = "overto_event";
//골라담기 이벤트명
$param["col"]["name"] = $fb->form("event_name");
//사용 여부
$param["col"]["use_yn"] = $fb->form("use_yn");
//전체 주문 금액
$param["col"]["tot_order_price"] = $fb->form("order_price");
//할인 요율
$param["col"]["sale_rate"] = $fb->form("sale_rate");
//판매채널 일련번호
$param["col"]["cpn_admin_seqno"] = $fb->form("sell_site");


//골라담기 이벤트 그룹 수정
if ($fb->form("overto_seqno")) {

    //골라담기 이벤트 그룹 일련번호
    $overto_seqno = $fb->form("overto_seqno");
    $param["prk"] = "overto_event_seqno";
    $param["prkVal"] = $overto_seqno;
    
    $result = $eventDAO->updateData($conn, $param);

    if ($fb->form("repre_upload_file")) {

        //골라담기 파일 삭제
        $param = array();
        $param["table"] = "overto_repre_file";
        $param["prk"] = "overto_event_seqno";
        $param["prkVal"] = $fb->form("overto_seqno");

        $result = $eventDAO->deleteData($conn, $param);
        if (!$result) {
            $check = 0;
        }

        //파일 업로드 경로
        $param =  array();
        $param["file_path"] = SITE_DEFAULT_OVERTO_FILE;
        $param["tmp_name"] = $_FILES["repre_upload_btn"]["tmp_name"];
        $param["origin_file_name"] = $_FILES["repre_upload_btn"]["name"];

        //파일을 업로드 한 후 저장된 경로를 리턴한다.
        $result= $fileDAO->upLoadFile($param);

        $param = array();
        $param["table"] = "overto_repre_file";
        $param["col"]["origin_file_name"] = $_FILES["repre_upload_btn"]["name"];
        $param["col"]["save_file_name"] = $result["save_file_name"];
        $param["col"]["file_path"] = $result["file_path"];
        $param["col"]["overto_event_seqno"] = $overto_seqno;

        $result = $eventDAO->insertData($conn, $param);

        if (!$result) {
            $check = 0;
        }

    }

//골라담기 이벤트 그룹 추가
} else {

    $result = $eventDAO->insertData($conn, $param);
    //골라담기 이벤트 그룹 일련번호
    $overto_seqno = $conn->insert_ID();

    if ($fb->form("repre_upload_file")) {

        //파일 업로드 경로
        $param =  array();
        $param["file_path"] = SITE_DEFAULT_OVERTO_FILE;
        $param["tmp_name"] = $_FILES["repre_upload_btn"]["tmp_name"];
        $param["origin_file_name"] = $_FILES["repre_upload_btn"]["name"];

        //파일을 업로드 한 후 저장된 경로를 리턴한다.
        $result= $fileDAO->upLoadFile($param);

        $param = array();
        $param["table"] = "overto_repre_file";
        $param["col"]["origin_file_name"] = $_FILES["repre_upload_btn"]["name"];
        $param["col"]["save_file_name"] = $result["save_file_name"];
        $param["col"]["file_path"] = $result["file_path"];
        $param["col"]["overto_event_seqno"] = $overto_seqno;

        $result = $eventDAO->insertData($conn, $param);

        if (!$result) {
            $check = 0;
        }

    }
}

if ($result) {

    echo "1" . "♪♥♭" . $overto_seqno;

} else {

    echo "2" . "♪♥♭" . "";

}
$conn->CompleteTrans();
$conn->close();
?>
