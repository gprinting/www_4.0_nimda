<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/TemplateInfoMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/html/nimda/dataproc_mng/set/TemplateMngHTML.inc");
include_once(INC_PATH . "/common_define/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new TemplateInfoMngDAO();

$err_msg = '';

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$cate_name     = $fb["cate_name"];
$seqno         = $fb["seqno"];
$dvs           = $fb["dvs"];

$param = array();
$param["cate_template_seqno"] = $seqno;

// 기존 파일정보 검색
$file_rs = $dao->selectCateTemplateFileInfo($conn, $param)->fields;

$base_path = $_SERVER["SiteHome"] . SITE_NET_DRIVE;

$conn->StartTrans();
if (empty($dvs)) {
    $path = $base_path .
            $file_rs["ai_file_path"] .
            $file_rs["ai_save_file_name"];
    $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
    if (!$file_ret) {
        goto FILE_DEL_ERR;
    }
    $path = $base_path .
            $file_rs["eps_file_path"] .
            $file_rs["eps_save_file_name"];
    $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
    if (!$file_ret) {
        goto FILE_DEL_ERR;
    }
    $path = $base_path .
            $file_rs["cdr_file_path"] .
            $file_rs["cdr_save_file_name"];
    $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
    if (!$file_ret) {
        goto FILE_DEL_ERR;
    }
    $path = $base_path .
            $file_rs["sit_file_path"] .
            $file_rs["sit_save_file_name"];
    $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
    if (!$file_ret) {
        goto FILE_DEL_ERR;
    }

    $ret = $dao->deleteCateTemplate($conn, $seqno);

    if (!$ret) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto DEL_ERR;
    }
    unset($param);
} else {
    $param["ai_file_path"]        = '';
    $param["ai_origin_file_name"] = '';
    $param["ai_save_file_name"]   = '';

    $param["eps_file_path"]        = '';
    $param["eps_origin_file_name"] = '';
    $param["eps_save_file_name"]   = '';

    $param["cdr_file_path"]        = '';
    $param["cdr_origin_file_name"] = '';
    $param["cdr_save_file_name"]   = '';

    $param["sit_file_path"]        = '';
    $param["sit_origin_file_name"] = '';
    $param["sit_save_file_name"]   = '';

    if ($dvs === "ai" && !empty($file_rs["ai_save_file_name"])) {
        $path = $base_path .
                $file_rs["ai_file_path"] .
                $file_rs["ai_save_file_name"];
        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    if ($dvs === "eps" && !empty($file_rs["eps_save_file_name"])) {
        $path = $base_path .
                $file_rs["eps_file_path"] .
                $file_rs["eps_save_file_name"];
        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    if ($dvs === "cdr" && !empty($file_rs["cdr_save_file_name"])) {
        $path = $base_path .
                $file_rs["cdr_file_path"] .
                $file_rs["cdr_save_file_name"];
        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    if ($dvs === "sit" && !empty($file_rs["sit_save_file_name"])) {
        $path = $base_path .
                $file_rs["sit_file_path"] .
                $file_rs["sit_save_file_name"];
        $file_ret = is_file($path) ? (@unlink($path) ? true : false) : true;
        if (!$file_ret) {
            goto FILE_DEL_ERR;
        }
    }

    $ret = $dao->updateCateTemplateFileInfo($conn, $param);
    if (!$ret) {
        $conn->FailTrans();
        $conn->RollbackTrans();
        goto DEL_ERR;
    }
    unset($param);
}

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

FILE_DEL_ERR:
    $err_msg = "파일 삭제에 실패했습니다.";
    goto FIN;
DEL_ERR:
    $err_msg = "템플릿 파일정보 삭제에 실패했습니다.";
    goto FIN;
FILE_MAKE_ERR:
    $err_msg = "팝업파일 생성에 실패했습니다.";
    goto FIN;
FIN:
    $conn->CompleteTrans();
    echo $err_msg;
    $conn->Close();
    exit;
?>
