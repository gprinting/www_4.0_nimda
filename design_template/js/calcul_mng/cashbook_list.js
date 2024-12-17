/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/29 왕초롱 생성
 *============================================================================
 *
 */

$(document).ready(function() {

    activeDate();
    evidDateSet('0');
    loadAccCashbookList(1);

});
var page = 1; //페이지
var list_num = 30; //리스트 갯수
var search_type = "acc";

//달력 활성화
var activeDate = function() {

    //일자별 검색 datepicker 기본 셋팅
    $('#acc_date_from').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   

    $('#acc_date_to').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

    $('#path_date_from').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   

    $('#path_date_to').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });
}

//탭 바꿈
var changeSearchType = function(val) {

    search_type = val;
    if (val == "acc") {

	 var target = "acc";
	 var nontarget = "path";

    } else {

	 var target = "path";
	 var nontarget = "acc";
     evidDateSet('0');

    }

    $("#" + target + "_price").show();
    $("#" + target + "_tr").show();
    $("#" + target +"_list").show();
    $("#" + nontarget + "_price").hide();
    $("#" + nontarget + "_tr").hide();
    $("#" + nontarget + "_list").hide();

}

//검색 날짜 범위 설정
var evidDateSet = function(num) {

    if (num == "all") {
        console.log("써치타입", search_type);
        $("#" + search_type + "_date_from").val("");
        $("#" + search_type + "_date_to").val("");
        console.log("일단여기는타지");
        return false;
    }

    var day = new Date();
    var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));

    $("#" + search_type + "_date_from").datepicker("setDate", d_day);
    $("#" + search_type + "_date_to").datepicker("setDate", '0');
};

//계정과목 상세 불러오기
var loadAccDetail = function(el, dvs) {

    //계정과목 미선택시
    if ($(el).val() == "") {

	$(el + "_detail").html("<option value=\"\">전체</option>");
	return false;

    }

    //입출금경로 미선택시
    $.ajax({

        type: "POST",
        data: {
	        "acc_subject" : $(el).val(),
            "dvs"         : dvs
        },
        url: "/ajax/calcul_mng/cashbook_list/load_acc_detail.php",
        success: function(result) {
	    
	    	$(el + "_detail").html(result);

        }, 
        error: getAjaxError
    });

}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    searchListType(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    searchListType(page);

}

var searchListType = function(pg) {

    if (search_type == "acc") {

	loadAccCashbookList(pg);

    } else {

	loadPathCashbookList(pg);

   }
}

//계정날짜별 리스트 불러오기
var loadAccCashbookList = function(pg) {

    var formData = new FormData($("#acc_form")[0]);
        formData.append("page", pg);
        formData.append("list_num", list_num);

    showMask();

    $.ajax({
        type: "POST",
        data: formData,
	processData : false,
	contentType : false,
        url: "/ajax/calcul_mng/cashbook_list/load_acc_cashbook_list.php",
        success: function(result) {
		var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#acc_list").html("<tr><td colspan='8'>검색된 내용이 없습니다.</td></tr>"); 
                $("#income_sum").html("0원");
                $("#expen_sum").html("0원");
                $("#acc_sum").html("0원");

	    } else {

                $("#acc_list").html(list[0]);
                $("#cashbook_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);
                $("#income_sum").html(list[2]);
                $("#expen_sum").html(list[3]);
                $("#acc_sum").html(list[4]);

	    }
	    hideMask();
        }, 
        error: getAjaxError
    });
}

//입출금경로별 리스트 불러오기
var loadPathCashbookList = function(pg) {

    var formData = new FormData($("#path_form")[0]);
        formData.append("page", pg);
        formData.append("list_num", list_num);

    showMask();

    $.ajax({

        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/ajax/calcul_mng/cashbook_list/load_path_cashbook_list.php",
        success: function(result) {
		var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#path_list").html("<tr><td colspan='8'>검색된 내용이 없습니다.</td></tr>"); 
                $("#trsf_income_sum").html("0원");
                $("#trsf_expen_sum").html("0원");

	    } else {

                $("#path_list").html(list[0]);
                $("#cashbook_page").html(list[1]); 
                $('select[name=list_set]').val(list_num);
                $("#trsf_income_sum").html(list[2]);
                $("#trsf_expen_sum").html(list[3]);
                $("#path_sum").html(list[4]);
	    }
	    hideMask();
        }, 
        error: getAjaxError
    });
}

//입출금경로 상세 불러오기
var loadPathDetail = function() {

    //입출금경로 미선택시
    if ($("#depo_path").val() == "") {

        $("#depo_path_detail").html("<option value=\"\">선택</option>");
        return false;

    }

    $.ajax({

        type: "POST",
        data: {
		    "depo_path" : $("#depo_path").val()
        },
        url: "/ajax/calcul_mng/cashbook_list/load_path_detail.php",
        success: function(result) {
	    
	    	$("#depo_path_detail").html(result);
        }, 
        error: getAjaxError
    });
}



