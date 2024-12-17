/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/19 왕초롱 생성
 *============================================================================
 *
 */

$(document).ready(function() {
    loadMngAuthList(1);
});

var page = 1; //페이지
var list_num = 30; //리스트 갯수
var a_mng = "";
var b_mng = "";
var search_type = "";

//권한관리 리스트 불러오기
var loadMngAuthList = function(pg) {

    showMask();

    $.ajax({

        type: "POST",
        data: {
                "page" : pg,
                "list_num" : list_num,
                "sell_site" : $("#sell_site").val()
        },
        url: "/ajax/dataproc_mng/auth_mng/load_mng_auth_list.php",
        success: function(result) {
		var list = result.split('★');
            if($.trim(list[0]) == "") {

                $("#auth_list").html("<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {

                $("#auth_list").html(list[0]);
                $("#auth_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);

	        }
            hideMask();
        }, 
        error: getAjaxError
    });
}

//접근권한관리 팝업 불러오기
var popMngAuthView = function(seq) {

    $.ajax({
        type: "POST",
        data: {
            "empl_seq" : seq
        },
        url: "/ajax/dataproc_mng/auth_mng/load_mng_auth_popup.php",
        success: function(result) {
            openRegiPopup(result, 700);
        }, 
        error: getAjaxError
    });
}

//관리자 접근권한관리 저장
var saveMngAuth = function(seq) {

    var formData = new FormData($("#auth_form")[0]);
        formData.append("select_empl_seqno", seq);

    $.ajax({

        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
	    url: "/proc/dataproc_mng/auth_mng/proc_mng_auth.php",
        success: function(result) {
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadMngAuthList(1);
                    hideRegiPopup();

                } else {

                    alert("실패했습니다.");
                }
        }, 
        error: getAjaxError
    });

}

//직원명 가져오기
var loadEmplName = function(event, search_str, type) {
    
    if (event.keyCode != 13) {
        return false;
    }

    search_type = type;
    showMask();

    $.ajax({
            type: "POST",
            data: {
                "search_str" : search_str,
	        	"type"       : type,
                "sell_site"  : $("#sell_site").val()
            },
            url: "/ajax/dataproc_mng/auth_mng/load_empl_name.php",
            success: function(result) {
                $("#search_list").html(result);
                hideMask();
                openPopPopup($("#search_name").html(), 440);
                showPopMask();
           },
        error: getAjaxError
    });
}

//팝업 안 검색 직원명 가져오기
var loadEmplNamePopupList = function(event, dvs) {
    
    if (dvs == "enter") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    showMask();

    $.ajax({
            type: "POST",
            data: {
                "search_str" : $("#search").val(),
		        "type"       : search_type,
                "sell_site" : $("#sell_site").val()
            },
            url: "/ajax/dataproc_mng/auth_mng/load_empl_name.php",
            success: function(result) {
                $("#search_list").html(result);
                hideMask();
                showPopMask();
           },
        error: getAjaxError
    });
}


//관리자 권한 복사 저장
var saveCopyMngAuth = function() {

    if (a_mng == "") {

        alert("사용자A 엔터 검색후 관리자를 선택해주세요.");
	    $("#a_mng").focus();
        return false;
        
    }

    if (b_mng == "") {

        alert("사용자B 엔터 검색후 관리자를 선택해주세요.");
	    $("#b_mng").focus();
        return false;
        
    }

    $.ajax({

        type: "POST",
        data: {
                "a_mng" : a_mng,
                "b_mng" : b_mng
        },
        url: "/proc/dataproc_mng/auth_mng/proc_copy_mng_auth.php",
        success: function(result) {
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadMngAuthList(1);
                    hideRegiPopup();

                } else {

                    alert("실패했습니다.");
                }
        }, 
        error: getAjaxError
    });
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {
    list_num = val;
    loadMngAuthList(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {
    page = pg;
    loadMngAuthList(page);
}

//팝업 검색된 관리자A 클릭시
var aMngClick = function(val) {
    var tmp = val.split("♪@♭");
    a_mng = tmp[0];
    $("#a_mng").val(tmp[1]);
    hideSearchPop();
}

//팝업 검색된 관리자B 클릭시
var bMngClick = function(val) {
    var tmp = val.split("♪@♭");
    b_mng = tmp[0];
    $("#b_mng").val(tmp[1]);
    hideSearchPop();
}

//검색팝업 닫기
var hideSearchPop = function() {
    hidePopPopup();
    showBgMask();
}
