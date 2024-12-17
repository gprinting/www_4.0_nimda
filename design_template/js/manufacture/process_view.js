$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    //dateSet('0'); 
    searchProcess(30, 1);
});

//보여줄 페이지 수
var showPage = 30;

//공정확인리스트 
var searchProcess = function(showPage, page) {

    var url = "/ajax/manufacture/process_view/load_process_view.php";
    var blank = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";
    var data = { 
        "preset_cate" : $("#preset_cate").val(),
        "typset_num"  : $("#typset_num").val(),
        "date_cnd"    : $("#date_cnd").val(),
        "date_from"   : $("#basic_from").val(),
        "date_to"     : $("#basic_to").val()
    };

    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(rs[0]);
        $("#page").html(rs[1]);
    };

    data.showPage      = showPage;
    data.page          = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

//상품리스트 페이지 이동
var movePage = function(val) {
    searchProcess(showPage, val);
}

//공정진행
var goNextState = function(typset_num, state) {

    if (confirm("공정을 진행하시겠습니까?") == false) {
        return false;
    }

    var url = "/proc/manufacture/process_view/modi_process_next.php";
    var data = { 
        "typset_num" : typset_num, 
        "state"      : state 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("공정을 진행 하였습니다.");
            searchProcess(30, 1);
        } else {
            alert("공정진행을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/*
//공정되돌리기
var goBeforeState = function(typset_num, state) {

    if (confirm("공정을 되돌리시겠습니까?") == false) {
        return false;
    }
    var url = "/proc/manufacture/process_view/modi_process_before.php";
    var data = { 
        "typset_num" : typset_num, 
        "state"      : state 
    };

    var callback = function(result) {
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
*/
