<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$util = new CommonUtil();

$seqno = $fb->form("seqno");

$param = array();
$param["seqno"] = $fb->form("seqno");
$rs = $dao->selectBrochureTypsetDirectionsView($conn, $param);

$param = array();
$param["table"] = "brochure_typset_file";
$param["col"] = "brochure_typset_file_seqno, origin_file_name, size, save_file_name";
$param["where"]["brochure_typset_seqno"] = $rs->fields["brochure_typset_seqno"];

$file_rs = $dao->selectData($conn, $param);

$file_html = "";
$html  = "<div class=\"tmp\" id=\"%s\" style=\"margin-left: 102px;\">";
$html .= "\n<a href=\"/common/brochure_typset_file_down.inc?seqno=" . $file_rs->fields["brochure_typset_file_seqno"] . "\"> %s (";
$html .= "\n%s";
$html .= "\n)<b>100%%</b></a>";
$html .= "\n&nbsp;"; 
$html .= "\n<img src=\"/design_template/images/btn_circle_x_red.png\"";
$html .= "\n     file_seqno=\"\"";
$html .= "\n     alt=\"X\"";
$html .= "\n     onclick=\"removeFile('%s', true, 'format_file', '%s');\"";
$html .= "\n     style=\"cursor:pointer;\" /></div>";

while ($file_rs && !$file_rs->EOF) {

    $tmp = explode(".", $file_rs->fields["save_file_name"]);
    $tmp[0] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $tmp[0]); 
    $file_html .= sprintf($html, $tmp[0]
                               , $file_rs->fields["origin_file_name"]
                               , getFileSize($file_rs->fields["size"])
                               , $file_rs->fields["brochure_typset_file_seqno"]
                               , $tmp[0]);
    $file_rs->moveNext();
}

echo $file_html;
$conn->close();

/*******************************************************************************
                                 함수 영역
 ******************************************************************************/

//파일 용량 단위 변환 함수
function getFileSize($size, $float = 0) { 
    $unit = array('byte', 'kb', 'mb', 'gb', 'tb'); 
    for ($L = 0; intval($size / 1024) > 0; $L++, $size/= 1024); 
    if (($float === 0) && (intval($size) != $size)) $float = 2; 
    return round(number_format($size, $float, '.', ',')) .' '. $unit[$L]; 
} 
?>
