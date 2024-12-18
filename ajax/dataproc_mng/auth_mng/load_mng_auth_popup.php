<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/left_menu.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();


$fb = new FormBean();
$organDAO = new OrganMngDAO();

//직원 일련번호
$empl_seqno = $fb->form("empl_seq");

$param = array();
$param["table"] = "auth_admin_page";
$param["col"] = "page_url, auth_yn";
$param["where"]["empl_seqno"] = $empl_seqno;

$result = $organDAO->selectData($conn, $param);

$param = array();
while ($result && !$result->EOF) {

    $page_url = $result->fields["page_url"];
    $page = explode("/", $page_url);
    $url = explode(".", $page[2]);
    $auth_yn = $result->fields["auth_yn"];

    //권한이 있으면
    if ($auth_yn == "Y") {
        $param[$url[0] . "_y"] = "checked=\"checked\"";
    } else {
        $param[$url[0] . "_n"] = "checked=\"checked\"";
    }

    $result->moveNext();
}

$param["empl_seqno"] = $empl_seqno;

$tbody_html  = "\n<tr class=\"%s\">";
$tbody_html .= "\n    <td>%s</td>";
$tbody_html .= "\n    <td>%s</td>";
$tbody_html .= "\n    <td>%s</td>";
$tbody_html .= "\n    <td><label style=\"cursor: pointer;\"><input type=\"radio\" value=\"Y\" class=\"radio_box\" name=\"%s\" %s>허용</label>";
$tbody_html .= "\n    <label class=\"fix_width10\"> </label>";
$tbody_html .= "\n    <label style=\"cursor: pointer;\"><input type=\"radio\" value=\"N\" class=\"radio_box\" name=\"%s\" %s>허용안함</label></td>";
$tbody_html .= "\n</tr>";

$i = 1;

foreach (TOP_MENU_ARR as $top=>$top_val) {
    foreach (LEFT_MENU_ARR[$top]["sub"] as $mid=>$mid_val) {
        foreach (LEFT_MENU_ARR[$top][$mid] as $btm=>$btm_val) {
            if ($i % 2 == 0) {
                $class = "cellbg";
            } else if ($i % 2 == 1) {
                $class = "";
            }

            $name = $top . "-" . $btm;
            $true = $param[$btm . "_y"];
            $false = $param[$btm . "_n"];

            $return_html .= sprintf($tbody_html, $class, 
                    $i, $top_val, $btm_val, $name, 
                    $true, $name, $false);
            $i++;
        }
    }
}

$param["tbody_html"] = $return_html;

$html = getMngAuthHtml($param);

echo $html;
$conn->close();
?>
