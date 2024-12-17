/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/06/16 왕초롱 생성
 *=============================================================================
 *
 */
$(document).ready(function() {
    getTotalDay();

});

var search_year = "";
var search_mon = "";
var search_sell_site = "";

//달력 마지막 날짜
var getTotalDay = function(year, mon, sell_site) {

    var url = "/ajax/calcul_mng/day_close/load_day_list.php";
    var data = {
        "year"  : $("#year").val(),
        "mon" : $("#mon").val(),
        "sell_site" : $("#sell_site").val()
    };
    var callback = function(result) {
        if (result.trim() == "") {
            $("#day_list").html(blank);
            return false;
        }
        $("#day_list").html(result);
    };

    ajaxCall(url, "html", data, callback);
}

//일마감 조건 바꾸기
var changeDayClose = function(day, close_yn, sell_site) {

    var url = "/proc/calcul_mng/day_close/proc_day_close.php";
    var data = {
        "day"  : day,
        "close_yn" : close_yn,
	"sell_site" : sell_site
    };
    var callback = function(result) {
        if (result.trim() == "1") {
	    if (close_yn == "Y") {
		alert("일마감 하였습니다.");
	    } else {
		alert("일마감을 풀었습니다.");
	    }
	    getTotalDay();
        } else {
	    alert("실패하였습니다.");
	}
    };

    ajaxCall(url, "html", data, callback);
}

