<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/file/FileAttachDAO.inc');
include_once($_SERVER["DOCUMENT_ROOT"] . '/engine/dao/EngineDAO.php');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$fileDAO = new FileAttachDAO();
$engineDAO = new EngineDAO();

$dvs = $fb->form("dvs");
$file_name = getExcelFileName($dvs);

$err_msg = "";
if (!$fileDAO->checkExcelFiles($_FILES, "file")) {
    $err_msg = "엑셀파일이 아닙니다.";
    goto ERR;
}

$temp_path = UPLOAD_PATH . '/' . $file_name;
if (!move_uploaded_file($_FILES["file"]["tmp_name"], $temp_path)) {
    $err_msg = "업로드한 파일이동에 실패했습니다.";
    goto ERR;
}

$param = getEngineParam($dvs, UPLOAD_PATH, $file_name, $fb);

$rs = $engineDAO->selectWork($conn, $param);

if (!$rs->EOF) {
    $err_msg = "이미 등록된 작업입니다.";
    goto ERR;
}

//$conn->debug = 1;

$insert_ret = $engineDAO->insertWork($conn, $param);

if ($insert_ret !== TRUE) {
    echo $insert_ret;
} else {
    echo "\n\nTRUE(insert_ret)/Excel_Upload";
}

$conn->Close();
exit;

ERR :
    echo $err_msg;
    $conn->Close();
    exit;

/******************************************************************************
                                   함수 영역
 *****************************************************************************/

/**
 * @brief 구분에 따라서 엑셀파일명을 반환하는 함수
 *
 * @param $dvs = 구분값
 *
 * @return 엑셀파일명
 */
function getExcelFileName($dvs) {
    /*
    if ($dvs === "prdt_price") {
        return "prdt_price_excel.xlsx";
    } else if ($dvs === "aft_sell_price") {
        return "aft_sell_price_excel.xlsx";
    } else if ($dvs === "opt_sell_price") {
        return "opt_sell_price_excel.xlsx";
    } else if ($dvs === "paper_sell_price") {
        return "prdt_paper_price_excel.xlsx";
    } else if ($dvs === "output_sell_price") {
        return "prdt_output_price_excel.xlsx";
    } else if ($dvs === "print_sell_price") {
        return "prdt_print_price_excel.xlsx";
    } else if ($dvs === "sale_paper_price") {
        return "amt_paper_sale_price.xlsx";
    } else if ($dvs === "paper_pur_price") {
        return "paper_price_excel.xlsx";
    } else if ($dvs === "output_pur_price") {
        return "output_price_excel.xlsx";
    } else if ($dvs === "print_pur_price") {
        return "print_price_excel.xlsx";
    } else if ($dvs === "after_pur_price") {
        return "after_price_excel.xlsx";
    } else if ($dvs === "opt_pur_price") {
        return "option_price_excel.xlsx";
    } else if ($dvs === "member_sale_price") {
        return "member_sale_price_excel.xlsx";
    } else if ($dvs === "aft_member_sale_price") {
        return "aft_member_sale_price_excel.xlsx";
    }
    */

   return uniqid() . ".xlsx";
}

/**
 * @brief 구분에 따라서 엔진 파라미터을 반환하는 함수
 *
 * @param $dvs       = 구분값
 * @param $file_path = 엑셀파일 경로
 * @param $file_name = 엑셀파일명
 * @param $fb        = 폼 빈 객체
 *
 * @return 엔진 파라미터
 */
function getEngineParam($dvs, $file_path, $file_name, $fb) {
    $ret = array();
    $ret["state"] = "STAY";

    if ($dvs === "prdt_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "PLY"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "aft_sell_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "AFTER"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "opt_sell_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "OPTION"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "paper_sell_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "PAPER"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "output_sell_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "OUTPUT"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "print_sell_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "PRINT"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "sale_paper_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "SALE_PAPER"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "paper_pur_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "PUR_PRICE";
        $ret["param"] = sprintf($param_base, "PAPER"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "output_pur_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "PUR_PRICE";
        $ret["param"] = sprintf($param_base, "OUTPUT"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "print_pur_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "PUR_PRICE";
        $ret["param"] = sprintf($param_base, "PRINT"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "after_pur_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "PUR_PRICE";
        $ret["param"] = sprintf($param_base, "AFTER"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "opt_pur_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "PUR_PRICE";
        $ret["param"] = sprintf($param_base, "OPTION"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "member_sale_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "SALE_MEMBER"
                                           , $file_path
                                           , $file_name);
        return $ret;
    } else if ($dvs === "aft_member_sale_price") {
        $param_base = "%s!%s!%s";

        $ret["dvs"]   = "SELL_PRICE";
        $ret["param"] = sprintf($param_base, "SALE_AFT"
                                           , $file_path
                                           , $file_name);
    }

    return $ret;
}
?>
