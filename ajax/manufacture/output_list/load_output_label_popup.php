<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-17
 * Time: 오후 5:51
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OutputListDAO();
$util = new CommonUtil();
$fileDAO = new FileAttachDAO();

if (!$fb->form("seqno")) {
    echo "<script>alert('잘못된 접근입니다.');</script>";
    exit;
}

$seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");
$option_html = "<option value=\"%s\" %s>%s</option>";

//상세보기 출력 발주
$param = array();
$param["sheet_typset_seqno"] = $seqno;
$rs = $dao->selectOutputLabelView($conn, $param);

$img_html  = "<li data-thumb=\"%s\" width=\"884px\">";
$img_html .= "<img src=\"%s\" style=\"border-right: 1px dotted #ddd;\" width=\"884px\"/></li>";

$html_param['downloadurl'] = $dao->selectOriginLabelURL($conn, $param);

$html_param["pic"] = "";
while($rs && !$rs->EOF) {
    $file_path = $rs->fields["file_path"];
    $file_name = $rs->fields["save_file_name"];

    $full_path = $file_path .  $file_name;
    $chk_path = $file_path . $file_name;


    if (is_file($chk_path) === false) {
        $full_path = NO_IMAGE;
        $html_param["pic"] .= sprintf($img_html, $full_path, $full_path);
    } else {
        $temp = explode('.', $file_name);
        $ext = strtolower($temp[1]);

        $param = array();
        $param["fs"] = $full_path;
        $param["req_width"] = "300";
        $param["req_height"] = "225";

        $thumbname_fullpath = $file_path . "/" . $temp[0] . "_300_225." . $ext;

        if(!is_file($thumbname_fullpath)) {
            $pic = $fileDAO->makeThumbnail($param);
        }
        $html_param["pic"] .= sprintf($img_html, str_replace("/home/sitemgr/ndrive", "", $file_path) . "/" . $temp[0] . "_300_225." . $ext, $full_path);
    }
    $rs->moveNext();
}

echo getOutputLabelPopup($html_param);
$conn->close();

?>

