<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$bulletinDAO = new BulletinMngDAO();

$cont = "";
//공지사항 일련번호
$notice_seqno = $fb->form("notice_seq");

//공지사항 수정일때
if ($notice_seqno) {

    //공지사항
    $param = array();
    $param["table"] = "notice";
    $param["col"] = "title, cont, dvs";
    $param["where"]["notice_seqno"] = $notice_seqno;
    $result = $bulletinDAO->selectData($conn, $param);

    //공지사항 파일
    $f_param = array();
    $f_param["table"] = "notice_file";
    $f_param["col"] = "notice_file_seqno, origin_file_name, notice_seqno, file_path";
    $f_param["where"]["notice_seqno"] = $notice_seqno;
    $f_result = $bulletinDAO->selectData($conn, $f_param);

    if ($f_result->recordCount() > 0) {
        $file_html = getFileHtml($f_result);
    } else {
        $file_html = "<br/><br/>";
    }

    //파라미터 셋팅
    $param = array();
    $param["title"] = $result->fields["title"];
    $param["notice_seqno"] = $notice_seqno;
    $param["file_html"] = $file_html;
    $cont = $result->fields["cont"];
    $dvs = $result->fields["dvs"];

//공지사항 등록일때
} else {

    //파라미터 셋팅
    $param = array();
    $param["del_btn"] = "style=\"display:none\"";

}

echo getNoticeSetHtml($param) . "♪" . $cont . "♪" . $dvs;
$conn->close();
?>
