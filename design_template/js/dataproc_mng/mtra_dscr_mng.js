/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/07 왕초롱 생성
 *============================================================================
 *
 */

$(document).ready(function() {

	tabCtrl("paper","1","1");

});

var page = "1"; //페이지
var list_num = "15"; //리스트 갯수
var tab_id = "paper"; // 탭 id
var search_type = "1";
var ajax_data = {
	"list_num"     : "",
	"page"         : "1",
	"search_str"   : ""
}

/**********************************************************************
                         * 공통 부분 *
 **********************************************************************/


// 탭 클릭시
var tabCtrl = function(el, page, type) { 

    //팝업 보이기
    showMask();

    tab_id = el; //탭 선택 

    //탭 이동시 혹은 첫 검색시 리스트 카운트 초기화
    if (type == "3") {
        list_num = "15";
        $('select[name=list_set]').val(15);
        
	    search_type = "1";
	    ajax_data.search_str = "";
        resetSearchStr();

    } else if (type == "2") {

	    ajax_data.search_str = $("#search_" + tab_id).val();
	    search_type = "2";

    }

    //페이지 list 갯수
    ajax_data.list_num = list_num;
    //페이지
    ajax_data.page = page;

    if (el == "paper") {

	    loadPaperDscrList(ajax_data);

    } else if (el == "after") {

	    loadAfterDscrList(ajax_data);

    } else {

	    loadOptDscrList(ajax_data);

    }
}

var resetSearchStr = function() {

	$("#search_paper").val('');
	$("#search_after").val('');
	$("#search_opt").val('');
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    tabCtrl(tab_id, '1', search_type);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {

    tabCtrl(tab_id, page, search_type)

}

//엔터 쳤을때 검색
var enterCheck = function(event) {

    if(event.keyCode == 13) {
        tabCtrl(tab_id, '1','2');
    }

}

/**********************************************************************
                         * 종이 설명 부분 *
 **********************************************************************/

//종이 설명 리스트
var loadPaperDscrList = function(formData) {

    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/dataproc_mng/mtra_dscr_mng/load_paper_dscr_list.php",
            success: function(result) {

		    var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#paper_list").html("<tr><td colspan='8'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

                	$("#paper_list").html(list[0]);
                	$("#paper_page").html(list[1]); 
			        $('select[name=list_set]').val(list_num);

	            }
	   	    hideMask();
           },
           error: getAjaxError
    });
}

//종이 설명 수정 팝업
var popPaperDscr = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"paper_dscr_seq" : seq 
	    
	    },
            url: "/ajax/dataproc_mng/direct_dlvr_mng/load_input_dlvr_popup.php",
            success: function(result) {

        	    openRegiPopup(result, 420);

           },
           error: getAjaxError
    });
}

//종이 설명 저장
var savePaperDscr = function(seq) {

    //종이명이 비었을때
    if ($("#paper_name").val() == "") {

        alert("종이명을 입력해주세요.");
	    $("#paper_name").focus();
        return false;
    }

    //구분이 비었을때
    if ($("#dvs").val() == "") {

        alert("구분을 입력해주세요.");
	    $("#dvs").focus();
        return false;
    }

    //느낌이 비었을때
    if ($("#sense").val() == "") {

        alert("느낌을 입력해주세요.");
	    $("#sense").focus();
        return false;
    }

    var formData = new FormData($("#paper_form")[0]);
        formData.append("paper_dscr_seqno", seq);

    $.ajax({
            type: "POST",
            data: formData,
            processData : false,
            contentType : false,
            url: "/proc/dataproc_mng/mtra_dscr_mng/proc_paper_dscr.php",
            success: function(result) {
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadPaperDscrList('1');
                    hideRegiPopup();

                } else {

                    alert("실패했습니다.");
                }

           },
           error: getAjaxError
    });
}

//종이 설명 삭제
var delPaperDscr = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"paper_dscr_seq" : seq 
	    
	    },
            url: "/proc/dataproc_mng/mtra_dscr_mng/del_paper_dscr.php",
            success: function(result) {
		if($.trim(result) == "1") {

                    alert("삭제했습니다.");
                    loadPaperDscrList('1');
                    hideRegiPopup();

                } else {

                    alert("삭제에 실패했습니다.");
                }
           },
           error: getAjaxError
    });
}

/**********************************************************************
                         * 후공정 설명 부분 *
 **********************************************************************/

//후공정 설명 리스트
var loadAfterDscrList = function(formData) {

    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/dataproc_mng/mtra_dscr_mng/load_after_dscr_list.php",
            success: function(result) {

	    	var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#after_list").html("<tr><td colspan='4'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

                	$("#after_list").html(list[0]);
                	$("#after_page").html(list[1]); 
			$('select[name=list_set]').val(list_num);

	            }
	   	    hideMask();
           },
           error: getAjaxError
    });
}

//후공정 설명 수정 팝업
var popAfterDscr = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"after_dscr_seq" : seq 
	    
	    },
            url: "/ajax/dataproc_mng/mtra_dscr_mng/load_after_dscr_popup.php",
            success: function(result) {

        	    openRegiPopup(result, 500);

           },
           error: getAjaxError
    });
}

//후공정 설명 저장
var saveAfterDscr = function(seq) {

    //후공정명이 비었을때
    if ($("#after_name").val() == "") {

        alert("후공정명을 입력해주세요.");
	    $("#after_name").focus();
        return false;
    }

    //후공정 설명이 비었을때
    if ($("#dscr").val() == "") {

        alert("후공정설명을 입력해주세요.");
	    $("#after_dscr").focus();
        return false;
    }

    var formData = new FormData($("#after_form")[0]);
        formData.append("after_dscr_seqno", seq);

    $.ajax({
            type: "POST",
            data: formData,
            processData : false,
            contentType : false,
            url: "/proc/dataproc_mng/mtra_dscr_mng/proc_after_dscr.php",
            success: function(result) {
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadAfterDscrList();
                    hideRegiPopup();

                } else {

                    alert("실패했습니다.");
                }

           },
           error: getAjaxError
    });
}

//후공정 설명 삭제
var delAfterDscr = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"after_dscr_seq" : seq 
	    
	    },
            url: "/proc/dataproc_mng/mtra_dscr_mng/del_after_dscr.php",
            success: function(result) {
		if($.trim(result) == "1") {

                    alert("삭제했습니다.");
                    loadAfterDscrList();
                    hideRegiPopup();

                } else {

                    alert("삭제에 실패했습니다.");
                }
           },
           error: getAjaxError
    });
}

/**********************************************************************
                         * 옵션 설명 부분 *
 **********************************************************************/

//옵션 설명 리스트
var loadOptDscrList = function(formData) { 
    $.ajax({
            type: "POST",
            data: formData,
            url: "/ajax/dataproc_mng/mtra_dscr_mng/load_opt_dscr_list.php",
            success: function(result) {

	    var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#opt_list").html("<tr><td colspan='5'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

                	$("#opt_list").html(list[0]);
                	$("#opt_page").html(list[1]); 
			$('select[name=list_set]').val(list_num);

	            }
	   	hideMask();
           },
           error: getAjaxError
    });
}

//옵션 설명 수정 팝업
var popOptDscr = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"opt_dscr_seq" : seq 
	    
	    },
            url: "/ajax/dataproc_mng/mtra_dscr_mng/load_opt_dscr_popup.php",
            success: function(result) {

        	    openRegiPopup(result, 500);

           },
           error: getAjaxError
    });
}

//옵션 설명 저장
var saveOptDscr = function(seq) {

    //옵션명이 비었을때
    if ($("#opt_name").val() == "") {

        alert("옵션명을 입력해주세요.");
	    $("#opt_name").focus();
        return false;
    }

    //옵션 설명이 비었을때
    if ($("#opt_dscr").val() == "") {

        alert("옵션설명을 입력해주세요.");
	    $("#opt_dscr").focus();
        return false;
    }

    var conf = confirm("저장하시겠습니까?");

    if (!conf) {
        return false;
    }

    var formData = new FormData($("#opt_form")[0]);
        formData.append("opt_dscr_seqno", seq);

    $.ajax({
            type: "POST",
            data: formData,
            processData : false,
            contentType : false,
            url: "/proc/dataproc_mng/mtra_dscr_mng/proc_opt_dscr.php",
            success: function(result) {
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadOptDscrList();
                    hideRegiPopup();

                } else {

                    alert("실패했습니다.");
                }

           },
           error: getAjaxError
    });
}

//옵션 설명 삭제
var delOptDscr = function(seq) {

    var conf = confirm("삭제 하시겠습니까?");
    if (!conf) {
        return false;
    }

    $.ajax({
            type: "POST",
            data: {
	    		"opt_dscr_seq" : seq 
	    
	        },
            url: "/proc/dataproc_mng/mtra_dscr_mng/del_opt_dscr.php",
            success: function(result) {

		        if($.trim(result) == "1") {

                    alert("삭제했습니다.");
                    loadOptDscrList();
                    hideRegiPopup();

                } else {

                    alert("삭제에 실패했습니다.");
                }
           },
           error: getAjaxError
    });
}




