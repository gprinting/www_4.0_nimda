<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/TemplateInfoMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TemplateInfoMngDAO();

$param = array();
$param["cate_sortcode"] = $fb->form("cate_sortcode");

$html_form  = "<tr>";
$html_form .=     "<td>%s</td>";
$html_form .=     "<td>%s</td>";
$html_form .=     "<td>%s</td>";
$html_form .=     "<td>%s</td>";
$html_form .=     "<td>";
$html_form .=         "<span onclick=\"downloadTemplate('%s', 'ai');\" style=\"cursor:pointer;\">%s</span>";
$html_form .=     "</td>";
$html_form .=     "<td>";
$html_form .=         "<span onclick=\"downloadTemplate('%s', 'eps');\" style=\"cursor:pointer;\">%s</span>";
$html_form .=     "</td>";
$html_form .=     "<td>";
$html_form .=         "<span onclick=\"downloadTemplate('%s', 'cdr');\" style=\"cursor:pointer;\">%s</span>";
$html_form .=     "</td>";
$html_form .=     "<td>";
$html_form .=         "<span onclick=\"downloadTemplate('%s', 'sit');\" style=\"cursor:pointer;\">%s</span>";
$html_form .=     "</td>";
$html_form .=     "<td>";
$html_form .=         "<button onclick=\"showTemplatePop('%s');\" type=\"button\" class=\"btn btn-xs co_blue\">수정</button>";
$html_form .=     "</td>";
$html_form .= "</tr>";

$html_blank = "<td colspan=\"7\">등록된 템플릿이 없습니다.</td>";

$rs = $dao->selectCateTemplateInfo($conn, $param);

$html = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $seqno = $fields["cate_template_seqno"];

    $html .= sprintf($html_form, $fields["uniq_num"]
                               , $fields["stan_name"]
                               , $fields["cut_size"]
                               , $fields["work_size"]
                               , $seqno
                               , $fields["ai_origin_file_name"]
                               , $seqno
                               , $fields["eps_origin_file_name"]
                               , $seqno
                               , $fields["cdr_origin_file_name"]
                               , $seqno
                               , $fields["sit_origin_file_name"]
                               , $seqno);

    $rs->MoveNext();
}

if (empty($html)) {
    echo $html_blank;
    exit;
}

echo $html;

$conn->Close();
?>
