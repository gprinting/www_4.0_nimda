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


    searchStockList(30, 1);
});

//보여줄 페이지 수
var showPage = 30;

//선택 조건으로 검색
var searchStockList = function(showPage, page) {

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_stock_list.php";
    var blank = "<tr><td colspan=\"8\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "date"        : $("#date").val(), 
        "name"        : $("#paper_name").val(), 
        "dvs"         : $("#paper_dvs").val(), 
        "color"       : $("#paper_color").val(), 
        "basisweight" : $("#paper_basisweight").val(), 
        "manu"        : $("#paper_manu").val() 
    };
    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(rs[0]);
        $("#page").html(rs[1]);
        $("#standard").html("&lt;" + $("#date").val() + " 00:00:00 기준&gt;");
        $("#detail").hide();
    };

    data.showPage      = showPage;
    data.page          = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

// 제지사 선택시 셀렉트박스 설정
var selectPaperManu = function(el) {

    if ($("#" + el + "manu").val() == "") {
        $("#" + el + "name").html("<option value=\"\">종이(전체)</option>");
        $("#" + el + "name").attr("disabled", true);
        $("#" + el + "dvs").html("<option value=\"\">구분(전체)</option>");
        $("#" + el + "dvs").attr("disabled", true);
        $("#" + el + "color").html("<option value=\"\">색상(전체)</option>");
        $("#" + el + "color").attr("disabled", true);
        $("#" + el + "basisweight").html("<option value=\"\">평량(전체)</option>");
        $("#" + el + "basisweight").attr("disabled", true);
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_manu.php";
    var data = { 
        "manu" : $("#" + el + "manu").val() 
    };

    var callback = function(result) {
        $("#" + el + "name").html(result);
        $("#" + el + "name").removeAttr("disabled");
        $("#" + el + "dvs").attr("disabled", true);
        $("#" + el + "dvs").html("<option value=\"\">구분(전체)</option>");
        $("#" + el + "color").attr("disabled", true);
        $("#" + el + "color").html("<option value=\"\">색상(전체)</option>");
        $("#" + el + "basisweight").attr("disabled", true);
        $("#" + el + "basisweight").html("<option value=\"\">평량(전체)</option>");

	if (el == "") {
            $("#now_stock_amt").html("종이를 선택해주세요.");
	    showBgMask();
	}
    };

    ajaxCall(url, "html", data, callback);
}

// 종이명 선택시 셀렉트박스 설정
var selectPaperName = function(el) {

    if ($("#" + el + "name").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_name.php";
    var data = { 
        "manu" : $("#" + el + "manu").val(),
        "name" : $("#" + el + "name").val() 
    };

    var callback = function(result) {
        var arr = result.split("♪");
        $("#" + el + "dvs").html(arr[0]);
        $("#" + el + "color").html(arr[1]);
        $("#" + el + "basisweight").html(arr[2]);
        
        $("#" + el + "dvs").removeAttr("disabled");
        $("#" + el + "color").removeAttr("disabled");
        $("#" + el + "basisweight").removeAttr("disabled");

	if (el == "") {
            $("#now_stock_amt").html("종이를 선택해주세요.");
	    showBgMask();
	}
    };

    ajaxCall(url, "html", data, callback);
}

// 종이구분 선택시 구분 셀렉트박스 설정
var selectPaperDvs = function(el) {

    if ($("#" + el + "dvs").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_dvs.php";
    var data = { 
        "manu" : $("#" + el + "manu").val(),
        "name" : $("#" + el + "name").val(),
        "dvs"  : $("#" + el + "dvs").val() 
    };

    var callback = function(result) {
        var arr = result.split("♪");
        $("#" + el + "color").html(arr[0]);
        $("#" + el + "basisweigth").html(arr[1]);

	if (el == "") {
            $("#now_stock_amt").html("종이를 선택해주세요.");
	    showBgMask();
	}
    };

    ajaxCall(url, "html", data, callback);
}

// 종이색상 선택시 구분 셀렉트박스 설정
var selectPaperColor = function(el) {

    if ($("#" + el + "name").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_color.php";
    var data = { 
        "manu"  : $("#" + el + "manu").val(),
        "name"  : $("#" + el + "name").val(), 
        "dvs"   : $("#" + el + "dvs").val(), 
        "color" : $("#" + el + "color").val() 
    };

    var callback = function(result) {
        $("#" + el + "basisweight").html(result);

	if (el == "") {
            $("#now_stock_amt").html("종이를 선택해주세요.");
	    showBgMask();
	}
    };

    ajaxCall(url, "html", data, callback);
}

// 종이색상 선택시 구분 셀렉트박스 설정
var selectPaperBasisweight = function() {

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_stock_amt.php";
    var data = { 
        "manu"        : $("#manu").val(),
        "name"        : $("#name").val(), 
        "dvs"         : $("#dvs").val(), 
        "color"       : $("#color").val(),
        "basisweight" : $("#basisweight").val() 
    };

    var callback = function(result) {
        $("#now_stock_amt").html(result);
	showBgMask();
    };

    ajaxCall(url, "html", data, callback);
}

//종이 재고 상세보기
var paperStockView = function(seqno) {
   
    $("#detail").show();
    var url = "/json/manufacture/paper_stock_mng/load_paper_detail_info.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        makePieChart("day_chart", "오늘 재고/사용률", result.day);
        makePieChart("month_chart","이번달 재고/사용률" , result.month);
    };

    showMask();
    ajaxCall(url, "json", data, callback);
}

//재고조정 팝업 호출
var showStockMng = function() {
 
    var url = "/ajax/manufacture/paper_stock_mng/load_stock_mng_popup.php";
    var data = { 
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//파이 차트 생성
var makePieChart = function(el, title, data) {

    Highcharts.chart(el, {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: title
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
        enabled: false
    },
    showInLegend: true
        }
    },
    credits: {
        enabled: false
    },
    series: [{
        name: '입고대비 비율',
        colorByPoint: true,
        data: data
        }]
    });
}

//재고조정 등록
var regiStockCtrl = function() {

    var url = "/proc/manufacture/paper_stock_mng/modi_paper_stock.php";
    var data = { 
        "manu"          : $("#manu").val(),
        "name"          : $("#name").val(), 
        "dvs"           : $("#dvs").val(), 
        "color"         : $("#color").val(),
        "basisweight"   : $("#basisweight").val(), 
        "realstock_amt" : $("#realstock_amt").val(),
        "stor_yn"       : $(':radio[name="stor_yn"]:checked').val(),
        "amt"           : $("#amt").val(),
        "worker"        : $("#worker").val(),
        "adjust_reason" : $("#adjust_reason").val()
       
    };

    var callback = function(result) {
        if (result == 1) {
            alert("종이발주를 취소 하였습니다..");
        } else {
            alert("종이발주취소를 실패 하였습니다. \n 관리자에게 문의 바람니다.");
        }
    };

    ajaxCall(url, "html", data, callback);
}
