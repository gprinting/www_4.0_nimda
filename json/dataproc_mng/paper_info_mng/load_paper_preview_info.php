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

$fb = $fb->getForm();

$paper_name  = $fb["paper_name"];
$paper_dvs   = $fb["paper_dvs"];
$paper_color = $fb["paper_color"];

$param = array();
$param["name"]  = $paper_name;
$param["dvs"]   = $paper_dvs;
$param["color"] = $paper_color;

$rs = $dao->selectPaperPreviewInfo($conn, $param);

$json = "{\"seqno\" : \"%s\", \"path\" : \"%s\", \"name\" : \"%s\"}";

if ($rs->EOF) {
    $json = sprintf($json, '', NO_IMAGE, '');
} else{
    $rs = $rs->fields;

    $json = sprintf($json, $rs["paper_preview_seqno"]
                         , $rs["file_path"] . DIRECTORY_SEPARATOR . $rs["save_file_name"]
                         , $rs["origin_file_name"]);
}

echo $json;
?>
