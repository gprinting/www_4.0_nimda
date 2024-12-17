<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/PaperInfoMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperInfoMngDAO();
$fileDAO = new FileAttachDAO();

$paper_name    = $fb->form("paper_name");
$paper_dvs     = $fb->form("paper_dvs");
$paper_color   = $fb->form("paper_color");
$preview_seqno = $fb->form("preview_seqno");

$err_msg = '';

$file_arr = $_FILES["preview_file"];

$file_ext  = $fileDAO->getExt($file_arr["name"]);
$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;
$save_path = SITE_DEFAULT_PAPER_PREVIEW_FILE;
$save_path = $fileDAO->getFilePath($save_path);

$save_file_name = $fileDAO->getUniqueNm($base_path . $save_path) . '.' . $file_ext;

if (!$fileDAO->chkPath($save_path)) {
    $err_msg = "디렉토리 생성실패";
    goto ERR;
}

$full_path = $base_path . $save_path . '/' . $save_file_name;

if (!move_uploaded_file($file_arr["tmp_name"] , $full_path)) {
    $err_msg = "파일 이동실패";
    goto ERR;
}

if (!empty($preview_seqno)) {
    unset($param);
    $param["paper_preview_seqno"] = $preview_seqno;

    $rs = $dao->selectPaperPreviewInfo($conn, $param);
    $rs = $rs->fields;

    if (!$dao->deletePaperPreview($conn, $param)) {
        $err_msg = "데이터 삭제 실패";
        goto ERR;
    }

    $full_path = $base_path .
                 $rs["file_path"] . '/' .
                 $rs["save_file_name"];

    if (!unlink($full_path)) {
        $err_msg = "본 파일삭제 실패";
        goto ERR;
    }

    $exist_file_arr = explode('.', $rs["save_file_name"]);
    $thumb_path = $base_path .
                  $rs["file_path"] . '/' .
                  $exist_file_arr[0] . "_400_313." .
                  $exist_file_arr[1];

    if (!unlink($thumb_path)) {
        $err_msg = "썸네일 파일삭제 실패";
        goto ERR;
    }
}

// 썸네일 생성
//unset($param);
//$param["fs"] = $save_path . '/' . $save_file_name;
//$param["req_width"] = 400;
//$param["req_height"] = 313;
//$fileDAO->makeThumbnail($param);

unset($param);
$param["paper_name"]       = $paper_name;
$param["paper_dvs"]        = $paper_dvs;
$param["paper_color"]      = $paper_color;
$param["file_path"]        = $save_path;
$param["save_file_name"]   = $save_file_name;
$param["origin_file_name"] = $file_arr["name"];

if (!$dao->insertPaperPreview($conn, $param)) {
    $err_msg = "데이터 입력 실패";
    goto ERR;
}

ERR :
    echo $err_msg;
    exit;
?>
