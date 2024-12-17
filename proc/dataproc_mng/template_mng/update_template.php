<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/file/FileAttachDAO.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/TemplateInfoMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/html/nimda/dataproc_mng/set/TemplateMngHTML.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$fileDAO = new FileAttachDAO();
$dao = new TemplateInfoMngDAO();

$err_msg = '';

$fb = $fb->getForm();

$seqno         = $fb["seqno"];
$cate_sortcode = $fb["cate_sortcode"];
$cate_name     = $fb["cate_name"];
$uniq_num      = $fb["uniq_num"];
$stan_name     = $fb["stan_name"];
$cut_wid_size   = $fb["cut_wid_size"];
$cut_vert_size  = $fb["cut_vert_size"];
$work_wid_size  = $fb["work_wid_size"];
$work_vert_size = $fb["work_vert_size"];

$ai_file  = $_FILES["ai_file"];
$eps_file = $_FILES["eps_file"];
$cdr_file = $_FILES["cdr_file"];
$sit_file = $_FILES["sit_file"];

$dest_path = $fileDAO->getFilePath(SITE_DEFAULT_CATE_TEMPLATE_FILE);
if (!$fileDAO->chkPath($dest_path)) {
    goto FILE_DIR_ERR;
}
$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;
$dest_abs_path = $base_path . $dest_path . '/';

$ai_file_name  = $fileDAO->getUniqueNm($dest_abs_path);
$eps_file_name = $fileDAO->getUniqueNm($dest_abs_path);
$cdr_file_name = $fileDAO->getUniqueNm($dest_abs_path);
$sit_file_name = $fileDAO->getUniqueNm($dest_abs_path);

$param = array();
$param["cate_template_seqno"] = $seqno;
$param["uniq_num"]      = $uniq_num;
$param["stan_name"]     = $stan_name;
$param["cut_wid_size"]   = $cut_wid_size;
$param["cut_vert_size"]  = $cut_vert_size;
$param["work_wid_size"]  = $work_wid_size;
$param["work_vert_size"] = $work_vert_size;

// 기존 파일정보 검색
$file_rs = $dao->selectCateTemplateFileInfo($conn, $param)->fields;

if (!empty($ai_file)) {
    if (!empty($file_rs["ai_save_file_name"])) {
        $path = $base_path .
                $file_rs["ai_file_path"] .
                $file_rs["ai_save_file_name"];

        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    $chk_ret = $fileDAO->checkUploadFiles($_FILES, "ai_file");
    if (!$chk_ret) {
        goto FILE_ERR;
    }

    if (!moveFile($ai_file, $dest_abs_path . $ai_file_name)) {
        goto FILE_MOVE_ERR;
    }

    $ai_org_file_name  = $ai_file["name"];
    $param["ai_file_path"]  = $dest_path;
    $param["ai_origin_file_name"]  = $ai_org_file_name;
    $param["ai_save_file_name"]  = $ai_file_name;
}

if (!empty($eps_file)) {
    if (!empty($file_rs["eps_save_file_name"])) {
        $path = $base_path .
                $file_rs["eps_file_path"] .
                $file_rs["eps_save_file_name"];

        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    $chk_ret = $fileDAO->checkUploadFiles($_FILES, "eps_file");
    if (!$chk_ret) {
        goto FILE_ERR;
    }

    if (!moveFile($eps_file, $dest_abs_path . $eps_file_name)) {
        goto FILE_MOVE_ERR;
    }

    $eps_org_file_name = $eps_file["name"];
    $param["eps_file_path"] = $dest_path;
    $param["eps_origin_file_name"]  = $eps_org_file_name;
    $param["eps_save_file_name"] = $eps_file_name;
}

if (!empty($cdr_file)) {
    if (!empty($file_rs["cdr_save_file_name"])) {
        $path = $base_path .
                $file_rs["cdr_file_path"] .
                $file_rs["cdr_save_file_name"];

        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    $chk_ret = $fileDAO->checkUploadFiles($_FILES, "cdr_file");
    if (!$chk_ret) {
        goto FILE_ERR;
    }

    if (!moveFile($cdr_file, $dest_abs_path . $cdr_file_name)) {
        goto FILE_MOVE_ERR;
    }

    $cdr_org_file_name = $cdr_file["name"];
    $param["cdr_file_path"] = $dest_path;
    $param["cdr_origin_file_name"]  = $cdr_org_file_name;
    $param["cdr_save_file_name"] = $cdr_file_name;
}

if (!empty($sit_file)) {
    if (!empty($file_rs["sit_save_file_name"])) {
        $path = $base_path .
                $file_rs["sit_file_path"] .
                $file_rs["sit_save_file_name"];
        echo $path;

        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    $chk_ret = $fileDAO->checkUploadFiles($_FILES, "sit_file");
    if (!$chk_ret) {
        goto FILE_ERR;
    }

    if (!moveFile($sit_file, $dest_abs_path . $sit_file_name)) {
        goto FILE_MOVE_ERR;
    }

    $sit_org_file_name = $sit_file["name"];
    $param["sit_file_path"] = $dest_path;
    $param["sit_origin_file_name"]  = $sit_org_file_name;
    $param["sit_save_file_name"] = $sit_file_name;
}

$conn->StartTrans();
$ret = $dao->updateCateTemplate($conn, $param);

if (!$ret) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto DATA_ERR;
}
unset($param);

// DB에 데이터 입력 후 해당 정보를 카테고리별로 html 생성
// 생성된 html 파일은 각 상품페이지 주문에서 TPH_I로 include 시킨다
$param["cate_sortcode"] = $cate_sortcode;
$rs = $dao->selectCateTemplateInfo($conn, $param);
unset($param);

$param["cate_name"] = $cate_name;
$html = makeTemplatePopHtml($rs, $param);
$dest = TEMPLATE_POPUP . DIRECTORY_SEPARATOR . $cate_sortcode . ".html";
if (!$util->writeHtmlFile($dest, $html)) {
    $conn->FailTrans();
    $conn->RollbackTrans();
    goto FILE_MAKE_ERR;
}

goto FIN;

DUP_ERR:
    $err_msg = "중복된 정보입니다.";
    goto FIN;
FILE_DEL_ERR:
    $err_msg = "기존파일 삭제에 실패했습니다.";
    goto FIN;
FILE_ERR:
    $err_msg = "파일이 제대로 업로드되지 않았습니다.";
    goto FIN;
FILE_MOVE_ERR:
    $err_msg = "파일을 업로드 했으나 파일이동에 실패했습니다.";
    goto FIN;
FILE_DIR_ERR:
    $err_msg = "파일을 이동할 디렉토리 생성에 실패했습니다.";
    goto FIN;
DATA_ERR:
    $err_msg = "데이터 입력에 실패했습니다.";
    goto FIN;
FILE_MAKE_ERR:
    $err_msg = "팝업파일 생성에 실패했습니다.";
    goto FIN;
FIN:
    $conn->CompleteTrans();
    echo $err_msg;
    $conn->Close();
    exit;

/******************************************************************************
 ******************** 함수 영역
 ******************************************************************************/

function moveFile($file, $dest_path) {
    if (!move_uploaded_file($file["tmp_name"], $dest_path)) {
        return false;
    }

    return true;
}
?>
