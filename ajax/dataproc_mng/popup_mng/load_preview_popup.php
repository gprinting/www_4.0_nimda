<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$bulletinDAO = new BulletinMngDAO();

//팝업 일련번호
$popup_seqno = $fb->form("popup_seq");

//팝업 설정
$param = array();
$param["popup_seqno"] =  $popup_seqno;
$result = $bulletinDAO->selectPopupList($conn, $param);

//파일
$param["popup_file"] = $result->fields["file_path"] . $result->fields["save_file_name"];

$html = getPopupPreviewHtml($param);

echo $html;

$conn->close();
?>
