$(document).ready(function() {
    $("#typset_num").focus();
}).focusout(function(){
    $("#typset_num").focus();
});

//인쇄 검색
var searchPrintInfo = function(typset_num) {

    var url = "/ajax/manufacture/print_list/load_print_process.php";
    var callback = function(result) {
        $("#rs_info").html(result);
        $("#typset_num").focus();
        opener.location.reload(true);
    };

    ajaxCall(url, "html", {"typset_num" : typset_num}, callback);
}

//상태변경
var changeState = function(event, typset_num) {
    var url = "/proc/manufacture/print_list/modi_print_process_multi_finish.php";
    var callback = function(result) {
        if (result == 0) {
            showMsg440("상태변경을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        } else if (result == 2) {
            showMsg440("인쇄작업이 완료 된 조판이거나 존재하지 않는 조판입니다.");
        } else{
            searchPrintInfo(typset_num);
        }
        setTimeout(function(){ hideMsg440(); }, 1000);
    };

    if (event.keyCode == 9 || event.keyCode == 13) {
        ajaxCall(url, "html", {"typset_num" : typset_num}, callback);
        $("#typset_num").val("");
    }
}
