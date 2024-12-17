<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/PaperInfoMngDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperInfoMngDAO();

$err_msg = '';

$preview_seqno = $fb->form("seqno");

$param = array();
$param["paper_preview_seqno"] = $preview_seqno;

$rs = $dao->selectPaperPreviewInfo($conn, $param);
$rs = $rs->fields;

if (!$dao->deletePaperPreview($conn, $param)) {
    $err_msg = "데이터 삭제 실패";
    goto ERR;
}

$full_path = INC_PATH .
             $rs["file_path"] .
             $rs["save_file_name"];

if (!unlink($full_path)) {
    $err_msg = "파일삭제 실패";
    goto ERR;
}

$exist_file_arr = explode('.', $rs["save_file_name"]);
$thumb_path = INC_PATH .
              $rs["file_path"] .
              $exist_file_arr[0] . "_400_313." .
              $exist_file_arr[1];

if (!unlink($thumb_path)) {
    $err_msg = "썸네일 파일삭제 실패";
    goto ERR;
}

ERR :
    echo $err_msg;
    exit;
?>
