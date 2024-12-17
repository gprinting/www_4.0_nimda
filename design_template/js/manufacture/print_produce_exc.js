/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/03 harry 생성
 *=============================================================================
 *
 */

$(document).ready(function() {
    //$("#date").datepicker("setDate", new Date());
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');


    printExcsearch();
});

//검색
var printExcsearch = function() {
    var url = "/ajax/manufacture/print_produce_exc/load_print_produce_exc.php";
    var blank = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
    var data = { 
        "date"              : $("#date").val(),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val() 
    };
    var callback = function(result) {
        if (result.trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
};

/*
//이행추가
var addExec = function(seqno, fields, max, cur) {

    cur = cur*1;
    if (max < cur+1) {
        alert("0 이상으로 설정 할 수 없습니다.");
	return false;
    }
    var url = "/proc/manufacture/print_produce_exc/modi_print_produce_exc.php";
    var data = {
        "seqno"  : seqno,
	"fields" : fields,
	"value"  : cur+1,
	"tot_dvs": "P"
    };	
    var callback = function(result) {
        if (result == 1) {
            printExcsearch();
	} else {
            alert("이행증가를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//이행감소
var decExec = function(seqno, fields, cur) {
    cur = cur*1;	
    if (cur-1 < 0) {
        alert("0 미만으로 설정 할 수 없습니다.");
        return false;
    }
    var url = "/proc/manufacture/print_produce_exc/modi_print_produce_exc.php";
    var data = {
        "seqno"  : seqno,
	"fields" : fields,
	"value"  : cur-1,
	"tot_dvs": "M"
    };	
    var callback = function(result) {
        if (result == 1) {
            printExcsearch();
	} else {
            alert("이행감소를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
*/
