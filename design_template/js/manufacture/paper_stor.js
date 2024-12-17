/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/08 harry 생성
 *=============================================================================
 *
 */

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
	
//선택 조건으로 검색
var searchProcess = function(showPage, page) {

    var url = "/ajax/manufacture/paper_stor/load_paper_stor_list.php";
    var blank = "<tr><td colspan=\"14\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
    	"date_cnd" : $("#date_cnd").val(),
    	"date_from"   : $("#basic_from").val(),
    	"date_to"     : $("#basic_to").val(),
    	"state"       : $("#op_state").val(),
    	"search_cnd2" : $("#search_cnd2").val(),
    	"search_txt"  : $("#search_val").val()
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

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    showPage = val;
    searchProcess(showPage, 1);
}

//상품리스트 페이지 이동
var movePage = function(val) {

    searchProcess(showPage, 1);
}

//종이입고
var paperStor = function(seqno) {
   
    var url = "/proc/manufacture/paper_stor/regi_paper_stor.php";
    var data = { 
        "paper_op_seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            alert("종이를 입고 하였습니다. \n완료 되거나, 취소 된 입고는 입고가 되지 않습니다.");
            searchProcess(showPage, 1);
        } else {
            alert("종이입고를 실패 하였습니다. \n관리자에게 문의 해주세요.");
        }
    };

    ajaxCall(url, "html", data, callback);
}

/*
//종이 다중입고
var paperMultiStor = function() {
   
    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/paper_stor/regi_paper_stor.php";
    var data = { 
        "paper_op_seqno" : getselectedNo()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("종이를 입고 하였습니다. \n완료 되거나, 취소 된 입고는 입고가 되지 않습니다.");
            searchProcess(showPage, 1);
        } else {
            alert("종이입고를 실패 하였습니다. \n관리자에게 문의 해주세요.");
        }
    };

    ajaxCall(url, "html", data, callback);
}
*/
