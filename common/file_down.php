<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$param = array();
$param["table"] = "member_certi";
$param["col"] = "file_path, save_file_name, origin_file_name";
$param["where"]["member_certi_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->selectData($conn, $param);

$file_path = INC_PATH . $rs->fields["file_path"] . $rs->fields["save_file_name"];
$file_size = filesize($file_path);

if (!is_file($file_path)) {
    echo "<script>alert('파일이 존재 하지 않습니다.');</script>";
    exit;
}

$down_file_name = $rs->fields["origin_file_name"];
if (isIe()) {
    $down_file_name = utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile($file_path);

/******************************************************************************
//  연산함수 영역
 ******************************************************************************/

/**
 * @brief ie에서 utf-8 파일명 다운로드 받을 때 euc-kr로 인코딩
 *
 * @param $str = 인코딩할 문자열
 *
 * @return 인코딩된 문자열
 */
function utf2euc($str) {
    return iconv("UTF-8", "cp949//IGNORE", $str);
}

/**
 * @brief 현재 브라우저가 ie인지 확인
 *
 * @return ie면 true
 */
function isIe() {
    if(!isset($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
        return true; // IE8
    }
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'Windows NT') !== false) {
        return true; // IE11
    }

    return false;
}
?>
