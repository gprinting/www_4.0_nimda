/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/23 임종건 생성
 * 2015/12/23 임종건 휴면대상회원 리스트 관련함수 추가
 *=============================================================================
 *
 */

$(document).ready(function() {
    dateSet('0');
    quiescenceListAjaxCall("", 30, 1, "");
});

var searchTxt = "";
var showPage = "";

//전체 선택
var allCheck = function() {

    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#allCheck").prop("checked")) {
        $("#member_list input[type=checkbox]").prop("checked", true);
    } else {
        $("#member_list input[type=checkbox]").prop("checked", false);
    }
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function() {

    var selectedValue = ""; 
    
    $("#member_list input[name=chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

/*
 * 휴면 대상 회원 리스트 호출
 */
var quiescenceListAjaxCall = function(txt, sPage, page, sorting) {
 
    showMask();
    var tmp = sorting.split('/');
    for (var i in tmp) {
        tmp[i];
    }

    if ($("#date_from").val() > $("#date_to").val()) {
        hideMask();
        alert("선택하신 날짜 기간에 이상이 있습니다.");
        return false;
    }
 
    var data = {
    	"sell_site"   : $("#sell_site").val(),
    	"search_cnd"  : $("#search_cnd").val(),
    	"date_from"   : $("#date_from").val(),
    	"date_to"     : $("#date_to").val(),
    	"time_from"   : $("#time_from").val(),
    	"time_to"     : $("#time_to").val(),
        "searchTxt"   : txt,
    	"showPage"    : sPage,
    	"page"        : page,
        "sorting"       : tmp[0],
        "sorting_type"  : tmp[1]
    };
    var url = "/ajax/member/quiescence_list/load_quiescence_list.php";

    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0] == "") {
                $("#member_list").html(blank);
            } else {
                $("#member_list").html(rs[0]);
            }

            $("#member_page").html(rs[1]);
            $("#member_total").html(rs[2]);
        }   
    });
}

//회원 검색
var searchMember = function() {

    quiescenceListAjaxCall("", 30, 1, "");
}

//회원 상세정보 페이지 보여주는수 
var showPageSetting = function(val) {

    showPage = val;
    quiescenceListAjaxCall("", val, 1, "");
}

//회원상세 정보 페이지 이동
var movePage = function(val) {

    quiescenceListAjaxCall("", showPage, val, "");
}

//회원상세정보 컬럼별 sorting
var sortList = function(val, el) {

    var flag = "";

    if ($(el).children().hasClass("fa-sort-desc")) {
        sortInit();
        $(el).children().addClass("fa-sort-asc");
        $(el).children().removeClass("fa-sort");
        flag = "ASC";
    } else {
        sortInit();
        $(el).children().addClass("fa-sort-desc");
        $(el).children().removeClass("fa-sort");
        flag = "DESC";
    }

    var sorting = val + "/" + flag;
    quiescenceListAjaxCall("", showPage, 1, sorting);
}

//검색어 검색 엔터
var searchKey = function(event, val, dvs) {

    if (event.keyCode == 13) {
        searchTxt = val;
        quiescenceListAjaxCall(searchTxt, showPage, 1, "");
    }
}

//검색어 검색 버튼
var searchText = function(dvs) {

    searchTxt = $("#search").val();
    quiescenceListAjaxCall(searchTxt, showPage, 1, "");
}

//휴면처리
var quiescenceProc = function() {
 
    showMask();
    var seqno = getselectedNo();
    var data = {
    	"seqno"   : seqno
    };
    var url = "/proc/member/quiescence_list/modi_quiescence_list.php";
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            if (result == 1) {
                quiescenceListAjaxCall("", 30, 1, "");
                sortInit();
                $(".check_box").prop("checked", false);
                alert("선택하신 회원을 휴면처리하였습니다.");
            } else {
                alert("휴면처리를 실패하였습니다.");
            }
        }   
    });
}
