<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/CateListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CateListDAO();

$fb = $fb->getForm();

$seqno = $fb["seqno"];
$name  = $fb["name"];
$rate  = $fb["rate"];

$html  = "\n<dl>";
$html .= "\n   <dt class=\"tit\">";
$html .= "\n        <h4>카테고리 회원할인</h4>";
$html .= "\n   </dt>";
$html .= "\n   <dt class=\"cls\">";
$html .= "\n        <button type=\"button\" class=\"btn btn-sm btn-danger fa fa-times\" onclick=\"hideRegiPopup();\"></button>";
$html .= "\n   </dt>";
$html .= "\n</dl>";

$html .= "\n<div class=\"pop-base\">";
$html .= "\n   <div class=\"pop-content\">";
$html .= "\n       <div class=\"form-group\">";

$html .= "\n           <label class=\"control-label fix_width75 tar\">회원명</label><label class=\"fix_width10 fs14 tac\">:</label>";                                               
$html .= "\n           " . $name;
$html .= "\n           <label class=\"control-label fix_width75 tar\">할인</label><label class=\"fix_width10 fs14 tac\">:</label>";                                               
$html .= "\n           <input type=\"text\" class=\"input_co2 fix_width110\" id=\"pop_member_rate\" placeholder=\"\" value=\"" . $rate . "\" maxlength=\"10\"  onkeyup=\"this.value = inputOnlyNumber(this.value);\">%";

$html .= "\n       <hr class=\"hr_bd3\">";
$html .= "\n       <p class=\"tac mt15\">";
$html .= "\n          <button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"modiMemberRateInfo('" . $seqno . "', 'pop');\">저장</button>";
$html .= "\n          <label class=\"fix_width5\"> </label>";
$html .= "\n          <button type=\"button\" id=\"delete_btn\" class=\"btn btn-sm btn-danger\" onclick=\"deleteMemberRateInfo('" . $seqno . "');\">삭제</button>";
$html .= "\n          <label class=\"fix_width140\"> </label>";
$html .= "\n          <button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"hideRegiPopup();\">닫기</button>";
$html .= "\n       </p>";
$html .= "\n   </div>";
$html .= "\n</div>";

echo $html;
$conn->Close();
?>
