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
//FAQ 일련번호
$faq_seqno = $fb->form("faq_seq");

//FAQ 수정일때
if ($faq_seqno) {

    //FAQ
    $param = array();
    $param["table"] = "faq";
    $param["col"] = "title, cont, sort";
    $param["where"]["faq_seqno"] = $faq_seqno;
    $result = $bulletinDAO->selectData($conn, $param);

    //FAQ 파일
    $f_param = array();
    $f_param["table"] = "faq_file";
    $f_param["col"] = "faq_file_seqno, origin_file_name, faq_seqno, file_path";
    $f_param["where"]["faq_seqno"] = $faq_seqno;
    $f_result = $bulletinDAO->selectData($conn, $f_param);

    if ($f_result->recordCount() > 0) {
        $file_html = getFaqFileHtml($f_result);
    } else {
        $file_html = "<br/><br/>";
    }

    //파라미터 셋팅
    $param = array();
    $param["title"] = $result->fields["title"];
    $param["faq_seqno"] = $faq_seqno;
    $param["file_html"] = $file_html;
    $cont = $result->fields["cont"];
    $sort = $result->fields["sort"];

//FAQ 등록일때
} else {

    //파라미터 셋팅
    $param = array();
    $param["del_btn"] = "style=\"display:none\"";

}

echo getFaqSetHtml($param) . "♪" . $cont . "♪" . $sort;
$conn->close();
?>
