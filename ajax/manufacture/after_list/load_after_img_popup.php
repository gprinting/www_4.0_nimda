<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/after_mng/AfterListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool(); $conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterListDAO();
$util = new CommonUtil();
$fileDAO = new FileAttachDAO();

if (!$fb->form("seqno")) {
    echo "<script>alert('잘못된 접근입니다.');</script>";
    exit;
}

$seqno = $fb->form("seqno");
$state_arr = $fb->session("state_arr");

//상세보기 출력 발주
$param = array();
$param["seqno"] = $seqno;

$rs = $dao->selectAfterProcessView($conn, $param);

//낱장형(S) / 책자형(B) 여부
$flattyp_dvs = substr($rs->fields["order_detail_dvs_num"], 0, 1);
$order_common_seqno = $rs->fields["order_common_seqno"];

$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "preview_file_path, preview_file_name";
$param["where"]["after_op_seqno"] = $seqno;

$picture_rs = $dao->selectData($conn, $param);

$slider_html = "";

if ($picture_rs->EOF || !picture_rs) {
    $full_path = NO_IMAGE;
    $slider_html .= "\n<li class=\"on\"><img src=\"" . $full_path . "\" style=\"width:540px; height:331.27px;\" alt=\"" . $file_name . "\"></li>";
} else {
    while ($picture_rs && !$picture_rs->EOF) {
        $file_path = $picture_rs->fields["preview_file_path"];
        $file_name = $picture_rs->fields["preview_file_name"];

        $full_path = $file_path . $file_name;
        $chk_path = INC_PATH . $full_path;

        if (is_file($chk_path) === false) {
            $full_path = NO_IMAGE;
        }

        if ($i == 1) {
            $class = "class=\"on\"";
        } else {
            $class = "";
        } 

        $slider_html .= "\n<li " . $class . "><img src=\"" . $full_path . "\" style=\"width:540px; height:331.27px;\" alt=\"" . $file_name . "\"></li>";

        $i++;
        $picture_rs->moveNext();
    }
}
//후공정 미리보기
$pic_html = <<<HTML
        <section class="mainBanner" style="width:540px; margin-right:20px;">
        <nav>
          <button class="prev">
              <img style="width:30px;" src="/design_template/images/mainbanner_nav_prev.png" alt="<">
          </button>
          <button class="next">
              <img style="width:30px;" src="/design_template/images/mainbanner_nav_next.png" alt=">">
          </button>
        <ul style="display:none;"></ul>
        </nav>

        <ul class="list">
            $slider_html
        </ul>
        </section>
HTML;

$html_param["after_pic"] = $pic_html;

$img_html  = "<li data-thumb=\"%s\">";
$img_html .= "<img src=\"%s\" width=\"540px\"/></li>";

//낱장
if ($flattyp_dvs === "S") {
    //주문 상세 일련번호
    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $order_detail_rs = $dao->selectData($conn, $param);
    $class = "";

    while ($order_detail_rs && !$order_detail_rs->EOF) {

        if ($order_detail_rs->fields["order_detail_seqno"]) {

            $param = array();
            $param["table"] = "order_detail_count_file";
            $param["col"] = "order_detail_count_file_seqno";
            $param["where"]["order_detail_seqno"] = $order_detail_rs->fields["order_detail_seqno"];

            $seqno_rs = $dao->selectData($conn, $param);

            while ($seqno_rs && !$seqno_rs->EOF) {

                //주문상세파일
                $param = array();
                $param["table"] = "order_detail_count_preview_file";
                $param["col"] = "preview_file_path, preview_file_name";
                $param["where"]["order_detail_count_file_seqno"] = $seqno_rs->fields["order_detail_count_file_seqno"];

                $picture_rs = $dao->selectData($conn, $param);

                $html_param["pic"] = "";
                while ($picture_rs && !$picture_rs->EOF) {

                    $file_path = $picture_rs->fields["preview_file_path"];
                    $file_name = $picture_rs->fields["preview_file_name"];

                    $chk_path = $file_path . $file_name;
                    $full_path = str_replace(INC_PATH, "", $chk_path);

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

                        $pic = $fileDAO->makeThumbnail($param);

                        $html_param["pic"] .= sprintf($img_html, $file_path . $temp[0] . "_300_225." . $ext, $full_path); 
                    }
                    $picture_rs->moveNext();
                }
                $seqno_rs->moveNext();
            }
        }
        $order_detail_rs->moveNext();
    }
} 

echo getAfterImgPopup($html_param); 
$conn->close();
?>
