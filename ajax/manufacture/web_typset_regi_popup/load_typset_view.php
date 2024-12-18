<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$fileDAO = new FileAttachDAO();

$page_order_detail_brochure_seqno = $fb->form("page_order_detail_brochure_seqno");
$typset_num = $fb->form("typset_num");
$state_arr = $fb->session("state_arr");
$option_html = "\n<option value=\"%s\" %s>%s</option>";
$pic_html = "";

if ($typset_num) {

    $param = array();
    $param["table"] = "brochure_typset";
    $param["col"] = "beforeside_tmpt, beforeside_spc_tmpt, after_list,
        aftside_tmpt, aftside_spc_tmpt, honggak_yn, print_amt, opt_list,
        print_amt_unit, prdt_page, prdt_page_dvs, dlvrboard, memo,
        typset_name, affil, subpaper, wid_size, vert_size, 
        empl_seqno, specialty_items, brochure_typset_seqno";
    $param["where"]["typset_num"] = $typset_num;

    $rs = $dao->selectData($conn, $param);

    $empl_seqno = $rs->fields["empl_seqno"];
    $specialty_items = $rs->fields["specialty_items"];

    $specialty_item_arr = explode(" ", $specialty_items);

    $param = array();
    $param["table"] = "brochure_typset_preview_file";
    $param["col"] = "file_path, save_file_name";
    $param["where"]["brochure_typset_seqno"] = $rs->fields["brochure_typset_seqno"];

    $img_html  = "<li data-thumb=\"%s\" width=\"500px\">";
    $img_html .= "<img src=\"%s\" style=\"border-right: 1px dotted #ddd;\" width=\"500px\"/></li>";

    $picture_rs = $dao->selectData($conn, $param);
    while ($picture_rs && !$picture_rs->EOF) {
        $file_path = $picture_rs->fields["file_path"];
        $file_name = $picture_rs->fields["save_file_name"];

        $full_path = $file_path . $file_name;
        $chk_path = INC_PATH . $full_path;

        if (is_file($chk_path) === false) {
            $full_path = NO_IMAGE;
            $pic_html .= sprintf($img_html, $full_path, $full_path); 
        } else {

            $temp = explode('.', $file_name);
            $ext = strtolower($temp[1]);

            $param = array();
            $param["fs"] = $full_path;
            $param["req_width"] = "300";
            $param["req_height"] = "225";

            $pic = $fileDAO->makeThumbnail($param);

            $pic_html .= sprintf($img_html, $file_path . $temp[0] . "_300_225." . $ext, $full_path); 
        }
        $picture_rs->moveNext();
    }

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "name";
    $param["where"]["empl_seqno"] = $empl_seqno;

    $empl_name = $dao->selectData($conn, $param)->fields["name"];

    $honggak_y = "";
    $honggak_n = "";
    if ($rs->fields["honggak_yn"] == "Y") {
        $honggak_y = "checked";
    } else {
        $honggak_n = "checked";
    }

    $board_info = $rs->fields["board_info"];

    $beforeside_tmpt = 0;
    if ($rs->fields["beforeside_tmpt"]) {
        $beforeside_tmpt = $rs->fields["beforeside_tmpt"];
    }

    $beforeside_spc_tmpt = 0;
    if ($rs->fields["beforeside_spc_tmpt"]) {
        $beforeside_spc_tmpt = $rs->fields["beforeside_spc_tmpt"];
    }

    $aftside_tmpt = 0;
    if ($rs->fields["aftside_tmpt"]) {
        $aftside_tmpt = $rs->fields["aftside_tmpt"];
    }

    $aftside_spc_tmpt = 0;
    if ($rs->fields["aftside_spc_tmpt"]) {
        $aftside_spc_tmpt = $rs->fields["aftside_spc_tmpt"];
    }

    $param = array();
    $param["beforeside_tmpt"] = $beforeside_tmpt;
    $param["beforeside_spc_tmpt"] = $beforeside_spc_tmpt;
    $param["aftside_tmpt"] = $aftside_tmpt;
    $param["aftside_spc_tmpt"] = $aftside_spc_tmpt;
    $param["honggak_y"] = $honggak_y;
    $param["honggak_n"] = $honggak_n;
    $param["print_amt"] = $rs->fields["print_amt"];
    $param["print_amt_unit"] = $rs->fields["print_amt_unit"];
    $param["prdt_page"] = $rs->fields["prdt_page"];
    $param["prdt_page_dvs"] = $rs->fields["prdt_page_dvs"];
    $param["memo"] = $rs->fields["memo"];
    $param["typset_name"] = $rs->fields["typset_name"];
    $param["wid_size"] = $rs->fields["wid_size"];
    $param["vert_size"] = $rs->fields["vert_size"];
    $param["after_list"] = $rs->fields["after_list"];
    $param["opt_list"] = $rs->fields["opt_list"];
    $param["empl_name"] = $empl_name;
    $param["board_info_html"] = $board_info_html;
    $param["pic"] = $pic_html;

    //배송판 구분
    $check = "";
    $param["dlvrboard"] = "";
    foreach (DLVRBOARD_DVS as $val) {
        if ($rs->fields["dlvrboard"] == $val) {
            $check = "checked";
        }
        $param["dlvrboard"] .= sprintf($option_html, $val, $check, $val);
    }

    //계열
    $check = "";
    $param["affil"] = "";
    foreach (AFFIL as $val) {
        if ($rs->fields["affil"] == $val) {
            $check = "checked";
        }
        $param["affil"] .= sprintf($option_html, $val, $check, $val);
    }

    //절수
    $check = "";
    $param["subpaper"] = "";
    foreach (SUBPAPER as $val) {
        if ($rs->fields["subpaper"] == $val) {
            $check = "checked";
        }
        $param["subpaper"] .= sprintf($option_html, $val, $check, $val);
    }

    $opt_ck = array();
    foreach ($specialty_item_arr as $val) {
        if ($val == "당일판") {
            $opt_ck[1] = "checked";

        } else if ($val == "사고") {
            $opt_ck[2] = "checked";

        } else if ($val == "A판") {
            $opt_ck[3] = "checked";

        } else if ($val == "긴급") {
            $opt_ck[4] = "checked";

        } else if ($val == "재단주의") {
            $opt_ck[5] = "checked";

        } else if ($val == "견본") {
            $opt_ck[6] = "checked";

        } else if ($val == "베다") {
            $opt_ck[7] = "checked";

        } else if ($val == "감리") {
            $opt_ck[8] = "checked";

        }
    }

    $param["specialty_html"] = <<<HTML
        <input type="checkbox" value="당일판" name="opt" id="opt1" $opt_ck[1]><label class="fs14">당일판</label>
        <input type="checkbox" value="사고" name="opt" id="opt2" $opt_ck[2]><label class="fs14">사고</label>
        <input type="checkbox" value="A판" name="opt" id="opt3" $opt_ck[3]><label class="fs14">A판</label>
        <input type="checkbox" value="긴급" name="opt" id="opt4" $opt_ck[4]><label class="fs14">긴급</label>
        <br />
        <label class="fix_width104"></label>
        <input type="checkbox" value="재단주의" name="opt" id="opt5" $opt_ck[5]><label class="fs14">재단주의</label>
        <input type="checkbox" value="견본" name="opt" id="opt6" $opt_ck[6]><label class="fs14">견본</label>
        <input type="checkbox" value="베다" name="opt" id="opt7" $opt_ck[7]><label class="fs14">베다</label>
        <input type="checkbox" value="감리" name="opt" id="opt8" $opt_ck[8]><label class="fs14">감리</label>

HTML;

} else {

    $param = array();
    $param["table"] = "page_order_detail_brochure";
    $param["col"] = "page, order_detail_dvs_num";
    $param["where"]["page_order_detail_brochure_seqno"] 
        = $page_order_detail_brochure_seqno;

    $rs = $dao->selectData($conn, $param);

    $page = $rs->fields["page"];
    $order_detail_dvs_num = $rs->fields["order_detail_dvs_num"];

    $param = array();
    $param["table"] = "order_detail_brochure";
    $param["col"] = "order_common_seqno";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

    $order_common_seqno = $dao->selectData($conn, $param)->fields["order_common_seqno"];

    $param = array();
    $param["table"] = "order_after_history";
    $param["col"] = "after_name";
    $param["where"]["order_detail_dvs_num"] = $order_detail_dvs_num;

    $rs = $dao->selectData($conn, $param);

    $i = 1;
    $after_list = "";
    while ($rs && !$rs->EOF) {

        if ($i == 1) {
            $after_list .= $rs->fields["after_name"];
        } else {
            $after_list .= ", " . $rs->fields["after_name"];
        }

        $i++;
        $rs->moveNext();
    }

    $param = array();
    $param["table"] = "order_opt_history";
    $param["col"] = "opt_name";
    $param["where"]["order_common_seqno"] = $order_common_seqno;

    $rs = $dao->selectData($conn, $param);

    $i = 1;
    $opt_list = "";
    while ($rs && !$rs->EOF) {

        if ($i == 1) {
            $opt_list .= $rs->fields["opt_name"];
        } else {
            $opt_list .= ", " . $rs->fields["opt_name"];
        }

        $i++;
        $rs->moveNext();
    }

    $param = array();
    $param["empl_name"] = $fb->session("name");
    $param["prdt_page"] = $page;
    $param["prdt_page_dvs"] = "P";
    $param["honggak_y"] = "checked";
    $param["honggak_n"] = "";
    $param["after_list"] = $after_list;
    $param["opt_list"] = $opt_list;

    //계열
    $param["affil"] = "";
    foreach (AFFIL as $val) {
        $param["affil"] .= sprintf($option_html, $val, "", $val);
    }

    //절수
    $param["subpaper"] = "";
    foreach (SUBPAPER as $val) {
        $param["subpaper"] .= sprintf($option_html, $val, "", $val);
    }

    //배송판 구분
    $param["dlvrboard"] = "";
    foreach (DLVRBOARD_DVS as $val) {
        $param["dlvrboard"] .= sprintf($option_html, $val, "", $val);
    }

    $param["specialty_html"] = <<<HTML
        <input type="checkbox" value="당일판" name="opt" id="opt1"><label class="fs14">당일판</label>
        <input type="checkbox" value="사고" name="opt" id="opt2"><label class="fs14">사고</label>
        <input type="checkbox" value="A판" name="opt" id="opt3"><label class="fs14">A판</label>
        <input type="checkbox" value="긴급" name="opt" id="opt4"><label class="fs14">긴급</label>
        <br />
        <label class="fix_width104"></label>
        <input type="checkbox" value="재단주의" name="opt" id="opt5"><label class="fs14">재단주의</label>
        <input type="checkbox" value="견본" name="opt" id="opt6"><label class="fs14">견본</label>
        <input type="checkbox" value="베다" name="opt" id="opt7"><label class="fs14">베다</label>
        <input type="checkbox" value="감리" name="opt" id="opt8"><label class="fs14">감리</label>
HTML;
}

echo getTypsetView($param);
$conn->close();
?>
