<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/oto_inq_mng/OtoInqMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$fileDAO = new FileAttachDAO();
$check = 1;

$conn->StartTrans();
$seqno = $fb->form("seqno");

$param = array();
$param["table"] = "oto_inq_reply";
$param["col"]["cont"] = $fb->form("reply_cont");
$param["col"]["empl_seqno"] = $fb->session("empl_seqno");
$param["col"]["oto_inq_seqno"] = $seqno;
$param["col"]["regi_date"] = date("Y-m-d H:i:s");

$rs = $dao->insertData($conn,$param);

if (!$rs) {
    $check = 0;
}

if($_FILES["upload_file"]) {
//파일 업로드 경로
    $param = array();
    $param["file_path"] = SITE_DEFAULT_OTO_INQ_REPLY_FILE;
    $param["tmp_name"] = $_FILES["upload_file"]["tmp_name"];
    $param["origin_file_name"] = $_FILES["upload_file"]["name"];

//파일을 업로드 한 후 저장된 경로를 리턴한다.
    $rs = $fileDAO->upLoadFile($param);

    $param = array();
    $param["table"] = "oto_inq_reply_file";
    $param["col"]["origin_file_name"] = $_FILES["upload_file"]["name"];
    $param["col"]["save_file_name"] = $rs["save_file_name"];
    $param["col"]["file_path"] = $rs["file_path"];
    $param["col"]["oto_inq_reply_seqno"] = $conn->Insert_ID();

    $rs = $dao->insertData($conn, $param);
}
if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "oto_inq";
$param["col"]["answ_yn"] = "Y";
$param["prk"] = "oto_inq_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn,$param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
