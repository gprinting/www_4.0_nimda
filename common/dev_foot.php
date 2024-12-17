<?
if (empty($fb->session("empl_seqno")) === true) {
    header("Location: /login.html");
    exit;
}

//직원이 권한 가능한 페이지 체크
$commonDAO->checkAuth($conn, $fb->session("empl_seqno"), $_SERVER["PHP_SELF"]);

$tmp_param = array();
$tmp_param['empl_seqno'] = $fb->session("empl_seqno");
$rs = $commonDAO->selectAuthPage2($conn, $tmp_param);

$arr_visible_depth1 = array();
$arr_visible_depth2 = array();
while ($rs && !$rs->EOF) {
    $depth1 = explode("/",$rs->fields['page_url'])[1];
    $depth2 = explode(".",explode("/",$rs->fields['page_url'])[2])[0];
    $auth_yn = $rs->fields['auth_yn'];
    if($auth_yn == "Y") {
        array_push($arr_visible_depth2, $depth2);
    }
    if(!in_array($depth1, $arr_visible_depth1) && $auth_yn == "Y") {
        array_push($arr_visible_depth1, $depth1);
        $template->reg($depth1 . "_display" , "display : visible");
    }
    $rs->MoveNext();
}

//메인 메뉴
$template->reg("main_title" , TOP_MENU_ARR[$top]);

//서브 메뉴
$template->reg("sub_title" , LEFT_MENU_ARR[$top]["sub"][$left]);

//메뉴
$template->reg("title" , LEFT_MENU_ARR[$top][$left][$left_sub]);

//왼쪽 메뉴 로딩시 선택
$template->reg("top" , $top);

//왼쪽 메뉴 로딩시 선택
$template->reg("active" , $left);

//왼쪽 메뉴 글씨 on
$template->reg("on" , $left_sub);

//탑메뉴 로딩시 선택
$template->reg($top , "active-link");

$sess = array();
$sess["empl_seqno"] = $fb->session("empl_seqno");
$sess["id"] = $fb->session("id");
$sess["name"] = $fb->session("name");
$sess["login_date"] = $fb->session("login_date");

//왼쪽 메뉴 생성
$template->reg("left", $leftSetting->getLeftMenuSetting(
                       LEFT_MENU_ARR[$top],
                       $top,
                       LEFT_MENU_CLASS_ARR,
                       $arr_visible_depth2));
//로그인 정보 생성
$template->reg("login_info", $leftSetting->loginInfo($sess));

//design_dir 경로
$template->reg("design_dir" , "/design_template");
$template->htmlPrint($_SERVER["PHP_SELF"]);

?>
