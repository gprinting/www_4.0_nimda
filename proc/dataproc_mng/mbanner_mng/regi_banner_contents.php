<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new NimdaCommonDAO();
$fileDAO = new FileAttachDAO();
$util = new CommonUtil();
$check = 1;

$seqno = $fb->form("seqno");
$seq = $fb->form("seq");
$use_yn_field = "use_yn" . $seq;
$use_yn = $fb->form($use_yn_field);
$dvs_field = "dvs" . $seq;
$dvs = $fb->form($dvs_field);
$dvs_field = "banner_dvs" . $seq;
$dvs = $fb->form($dvs_field);
$url_addr_field = "url_addr" . $seq;
$url_addr = $fb->form($url_addr_field);
$upload_btn_field = "upload_btn" . $seq;

$param = array();
$param["table"] = "main_banner";
$param["col"]["use_yn"] = $use_yn;
$param["col"]["dvs"] = $dvs;
$param["col"]["url_addr"] = $url_addr;
$param["col"]["seq"] = $seq;

if ($_FILES[$upload_btn_field]["tmp_name"]) {
    $f_param = array();
    //파일 업로드 경로
    $f_param["file_path"] = SITE_DEFAULT_BANNER_FILE;
    $f_param["tmp_name"] = $_FILES[$upload_btn_field]["tmp_name"];
    $f_param["origin_file_name"] = $_FILES[$upload_btn_field]["name"];

    //파일을 업로드 한 후 저장된 경로를 리턴한다.
    $f_result= $fileDAO->upLoadFile($f_param);

    if (!$f_result) {
        $check = 0;
    } 

    $file_path = $f_result["file_path"];
    $file_name = $f_result["save_file_name"];
    $origin_file_name = $_FILES[$upload_btn_field]["name"];

    //팝업 파일 수정
    $param["col"]["origin_file_name"] = $origin_file_name;
    $param["col"]["save_file_name"] = $file_name;
    $param["col"]["file_path"] = $file_path;
} else {

    $rs_param = array();
    $rs_param["table"] = "main_banner";
    $rs_param["col"] = "origin_file_name, save_file_name, file_path";
    $rs_param["where"]["main_banner_seqno"] = $seqno;

    $rs = $dao->selectData($conn, $rs_param);

    $file_path = $rs->fields["file_path"];
    $file_name = $rs->fields["save_file_name"];
    $origin_file_name = $rs->fields["origin_file_name"];
}

if ($seqno) {
    $param["col"]["modi_date"] = date("Y-m-d H:i:s");
    $param["prk"] = "main_banner_seqno";
    $param["prkVal"] = $seqno;

    $rs = $dao->updateData($conn, $param);
} else {
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $rs = $dao->insertData($conn, $param);
    $seqno = $conn->insert_ID();
}

if (!$rs) {
    $check = 0;
} 

$file_path = $util->getAliasAttachPath($file_path);

//2016-10-12
echo $check . "♪" . $file_path . '/' . $file_name . "♪" . $origin_file_name . "♪" . $seqno;
$conn->CompleteTrans();
$conn->close();
?>
