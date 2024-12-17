/* *
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/08 왕초롱 생성
 *============================================================================
 *
 */

var page = "1"; //페이지
var list_num = "30"; //리스트 갯수

$(document).ready(function() {

    loadDeparList();

});
/**********************************************************************
                         * 부서 관리 부분 *
 **********************************************************************/

//부서 리스트 불러오기
var loadDeparList = function() {

    showMask();
    
    $.ajax({
            type: "POST",
            data: {},
            url: "/ajax/dataproc_mng/organ_mng/load_depar_list.php",
            success: function(result) {
	            if (result.trim() == "") {

                	$("#depar_list").html("<tr><td colspan='5'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

	    	        $("#depar_list").html(result);

	            }
                hideMask();
           },
           error: getAjaxError
    });
}

//부서 팝업
var popDeparView = function(seq, cpn_seq, depar_name) {

    $.ajax({
            type: "POST",
            data: {
	    		"depar_code" : seq,
	    		"cpn_admin_seq" : cpn_seq,
                "depar_name" : depar_name
	       },
            url: "/ajax/dataproc_mng/organ_mng/load_depar_popup.php",
            success: function(result) {

        	    openRegiPopup(result, 400);
		    if (!cpn_seq) {

		    	changeHighDepar($("#sell_site").val());
		    }

           },
           error: getAjaxError
    });
}

//부서 저장
var saveDeparInfo = function(seq) {

    //부서명이 비었을때
    if ($("#depar_name").val() == "") {

        alert("부서명을 입력해주세요.");
	    $("#depar_name").focus();
        return false;
    }

    var formData = new FormData($("#depar_form")[0]);
        formData.append("depar_admin_seq", seq);
        formData.append("high_depar_code", $("#high_depar_code").val());

    $.ajax({
            type: "POST",
            data: formData,
            processData : false,
            contentType : false,
            url: "/proc/dataproc_mng/organ_mng/proc_depar_info.php",
            success: function(result) {
            console.log("리저트", result);
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadDeparList();
                    hideRegiPopup();

                } else if ($.trim(result) == "3") {

                    alert("변경완료");
                    loadDeparList();
                    hideRegiPopup();

                } else {

                    alert("저장에 실패했습니다.");
		}

           },
           error: getAjaxError
    });
}

//판매채널별 상위 부서 change
var changeHighDepar = function(val) {

    $.ajax({
            type: "POST",
            data: {
	    		"sell_site" : val
	    },
            url: "/ajax/dataproc_mng/organ_mng/load_high_depar.php",
            success: function(result) {

	    	$("#high_depar_code").html(result);

           },
           error: getAjaxError
    });
}

/**********************************************************************
                         * 관리자 관리 부분 *
 **********************************************************************/

//관리자 리스트 불러오기
var loadMngList = function(page) {

    showMask();

    $.ajax({
            type: "POST",
            data: {
	    		"page" : page,
			"list_num" : list_num
	    },
            url: "/ajax/dataproc_mng/organ_mng/load_mng_list.php",
            success: function(result) {
		    var list = result.split('★');
	            if ($.trim(list[0]) == "") {

                	$("#mng_list").html("<tr><td colspan='7'>검색된 내용이 없습니다.</td></tr>"); 

	            } else {

                	$("#mng_list").html(list[0]);
                	$("#mng_page").html(list[1]); 
			        $('select[name=list_set]').val(list_num);

	            }
                hideMask();
           },
           error: getAjaxError
    });
}

//관리자 팝업
var popMngView = function(seq, cpn_seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"mng_seq" : seq,
	    		"cpn_admin_seq" : cpn_seq
	       },
            url: "/ajax/dataproc_mng/organ_mng/load_mng_popup.php",
            success: function(result) {
                openRegiPopup(result, 520);
                activeDate();

                if (!cpn_seq) {

                    changeDepar($("#sell_site").val());
                }
           },
           error: getAjaxError
    });
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadMngList('1');
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(page) {

    loadMngList(page);

}

//관리자 저장
var saveMngInfo = function(seq) {

    //성명이 비었을때
    if ($("#mng_name").val() == "") {

        alert("성명을 입력해주세요.");
	    $("#mng_name").focus();
        return false;
    }

    //id가 비었을때
    if ($("#empl_id").val() == "") {

        alert("ID를 입력해주세요.");
	    $("#empl_id").focus();
        return false;
    }

    var formData = new FormData($("#mng_form")[0]);
        formData.append("mng_seq", seq);

    $.ajax({
            type: "POST",
            data: formData,
            processData : false,
            contentType : false,
            url: "/proc/dataproc_mng/organ_mng/proc_mng_info.php",
            success: function(result) {
                if($.trim(result) == "1") {
                    alert("저장했습니다.");
                    loadMngList();
                    hideRegiPopup();
                } else if ($.trim(result) == "3") {
                    alert("이미 존재하는 아이디입니다.");

                } else {
                    alert("저장에 실패했습니다.");
		        }
           },
           error: getAjaxError
    });
}

//비밀번호 초기화
var resetPasswd = function(seq) {

    if (!seq) {

        alert("비밀번호 기본 셋팅은 0000입니다.");
        return false;

    }

    $.ajax({
            type: "POST",
            data: {
	    		"mng_seq" : seq,
	       },
            url: "/proc/dataproc_mng/organ_mng/proc_mng_passwd.php",
            success: function(result) {
                if($.trim(result) == "1") {

                    alert("0000으로 초기화 했습니다.");

                } else {

                    alert("초기화에 실패했습니다.");
		}


           },
           error: getAjaxError
    });

}

//퇴사 처리
var resignMng = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"mng_seq" : seq,
	          },
            url: "/proc/dataproc_mng/organ_mng/proc_mng_resign.php",
            success: function(result) {
                if($.trim(result) == "1") {
                    alert("퇴사 처리되었습니다.");
                    hideRegiPopup();
                    loadMngList();
                } else {
                    alert("퇴사 처리에 실패했습니다.");
		}
           },
           error: getAjaxError
    });
}

//판매채널별 부서 change
var changeDepar = function(val) {

    $.ajax({
            type: "POST",
            data: {
	    		"sell_site" : val
	    },
            url: "/ajax/dataproc_mng/organ_mng/load_depar.php",
            success: function(result) {
	    	$("#depar_code").html(result);
           },
           error: getAjaxError
    });
}

//달력 활성화
var activeDate = function() {

    $('#enter_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });
}
