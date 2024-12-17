/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/24 임종건 생성
 * 2015/12/24 임종건 정리회원 리스트 관련함수 추가
 *=============================================================================
 *
 */

$(document).ready(function() {
    dateSet('0');
    reduceListAjaxCall(30, 1, "");
});

var searchTxt = "";
var showPage = "";
var detailSearchTxt = "";
var detailShowPage = "";
var memberSeqno = "";

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

//정리 회원 리스트 호출
var reduceListAjaxCall = function(sPage, page, sorting) {
 
    var tmp = sorting.split('/');
    for (var i in tmp) {
        tmp[i];
    }

    if ($("#date_from").val() > $("#date_to").val()) {
        hideMask();
        alert("선택하신 날짜 기간에 이상이 있습니다.");
        return false;
    }

    if (checkBlank($("#office_nick").val())) {
        $("#member_seqno").val("");
    }

	if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
            alert("검색창 팝업을 이용하시고 검색해주세요.");
	    $("#office_nick").focus();
	    return false;
	}

    var data = {
    	"sell_site"    : $("#sell_site").val(),
    	"withdraw_dvs" : $("#withdraw_dvs").val(),
    	"search_cnd"   : $("#search_cnd").val(),
    	"date_from"    : $("#date_from").val(),
    	"date_to"      : $("#date_to").val(),
    	"time_from"    : $("#time_from").val(),
    	"time_to"      : $("#time_to").val(),
        "member_seqno" : $("#member_seqno").val(),
    	"showPage"     : sPage,
    	"page"         : page,
        "sorting"      : tmp[0],
        "sorting_type" : tmp[1]
    };
    var url = "/ajax/member/reduce_list/load_reduce_list.php";

    var blank = "<tr><td colspan=\"9\">검색 된 내용이 없습니다.</td></tr>";

    showMask();
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
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

    reduceListAjaxCall(30, 1, "");
}

//회원 상세정보 페이지 보여주는수 
var showPageSetting = function(val) {

    showPage = val;
    reduceListAjaxCall(val, 1, "");
}

//회원상세 정보 페이지 이동
var movePage = function(val) {

    reduceListAjaxCall(showPage, val, "");
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
    reduceListAjaxCall(showPage, 1, sorting);
}

//회원 상세보기
var showMemberDetail = function(seqno) {

    showMask();
    var url = "/ajax/member/reduce_list/load_reduce_member_info.php";

    $.ajax({
        type: "POST",
        data: {"seqno" : seqno},
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");
            $("#member_info").html(rs[0]);
            $("#input:radio[name='new_yn']:radio[value='" + rs[1] + "']").attr("checked",true);

            for (var i = 1; i <= 14; i++) {
                j = i + 1;
                if (rs[j] == "Y") {
                    $("input:checkbox[id='reduce_" + i + "']").prop("checked", true);
                }
            }

            $('#date_sales_from').datepicker({
                autoclose:true,
                format: "yyyy-mm-dd",
                todayBtn: "linked",
                todayHighlight: true,
            });   
            $('#date_sales_to').datepicker({
                autoclose:true,
                format: "yyyy-mm-dd",
                todayBtn: "linked",
                todayHighlight: true,
            });
            memberSeqno = seqno;
            detailListAjaxCall("sales", "", 30, 1, "");
        }   
    });
}

//회원 복원
var restoreMember = function() {
 
    var seqno = getselectedNo();

    if (checkBlank(seqno)) {
        alert("선택 한 항복이 없습니다.");
        return false;
    }
    showMask();
    var url = "/proc/member/reduce_list/restore_member.php";
    $.ajax({
        type: "POST",
        data: {
	    "seqno" : seqno
	},
        url: url,
        success: function(result) {

            hideMask();
            if (result == 1) {
                $("#member_info").html("");
                reduceListAjaxCall(30, 1, "");
                sortInit();
                $(".check_box").prop("checked", false);
                alert("선택하신 회원을 복원하였습니다.");
            } else {
                alert("복원을 실패하였습니다.");
            }
        }   
    });
}

//회원 영구탈퇴
var reduceMember = function() {
 
    var seqno = getselectedNo();

    if (checkBlank(seqno)) {
        alert("선택 한 항복이 없습니다.");
	return false;
    }
    showMask();
    var url = "/proc/member/reduce_list/reduce_member.php";
    $.ajax({
        type: "POST",
        data: {
	    "seqno" : seqno
	},
        url: url,
        success: function(result) {

            hideMask();
            if (result == 1) {
                reduceListAjaxCall(30, 1, "");
                sortInit();
                $(".check_box").prop("checked", false);
                alert("선택하신 회원을 영구탈퇴하였습니다.");
            } else {
                alert("영구탈퇴를 실패하였습니다.");
            }
        }   
    });
}

//매출정보 검색 날짜 범위 설정
var salesDateSet = function(num) {
    detailDateSet(num, "sales");
}

//회원상세정보 리스트호출
var detailListAjaxCall = function(dvs ,txt, sPage, page, sorting) {
 
    showMask();
    var tmp = sorting.split('/');
    for (var i in tmp) {
        tmp[i];
    }

    if ($("#date_" + dvs + "_from").val() > $("#date_" + dvs + "_to").val()) {
        hideMask();
        alert("선택하신 날짜 기간에 이상이 있습니다.");
        return false;
    }
 
    var data = {
    	"seqno"       : memberSeqno,
    	"search_cnd"  : $("#" + dvs + "_search_cnd").val(),
    	"date_from"   : $("#date_" + dvs + "_from").val(),
    	"date_to"     : $("#date_" + dvs + "_to").val(),
    	"time_from"   : $("#time_" + dvs + "_from").val(),
    	"time_to"     : $("#time_" + dvs + "_to").val(),
        "searchTxt"   : txt,
    	"showPage"    : sPage,
    	"page"        : page,
        "sorting"       : tmp[0],
        "sorting_type"  : tmp[1]
    };
    var url = "/ajax/member/member_common_list/load_member_" + dvs + "_list.php";

    var blank = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
                $("#member_" + dvs + "_list").html(blank);
            } else {
                $("#member_" + dvs + "_list").html(rs[0]);
            }

            $("#member_" + dvs + "_page").html(rs[1]);

            if (dvs == "sales") {
                $("#member_" + dvs + "_total").html(rs[2]);
            }
        }   
    });
}

//회원상세정보 검색
var searchMemberDetailInfo = function(dvs) {

    detailListAjaxCall(dvs, "", 30, 1, "");
}

//회원 상세정보 페이지 보여주는수 
var detailShowPageSetting = function(val, dvs) {

    detailShowPage = val;

    if (dvs == "point_req") {
        memberPointReqListAjaxCall(val, 1);
    } else {
        detailListAjaxCall(dvs, detailSearchTxt, val, 1, "");
    }
}

//회원상세 정보 페이지 이동
var detailMovePage = function(val, dvs) {

    if (dvs == "point_req") {
        memberPointReqListAjaxCall(detailShowPage, val);
    } else {
        detailListAjaxCall(dvs, detailSearchTxt, detailShowPage, val, "");
    }
}

//회원상세정보 컬럼별 sorting
var detailSortList = function(val, el, dvs) {

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

    detailListAjaxCall(dvs, detailSearchTxt, detailShowPage, val, sorting);
}

//검색어 검색 엔터
var detailSearchKey = function(event, val, dvs) {

    if (event.keyCode == 13) {
        detailSearchTxt = val;
        detailListAjaxCall(dvs, detailSearchTxt, detailShowPage, 1, "");
    }
}

//검색어 검색 버튼
var detailSearchText = function(dvs) {

    detailSearchTxt = $("#" + dvs + "_search").val();
    detailListAjaxCall(dvs, detailSearchTxt, detailShowPage, 1, "");
}
