<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/TemplateInfoMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TemplateInfoMngDAO();

$cate_sortcode = $fb->form("cate_sortcode");
$seqno = $fb->form("seqno"); // cate_template_seqno

$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["cate_template_seqno"] = $seqno;

$uniq_num = '';
$stan_name = '';
$s_cut_size  = '';
$s_work_size = '';

$ai_file_name  = "없음";
$eps_file_name = "없음";
$cdr_file_name = "없음";
$sit_file_name = "없음";

$ai_del_btn  = '';
$eps_del_btn = '';
$cdr_del_btn = '';

if (!empty($seqno)) {
    // 수정버튼 클릭했을 경우
    $del_btn = "&nbsp;&nbsp;<button class=\"btn-xs bred\" onclick=\"removeTemplate('%s', '%s');\">삭제</button>";

    $rs = $dao->selectCateTemplateInfo($conn, $param)->fields;

    $uniq_num = $rs["uniq_num"];
    $stan_name = $rs["stan_name"];
    if (!empty($rs["ai_origin_file_name"])) {
        $ai_file_name = $rs["ai_origin_file_name"];
        $ai_del_btn   = sprintf($del_btn, $seqno, "ai");
    }
    if (!empty($rs["eps_origin_file_name"])) {
        $eps_file_name = $rs["eps_origin_file_name"];
        $eps_del_btn   = sprintf($del_btn, $seqno, "eps");
    }
    if (!empty($rs["cdr_origin_file_name"])) {
        $cdr_file_name = $rs["cdr_origin_file_name"];
        $cdr_del_btn   = sprintf($del_btn, $seqno, "cdr");
    }

    if (!empty($rs["sit_origin_file_name"])) {
        $sit_file_name = $rs["sit_origin_file_name"];
        $sit_del_btn   = sprintf($del_btn, $seqno, "sit");
    }

    $s_cut_size  = $rs["cut_size"];
    $s_work_size = $rs["work_size"];
}
unset($rs);

$cut_size      = '';
$cut_wid_size  = '';
$cut_vert_size = '';

$work_size      = '';
$work_wid_size  = '';
$work_vert_size = '';

$stan_name_html = option("선택");

$rs = $dao->selectCateStanInfo($conn, $param);

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;
    $cut_size  = $fields["cut_size"];
    $work_size = $fields["work_size"];

    $attr = sprintf("cut_wid=\"%s\" cut_vert=\"%s\" " .
                    "work_wid=\"%s\" work_vert=\"%s\"", $fields["cut_wid_size"]
                                                      , $fields["cut_vert_size"]
                                                      , $fields["work_wid_size"]
                                                      , $fields["work_vert_size"]);

    $key1 = sprintf("%s|%s|%s", $stan_name, $s_cut_size, $s_work_size);
    $key2 = sprintf("%s|%s|%s", $fields["name"], $cut_size, $work_size);
    if ($key1 === $key2) {
        $stan_name_html .= option($fields["name"] . '(' . $fields["typ"] . ')',
                                  $fields["name"],
                                  $attr . " selected=\"selected\"");

        $cut_wid_size  = $fields["cut_wid_size"];
        $cut_vert_size = $fields["cut_vert_size"];

        $work_wid_size  = $fields["work_wid_size"];
        $work_vert_size = $fields["work_vert_size"];
    } else {
        $stan_name_html .= option($fields["name"] . '(' . $fields["typ"] . ')',
                                  $fields["name"],
                                  $attr);
    }

    $rs->MoveNext();
}
unset($rs);

$del_btn = '';
if (!empty($seqno)) {
    // 수정버튼 클릭했을 경우
    $del_btn = "<button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"removeTemplate('" . $seqno . "', null);\">삭제</button>";
}

$pop_html = <<<html
    <dl>
        <dt class="tit">
            <h4>상품템플릿관리</h4>
        </dt>
        <dt class="cls">
            <button type="button" class="btn btn-sm btn-danger fa fa-times" onclick="hideRegiPopup();"></button>
        </dt>
    </dl>
    <div class="pop-base">
        <div class="pop-content">
            <div class="form-group">
                <label class="control-label fix_width75 tar">고유번호</label><label class="fix_width10 fs14 tac">:</label>
                <input type="text" class="input_co2 fix_width140" placeholder="" id="pop_uniq_num" value="{$uniq_num}" maxlength="30" />
                <label class="control-label fix_width75 tar">사이즈</label><label class="fix_width10 fs14 tac">:</label>
                <select class="fix_width147" id="pop_stan_name" onchange="changeSize($(this).find('option:selected'));">
                    {$stan_name_html}
                </select>
                <br />

                <label class="control-label fix_width75 tar">재단사이즈</label><label class="fix_width10 fs14 tac">:</label>
                <input type="text" class="input_co2 fix_width140" id="pop_cut_size" value="{$cut_size}" disabled="disabled" readonly="readonly" maxlength="20" style="background-color: #e1e5ea;" />
                <input type="hidden" id="pop_cut_wid_size" value="{$cut_wid_size}" />
                <input type="hidden" id="pop_cut_vert_size" value="{$cut_vert_size}" />
                <label class="control-label fix_width75 tar">작업사이즈</label><label class="fix_width10 fs14 tac">:</label>
                <input type="text" class="input_co2 fix_width140" id="pop_work_size" value="{$work_size}" disabled="disabled" readonly="readonly" maxlength="20" style="background-color: #e1e5ea;">
                <input type="hidden" id="pop_work_wid_size" value="{$work_wid_size}" />
                <input type="hidden" id="pop_work_vert_size" value="{$work_vert_size}" />
                <br />

                <label class="control-label fix_width75 tar">AI파일</label><label class="fix_width10 fs14 tac">:</label>
                <label class="fs14 tac" id="ai_file_name" onclick="downloadTemplate('{$seqno}', 'ai');" style="cursor:pointer;">
                    {$ai_file_name}
                </label>
                {$ai_del_btn}
                <br/>
                <label class="control-label fix_width75 tar"></label><label class="fix_width10 fs14 tac"></label>
                <input type="file" id="pop_ai_file" name="pop_ai_file" />
                <br/>

                <label class="control-label fix_width75 tar">EPS파일</label><label class="fix_width10 fs14 tac">:</label>
                <label class="fs14 tac" id="eps_file_name" onclick="downloadTemplate('{$seqno}', 'eps');" style="cursor:pointer;">
                    {$eps_file_name}
                </label>
                {$eps_del_btn}
                <br/>
                <label class="control-label fix_width75 tar"></label><label class="fix_width10 fs14 tac"></label>
                <input type="file" id="pop_eps_file" name="pop_eps_file" />
                <br/>

                <label class="control-label fix_width75 tar">CDR파일</label><label class="fix_width10 fs14 tac">:</label>
                <label class="fs14 tac" id="cdr_file_name" onclick="downloadTemplate('{$seqno}', 'cdr');" style="cursor:pointer;">
                    {$cdr_file_name}
                </label>
                {$cdr_del_btn}
                <br/>
                <label class="control-label fix_width75 tar"></label><label class="fix_width10 fs14 tac"></label>
                <input type="file" id="pop_cdr_file" name="pop_cdr_file" />
                <br/>

                <label class="control-label fix_width75 tar">SIT파일</label><label class="fix_width10 fs14 tac">:</label>
                <label class="fs14 tac" id="sit_file_name" onclick="downloadTemplate('{$seqno}', 'sit');" style="cursor:pointer;">
                    {$sit_file_name}
                </label>
                {$sit_del_btn}
                <br/>
                <label class="control-label fix_width75 tar"></label><label class="fix_width10 fs14 tac"></label>
                <input type="file" id="pop_sit_file" name="pop_sit_file" />
                <br/>

            </div>
            <hr class="hr_bd3">
            <p class="tac mt15">
                <button type="button" class="btn btn-sm btn-success" onclick="modiTemplate('{$seqno}');">저장</button>
                {$del_btn}
                <button type="button" class="btn btn-sm btn-primary" onclick="hideRegiPopup();">닫기</button>
            </p>
        </div>
    </div>
html;

echo $pop_html;

$conn->Close();
?>
