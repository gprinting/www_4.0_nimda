$(document).ready(function() {
    dateSet('0'); 
    searchList(30, 1);
    searchTypsetList(1);
});

var selectAllList = function() {
    searchList(30, 1);
    searchTypsetList(1);
}

//보여줄 페이지 수
var showPage = 30;
	
//선택 조건으로 검색
var searchList = function(showPage, page) {

    var url = "/ajax/manufacture/manual_sheet_typset/load_order_list.php";
    var blank = "<tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "state"       : $("#state").val(),
        "cate_top"    : $("#cate_top").val(),
        "cate_mid"    : $("#cate_mid").val(),
        "cate_bot"    : $("#cate_bot").val(),
        "typset_num"  : $("#typset_num").val(),
        "search_cnd"  : $("#search_cnd").val(),
        "search_txt"  : $("#search_txt").val(),
        "date_cnd"    : $("#date_cnd").val(),
        "date_from"   : $("#date_from").val(),
        "date_to"     : $("#date_to").val(),
        "time_from"   : $("#time_from").val(),
        "time_to"     : $("#time_to").val()
    };
    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(rs[0]);
        $("#page").html(rs[1]);
        $("#allCheck").prop("checked", false);
    };

    data.showPage      = showPage;
    data.page          = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

//조건 검색 조건 변경
var changeSearchCnd = function(val) {

    $("#search_cnd").val(val);
    $("#search_val").val("");
    $("#search_txt").val("");
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    showPage = val;
    searchList(showPage, 1);
}

//상품리스트 페이지 이동
var movePage = function(val) {

    searchList(showPage, 1);
}

//조판 생성
var regiSheetTypset = function() {

    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/manual_sheet_typset/regi_sheet_typset.php";
    var data = { 
        "seqno" : getselectedNo(),
        "board" : $("#board").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            selectAllList();
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//선택 조건으로 검색
var searchTypsetList = function(page) {

    var url = "/ajax/manufacture/manual_sheet_typset/load_typset_list.php";
    var blank = "<tr><td colspan=\"5\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "date_cnd"    : $("#date_cnd").val(),
        "date_from"   : $("#date_from").val(),
        "date_to"     : $("#date_to").val(),
        "time_from"   : $("#time_from").val(),
        "time_to"     : $("#time_to").val()
    };
    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#typset_list").html(blank);
            return false;
        }
        $("#typset_list").html(rs[0]);
        $("#typset_page").html(rs[1]);
    };

    data.page = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

//조판 - 새창 열기
var openTypsetPop = function (typset_num) {

    window.open("/manufacture/web_sheet_typset_regi_popup.html?typset_num=" + typset_num, "_blank");
}

//조판 취소
var cancelTypset = function(typset_num) {

    var url = "/proc/manufacture/manual_sheet_typset/del_sheet_typset.php";
    var data = {
        "typset_num" : typset_num 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("조판을 취소 하였습니다.");
             selectAllList();
        } else {
            alert("조판 취소를 실패 하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
