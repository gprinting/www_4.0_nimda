$(document).ready(function() {
    dateSet('0'); 
    searchList(30, 1);
});

//보여줄 페이지 수
var showPage = 30;
	
//선택 조건으로 검색
var searchList = function(showPage, page) {

    var url = "/ajax/manufacture/manual_brochure_typset/load_typset_list.php";
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

//조판 - 새창 열기
var openTypsetPop = function (seqno) {

    /*
    if (state != "2130") {
        if (confirm("완료 된것을 수정하면 모든 지시서들이 준비상태가 되며 공정은 취소됩니다.\n승인 하시겠습니까?")) {
            delOpData(seqno);
        } else {
            return false;
        }
    } else {
        window.open("/produce/" + el + "_regi_popup.html?seqno=" + seqno, "_blank");
    }
    */
    window.open("/manufacture/web_typset_regi_popup.html?seqno=" + seqno, "_blank");
}
