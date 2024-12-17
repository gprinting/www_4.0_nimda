/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/11/03 harry 생성
 * 2017/09/01 이청산 수정
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

    $("#date_data").val($("#date").val());
    printOrdSearch();
});

//검색
var printOrdSearch = function() {
    var url = "/ajax/manufacture/print_produce_ord/load_print_produce_ord.php";
    var blank = "<tr><td colspan=\"4\">검색 된 내용이 없습니다.</td></tr>";
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

//수정모드
var PrintOrdModiMode = function() {
    var url = "/ajax/manufacture/print_produce_ord/load_print_produce_ord_modi_mode.php";
    var data = { 
        "date"              : $("#date").val(),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val() 
    };
    var callback = function(result) {
        $("#list").html(result);
        $("#col").html("관리");
        $("#btn").hide();
        $("#modi_button").hide();
        $("#comp_button").show();
        $("#canc_button").show();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//지시수정모드 지시 추가
var plusLine = function(btn) {

    $(btn).parent().find("button").eq(0).hide();
    $(btn).parent().find("button").eq(1).show();
    $(".li").removeClass("selbg");
    var url = "/ajax/manufacture/print_produce_ord/load_plus_line.php";
    var data = {};
    var callback = function(result) {
        $("#list").append(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//지시수정모드 지시 제거
var minusLine = function(tr) {
    $(tr).parents("tr").remove();
}

//벨리데이션
var validDirection = function() {

    var valid = false;
    //validation
    var result = [];
    $("select[name='selManu[]']").each(function(idx, select) { 
	
        var selVal = $(select).val();
        if ($.inArray(selVal, result) === -1) {
            result.push(selVal);
        } else {
            alert("제조사는 중복선택이 불가능합니다.");
            $(select).focus();
            valid = true;
            return false;
        }
    });

    if (valid) {
        return valid;
    }

    $("input[name='seoul[]']").each(function(idx, select) {
        var selVal = $(select).val();

        if (selVal == "") {
            alert("지시값을 입력해주세요.");
            $(select).focus();
	    valid = true;
    	    return false;
	}
    });

    if (valid) {
        return valid;
    }

    $("input[name='region[]']").each(function(idx, select) {
        var selVal = $(select).val();

        if (selVal == "") {
            alert("지시값을 입력해주세요.");
            $(select).focus();
            valid = true;
            return false;
	}
    });

    return valid;
}

//지시완료
var PrintOrdCompMode = function() {
    if (validDirection()) {
        return false;
    }

    $("select[name='selManu[]']").each(function(idx, select) {
	$(select).attr("disabled",false);
    });	
     
    var url = "/proc/manufacture/print_produce_ord/regi_print_produce_ord.php";
    var data = $("#frm").serialize();
    $("#date_data").val($("#date").val());
    var callback = function(result) {
        if (result == 1) {
            $("#col").html("합계");
            $("#modi_button").show();
            $("#btn").show();
            $("#comp_button").hide();
            $("#canc_button").hide();
            printOrdSearch();
        } else {
            alert("지시등록을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//지시취소
var PrintOrdCancel = function() {
    $("#col").html("합계");
    $("#modi_button").show();
    $("#btn").show();
    $("#comp_button").hide();
    $("#canc_button").hide();
    printOrdSearch();
}

//인쇄 지시서 인쇄
function pagePrint(Obj) { 
    var W = Obj.offsetWidth;        //screen.availWidth; 
    var H = Obj.offsetHeight;       //screen.availHeight;

    //var features = "menubar=no,toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,width=" + W + ",height=" + H + ",left=0,top=0,fullscreen=yes"; 
    var features = "menubar=no,toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,left=0,top=0,fullscreen=yes"; 
    var PrintPage = window.open("about:blank",Obj.id,features); 

    PrintPage.document.open(); 
    PrintPage.document.write("<html><head><title></title><style type='text/css'>table th{border:1px solid #333; width:100px; text-align:center; height:80px; font-size:24px;}table td{border:1px solid #333; width:100px; text-align:right; height:80px; font-size:15px; padding-right:5px;}table{border-collapse:collapse; width:100%;}</style>\n</head>\n<body>" + Obj.innerHTML + "\n</body></html>"); 
    PrintPage.document.close(); 

    //PrintPage.document.title = document.domain; 
    PrintPage.document.title = "인쇄 생산 지시서"; 
    PrintPage.print(PrintPage.location.reload()); 
}
