<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$bulletinDAO = new BulletinMngDAO();
$fileDAO = new FileAttachDAO();

//팝업 관리 수정
$param = array();
$param["table"] = "popup_admin";
//팝업 제목
$param["col"]["name"] = $fb->form("popup_name");
//사용 여부
$param["col"]["use_yn"] = $fb->form("use_yn");
//게시 시작 일자
$param["col"]["post_start_date"] = $fb->form("start_date");
//게시 종료 일자
$param["col"]["post_end_date"] = $fb->form("end_date");
//게시 시작 시간
$param["col"]["start_hour"] = 
    $fb->form("start_hour") . ":" . $fb->form("start_min") . ":00";
//게시 종료 시간
$param["col"]["end_hour"] = 
    $fb->form("end_hour") . ":" . $fb->form("end_min") . ":00";
//팝업 가로 사이즈
$param["col"]["wid_size"] = $fb->form("wid_size");
//팝업 세로 사이즈
$param["col"]["vert_size"] = $fb->form("vert_size");

//팝업 파일
if ($fb->form("upload_file")) {

    $f_param = array();
    //파일 업로드 경로
    $f_param["file_path"] = SITE_DEFAULT_POPUP_FILE;
    $f_param["tmp_name"] = $_FILES["upload_btn"]["tmp_name"];
    $f_param["origin_file_name"] = $_FILES["upload_btn"]["name"];

    //파일을 업로드 한 후 저장된 경로를 리턴한다.
    $f_result= $fileDAO->upLoadFile($f_param);

    //팝업 파일 수정
    $param["col"]["origin_file_name"] = $_FILES["upload_btn"]["name"];
    $param["col"]["save_file_name"] = $f_result["save_file_name"];
    $param["col"]["file_path"] = $f_result["file_path"];

}

//링크될 url 주소
$param["col"]["url_addr"] = $fb->form("url_addr");
//타겟 여부
$param["col"]["target_yn"] = $fb->form("target_yn");

$param["prk"] = "popup_admin_seqno";
$param["prkVal"] = $fb->form("popup_seq");

$result = $bulletinDAO->updateData($conn, $param);

if ($result) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
