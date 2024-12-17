/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2015/12/31 왕초롱 생성
 *============================================================================
 *
 */
$(document).ready(function() {
    dateSet('0');
    selectSearch(1,1);
});

var page = "1"; //페이지
var list_num = "30"; //리스트 갯수
var tab_id = "grade"; //탭 id
var search_data = {
    "list_num"     : "",
    "page"         : "1",
    "sell_site"    : "",
    "member_seq" : "",
    "date_from"    : "",
    "date_to"      : "",
    "date_dvs"     : "",
    "aprvl_type"   : ""
}
var grade_req_seq = "";
var point_req_seq = "";
var member_seq = "";

/**********************************************************************
                         * 마케팅 승인 공통 부분 *
 **********************************************************************/

//팝업창 검색 버튼 클릭 검색시
var clickSearchNick = function(event, search_str, dvs) {

    loadOfficeNick(event, $("#search_pop").val(), "click");

}

//회원 닉네임 가져오기
var loadOfficeNick = function(event, search_str, dvs) {
    if (dvs != "click") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    showMask();

    $.ajax({
            type: "POST",
            data: {
                "sell_site"  : $("#sell_site").val(),
                "search_val" : search_str
            },
            url: "/ajax/common/load_office_nick.php",
            success: function(result) {
                if (dvs == "") {

                    hideMask();
                    searchPopShow(event, 'loadOfficeNick', 'clickSearchNick');

                } else {

                    hideMask();
                    showBgMask();

                }
                $("#search_list").html(result);
           }
    });
}

//팝업 검색된 인쇄명 클릭시
var nameClick = function(val, name) {
    $("#member_seqno").val(val);
    hideRegiPopup();
    $("#office_nick").val(name);

}

//선택 조건으로 검색
var selectSearch = function(page, type) {

    tabCtrl(tab_id, page, type);

}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    tabCtrl(tab_id, page, '2');
}

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {
    selectSearch(page, "2");
}

// 탭 클릭시
var tabCtrl = function(el, page, type) {

    $('#aprvl_dvs').val(el);

    //사내 닉네임이 비었을때
    if (checkBlank($("#office_nick").val())) {
        $("#member_seqno").val("");
    }

	if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
        alert("검색창 팝업을 이용하시고 검색해주세요.");
	    $("#office_nick").focus();
	    return false;
	}

    showMask();
    tab_id = el; //탭 선택
    var search_str = ""; //검색어

    //탭클릭이나 상단의 검색바로 검색시 value 값 설정
    if (type == "1") {

        search_data.sell_site = $("#sell_site").val();
        search_data.member_seqno = member_seq;
        search_data.date_dvs =  $("#search_cnd").val();
        search_data.aprvl_type =  $("#aprvl_type").val();

    }

    //탭 이동시 혹은 첫 검색시 리스트 카운트 초기화
    if (type == "1" || type == "3") {

        list_num = "30";
        $('select[name=list_set]').val('30');

    }

    search_data.year =  $("#year").val();
    search_data.mon =  $("#mon").val();

    //페이지 list 갯수
    search_data.list_num = list_num;
    //페이지
    search_data.page = page;

    //탭 별 Ajax호출
    if (tab_id == "grade") {

        gradeAprvlList(search_data);

    } else {

        pointAprvlList(search_data);

    }
}

/**********************************************************************
                         * 등급 승인 부분 *
 **********************************************************************/

//등급 승인 리스트
var gradeAprvlList = function(formData) {

    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/mkt/mkt_aprvl_mng/load_grade_aprvl_list.php",
            success: function(result) {
                var list = result.split('♪♭@');
	            if ($.trim(list[0]) == "") {

                	$("#grade_list").html("<tr><td colspan='9'>검색된 내용이 없습니다.</td></tr>");

	            } else {

                	$("#grade_list").html(list[0]);
                	$("#grade_page").html(list[1]);
			$('select[name=list_set]').val(list_num);

	            }
	   	    hideMask();
           },
           error: getAjaxError
    });
}

//등급 승인 View
var gradeAprvlView = function(seq) {

    grade_req_seq = seq;

    $.ajax({
            type: "POST",
            data: {
	    		"grade_req_seq" : seq

	    },
            url: "/ajax/mkt/mkt_aprvl_mng/load_grade_aprvl_popup.php",
            success: function(result) {

        	openRegiPopup(result, 420);

           },
           error: getAjaxError
    });
}

//등급 승인
var acceptGradeAprvl = function() {

    $.ajax({
            type: "POST",
            data: {
	    		"grade_req_seq" : grade_req_seq,
			"state"         : "2"

	    },
            url: "/proc/mkt/mkt_aprvl_mng/proc_grade_aprvl.php",
            success: function(result) {
	    	if ($.trim(result) == "1") {

			alert("승인되었습니다.");
	    		hideRegiPopup();
    			tabCtrl("grade", page, "2");

		} else {

			alert("승인 실패했습니다.");

		}
           },
           error: getAjaxError
    });

}

//등급 승인 거절
var rejectGradeAprvl = function() {

    $.ajax({
            type: "POST",
            data: {
	    		"grade_req_seq" : grade_req_seq,
			    "state"         : "3"

	    },
            url: "/proc/mkt/mkt_aprvl_mng/proc_grade_aprvl.php",
            success: function(result) {
		if ($.trim(result) == "1") {

			alert("승인거절되었습니다.");
	    		hideRegiPopup();
    			tabCtrl("grade", page, "2");

		} else {

			alert("승인거절에 실패했습니다.");

		}
           },
           error: getAjaxError
    });
}

/**********************************************************************
                         * 포인트 승인 부분 *
 **********************************************************************/

//포인트 승인 리스트
var pointAprvlList = function(formData) {
    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/mkt/mkt_aprvl_mng/load_point_aprvl_list.php",
            success: function(result) {
                var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#point_list").html("<tr><td colspan='9'>검색된 내용이 없습니다.</td></tr>");

	            } else {

                	$("#point_list").html(list[0]);
                	$("#point_page").html(list[1]);
			$('select[name=list_set]').val(list_num);

	            }
	   	    hideMask();
           },
           error: getAjaxError
    });
}

//포인트 승인 View
var pointAprvlView = function(seq) {

    point_req_seq = seq;

    $.ajax({
            type: "POST",
            data: {
	    		"point_req_seq" : seq

	    },
            url: "/ajax/mkt/mkt_aprvl_mng/load_point_aprvl_popup.php",
            success: function(result) {

        	openRegiPopup(result, "420");

           },
           error: getAjaxError
    });
}

//포인트 승인
var acceptPointAprvl = function() {

    $.ajax({
            type: "POST",
            data: {
	    		"point_req_seq" : point_req_seq,
			    "state"         : "2"

	    },
            url: "/proc/mkt/mkt_aprvl_mng/proc_point_aprvl.php",
            success: function(result) {
	    	if ($.trim(result) == "1") {

			alert("승인되었습니다.");
	    		hideRegiPopup();
    			tabCtrl("point", page, "2");

		} else {

			alert("승인 실패했습니다.");

		}
           },
           error: getAjaxError
    });

}

//포인트 승인 거절
var rejectPointAprvl = function() {

    $.ajax({
            type: "POST",
            data: {
	    		"point_req_seq" : point_req_seq,
			"state"         : "3"

	    },
            url: "/proc/mkt/mkt_aprvl_mng/proc_point_aprvl.php",
            success: function(result) {
		if ($.trim(result) == "1") {

			alert("승인거절되었습니다.");
	    		hideRegiPopup();
    			tabCtrl("point", page, "2");

		} else {

			alert("승인거절에 실패했습니다.");

		}
           },
           error: getAjaxError
    });

}

