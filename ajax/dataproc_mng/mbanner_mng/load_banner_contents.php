<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/dataproc_mng/mbanner_mng/MbannerMngDOC.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new NimdaCommonDAO();

$count = $fb->form("count");

$html = "";
for ($i=1;$i<=$count;$i++) {

    $rs_param = array();
    $rs_param["table"] = "main_banner";
    $rs_param["col"] = "dvs, save_file_name, 
        origin_file_name, url_addr, file_path, 
        use_yn, seq, main_banner_seqno";
    $rs_param["where"]["seq"] = $i;

    $rs = $dao->selectData($conn, $rs_param);

    $use_yn = $rs->fields["use_yn"];
    $use_y = "checked";
    $use_n = "";
    if ($use_yn == "Y") {
        $use_y = "checked";
        $use_n = "";
    } else if ($use_yn == "N") {
        $use_y = "";
        $use_n = "checked";
    }
    $dvs = $rs->fields["dvs"];
    $dvs1 = "checked";
    $dvs2 = "";
    $dvs3 = "";
    if ($dvs == "공지") {
        $dvs1 = "checked";
        $dvs2 = "";
        $dvs3 = "";
    } else if ($dvs == "상품소개") {
        $dvs1 = "";
        $dvs2 = "checked";
        $dvs3 = "";
    } else if ($dvs == "이벤트") {
        $dvs1 = "";
        $dvs2 = "";
        $dvs3 = "checked";
    }
    
    $param["img_html"] = "<img src=\"" . $rs->fields["file_path"] . $rs->fields["save_file_name"] . "\" width=\"500px\" height=\"255px\">";
    $param["count"] = $i;
    $param["origin_file_name"] = $rs->fields["origin_file_name"];
    $param["url_addr"] = $rs->fields["url_addr"];
    $param["use_y"] = $use_y;
    $param["use_n"] = $use_n;
    $param["dvs1"] = $dvs1;
    $param["dvs2"] = $dvs2;
    $param["dvs3"] = $dvs3;

    if (!$rs->fields["main_banner_seqno"]) {
        $param["seqno"] = "";
    } else {
        $param["seqno"] = $rs->fields["main_banner_seqno"];
    }

    $html .= setContentHtml($param);
}

echo $html;
?>
