$(document).ready(function() {
    //$("#date1").datepicker("setDate", new Date());
    $("#date1").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date2").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date3").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date4").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date5").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date6").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');
    //getProduceOrdList();

    $("#date7").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date8").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#date9").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');
});

/*
//생산지시서 리스트
var getProduceOrdList = function() {

    var url = "/ajax/manufacture/process_ord_list/load_produce_ord_list.php";
    var data = { 
        "ord_dvs"     : $("#ord_dvs").val(),
        "date"        : $("#date").val()
    };

    var callback = function(result) {
        $("#list").html(result);
	if (!result) {
            $("#list").html("<table class=\"table fix_width100f\"><tr><td>데이터가 없습니다.</td></tr></table>");
	}
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
*/

var getProduceOrdList = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_printer.php";
    var data = {
        "print_etprs"     : $("#print_etprs1").val(),
        "date"        : $("#date1").val()
    };

    var callback = function(result) {
        var kind = $("#print_etprs1").val();
        if(kind == "") kind = "전체";
        $("#list1_title").html(kind);
        $("#list").html(result);
        if (!result) {
            $("#list").html("<table class=\"table fix_width100f\"><tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var downExcelProduceOrdListByPrinter = function() {
    var url = "/ajax/manufacture/process_ord_list/down_excel_produce_planning_by_printer.php";
    var data = {
        "print_etprs"     : $("#print_etprs1").val(),
        "date"        : $("#date1").val()
    };

    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr  = result.split('!');
            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
            downUrl += "&from=" + $("#from").val();
            downUrl += "&to=" + $("#to").val();
            downUrl += "&file_dvs=" + nameArr[0];

            $("#file_ifr").attr("src", downUrl);

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var downPdfProduceOrdListByNamecard = function() {
    var url = "/ajax/manufacture/process_ord_list/down_pdf_produce_planning_by_namecard.php";
    var data = {
        "date"        : $("#date9").val()
    };

    var callback = function(result) {
        downloadURI('/attach/gp/by_label_file/aligned2.pdf',"작업지시서.pdf")
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

function downloadURI(uri, name) {
    var link = document.createElement("a");
    link.download = name;
    link.href = uri;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    delete link;
}

var downExcelProduceOrdListByPaper = function() {
    var url = "/ajax/manufacture/process_ord_list/down_excel_produce_planning_by_paper.php";
    var data = {
        "print_etprs"     : $("#print_etprs2").val(),
        "date"        : $("#date2").val()
    };

    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr  = result.split('!');
            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
            downUrl += "&from=" + $("#from").val();
            downUrl += "&to=" + $("#to").val();
            downUrl += "&file_dvs=" + nameArr[0];

            $("#file_ifr").attr("src", downUrl);

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var downExcelProduceOrdListByTypset = function() {
    var url = "/ajax/manufacture/process_ord_list/down_excel_produce_planning_by_typset.php";
    var data = {
        "print_etprs"     : $("#print_etprs3").val(),
        "date"        : $("#date3").val()
    };

    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr  = result.split('!');
            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
            downUrl += "&from=" + $("#from").val();
            downUrl += "&to=" + $("#to").val();
            downUrl += "&file_dvs=" + nameArr[0];

            $("#file_ifr").attr("src", downUrl);

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var downExcelProduceOrdListByTypsetDirect = function() {
    var url = "/ajax/manufacture/process_ord_list/down_excel_produce_planning_by_typset_direct.php";
    var data = {
        "print_etprs"     : $("#print_etprs4").val(),
        "date"        : $("#date4").val()
    };

    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr  = result.split('!');
            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
            downUrl += "&from=" + $("#from").val();
            downUrl += "&to=" + $("#to").val();
            downUrl += "&file_dvs=" + nameArr[0];

            $("#file_ifr").attr("src", downUrl);

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var downExcelProduceOrdListByEnvelope = function() {
    var url = "/ajax/manufacture/process_ord_list/down_excel_produce_planning_by_envelope.php";
    var data = {
        "print_etprs"     : $("#print_etprs5").val(),
        "date"        : $("#date5").val()
    };

    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr  = result.split('!');
            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
            downUrl += "&from=" + $("#from").val();
            downUrl += "&to=" + $("#to").val();
            downUrl += "&file_dvs=" + nameArr[0];

            $("#file_ifr").attr("src", downUrl);

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var downExcelProduceOrdListByAfter = function() {
    var url = "/ajax/manufacture/process_ord_list/down_excel_produce_planning_by_after.php";
    var data = {
        "print_etprs"     : $("#print_etprs6").val(),
        "date"        : $("#date6").val()
    };

    var callback = function(result) {
        if (result === "FALSE") {
            alert("엑셀파일 생성에 실패했습니다.");
        } else if (result === "NOT_INFO") {
            alert("엑셀로 생성할 정보가 존재하지 않습니다.");
        } else {
            var nameArr  = result.split('!');
            var downUrl  = "/common/excel_file_down.php?name=" + nameArr[1];
            downUrl += "&from=" + $("#from").val();
            downUrl += "&to=" + $("#to").val();
            downUrl += "&file_dvs=" + nameArr[0];

            $("#file_ifr").attr("src", downUrl);

        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getProduceOrdListByPaper = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_paper.php";
    var data = {
        "print_etprs"     : $("#print_etprs2").val(),
        "date"        : $("#date2").val()
    };

    var callback = function(result) {
        var kind = $("#print_etprs2").val();
        if(kind == "") kind = "전체";
        $("#list2_title").html(kind);
        $("#list_paper").html(result);
        if (!result) {
            $("#list_paper").html("<table class=\"table fix_width100f\"><tr><td colspan=\"4\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var change_option = function(etprs) {
    var asd = $("#" + etprs).val();

    $("#print_etprs1").val(asd);
    $("#print_etprs2").val(asd);
    $("#print_etprs3").val(asd);
    $("#print_etprs4").val(asd);
    $("#print_etprs5").val(asd);
    $("#print_etprs6").val(asd);
    $("#print_etprs7").val(asd);
    $("#print_etprs8").val(asd);
    $("#print_etprs9").val(asd);
}

var change_date = function(date) {
    var asd = $("#" + date).val();

    $("#date1").val(asd);
    $("#date2").val(asd);
    $("#date3").val(asd);
    $("#date4").val(asd);
    $("#date5").val(asd);
    $("#date6").val(asd);
    $("#date7").val(asd);
    $("#date8").val(asd);
    $("#date9").val(asd);
}

var getProduceOrdListByCommercial = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_commercial.php";
    var data = {
        "print_etprs"     : $("#print_etprs8").val(),
        "date"        : $("#date8").val()
    };

    var callback = function(result) {
        var kind = $("#print_etprs8").val();
        if(kind == "") kind = "전체";
        $("#list8_title").html(kind);
        $("#list_commercial").html(result);
        if (!result) {
            $("#list_commercial").html("<table class=\"table fix_width100f\"><tr><td colspan=\"4\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getProduceOrdListByTypset = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_typset.php";
    var data = {
        "print_etprs"     : $("#print_etprs3").val(),
        "date"        : $("#date3").val()
    };

    var callback = function(result) {
        $("#list_typset").html(result);
        if (!result) {
            $("#list_typset").html("<table class=\"table fix_width100f\"><tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getProduceOrdListByTypsetDirect = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_typset_direct.php";
    var data = {
        "print_etprs"     : $("#print_etprs4").val(),
        "date"        : $("#date4").val()
    };

    var callback = function(result) {
        $("#list_typset_direct").html(result);
        if (!result) {
            $("#list_typset_direct").html("<table class=\"table fix_width100f\"><tr><td colspan=\"13\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getProduceOrdListByEnvelope = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_envelope.php";
    var data = {
        "print_etprs"     : $("#print_etprs5").val(),
        "date"        : $("#date5").val(),
        "after"        : $("#after5").val()
    };

    var callback = function(result) {
        $("#list_envelope").html(result);
        if (!result) {
            $("#list_envelope").html("<table class=\"table fix_width100f\"><tr><td colspan=\"12\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getProduceOrdListByAfter = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_after.php";
    var data = {
        "print_etprs"     : $("#print_etprs6").val(),
        "date"        : $("#date6").val()
    };

    var callback = function(result) {
        $("#list_after").html(result);
        if (!result) {
            $("#list_after").html("<table class=\"table fix_width100f\"><tr><td colspan=\"11\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var getProduceOrdListByNamecard = function() {
    var url = "/ajax/manufacture/process_ord_list/load_produce_planning_by_namecard.php";
    var data = {
        "print_etprs"     : $("#print_etprs9").val(),
        "date"        : $("#date9").val()
    };

    var callback = function(result) {
        $("#list_namecard").html(result.split("♪")[0]);
        $("#list_title9").html(result.split("♪")[1]);
        if (!result) {
            $("#list_namecard").html("<table class=\"table fix_width100f\"><tr><td colspan=\"11\">검색 된 내용이 없습니다.</td></tr></table>");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

function pagePrint(Obj) { 

    var PrintPage = window.open("about:blank",Obj.id); 

    PrintPage.document.open(); 
    PrintPage.document.write("<html><head><title></title><style type='text/css'>table th{border:1px solid #333; width:100px; text-align:center; height:20px; font-size:14px;}table td{border:1px solid #333; width:100px; text-align:right; height:20px; font-size:12px; padding-right:5px;}table{border-collapse:collapse; width:100%;}</style>\n</head>\n<body>" + Obj.innerHTML + "\n</body></html>"); 
    PrintPage.document.close(); 

    PrintPage.document.title = "인쇄 생산 계획서"; 
    PrintPage.print(PrintPage.location.reload()); 
}

var tabInit = function() {

    $("#paper_op_1").hide();
    $("#paper_op_2").hide();
    $("#paper_op_3").hide();
    $("#paper_op_4").hide();
    $("#paper_op_5").hide();
    $("#paper_op_6").hide();
    $("#paper_op_7").hide();
    $("#paper_op_8").hide();
    $("#paper_op_9").hide();
}

var tabView = function(dvs) {

    tabInit();
    $("#paper_op_" + dvs).show();
}


var printArea = function(print_list) {
    const setting = "width=890, height=1000";
    const objWin = window.open('', 'print', setting);
    objWin.document.open();
    objWin.document.write('<html><head><title>분석 레포트 </title>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/print.css"/>');
    //objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/common.css"/>');
    //objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/basic_manager.css"/>');
    //objWin.document.write('<link rel="stylesheet" type="text/css" href="/design_template/css/basic_manager.css"/>');
    objWin.document.write('</head><body>');
    objWin.document.write($('#' + print_list).html());
    objWin.document.write('</body></html>');
    objWin.focus();
    objWin.document.close();

    setTimeout(function(){ objWin.print();  }, 300);
    //objWin.close();
}