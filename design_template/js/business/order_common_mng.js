/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/23 엄준현 생성
 * 2015/12/29 임종건 일자 검색 관련 함수 수정
 * 2015/12/29 임종건 회원 팝업 검색 수정
 *=============================================================================
 *
 */

$(document).ready(function() {
    // 데이트 피커 초기화
    $("#date_team_from").datepicker({
        autoclose : true,
        format    : "yyyy-mm-dd",
        todayBtn  : "linked",
        todayHighlight: true,
    });
    $("#date_team_to").datepicker({
        autoclose : true,
        format    : "yyyy-mm-dd",
        todayBtn  : "linked",
        todayHighlight: true,
    });
    $("#date_memb_from").datepicker({
        autoclose : true,
        format    : "yyyy-mm-dd",
        todayBtn  : "linked",
        todayHighlight: true,
    });
    $("#date_memb_to").datepicker({
        autoclose : true,
        format    : "yyyy-mm-dd",
        todayBtn  : "linked",
        todayHighlight: true,
    });

    //일자 오늘로 세팅
    setDateMemb("0");
    setDateTeam("0");

    // 팀 별 검색에서 팀구분 값 로드
    loadDeparInfo();

    // 페이징 목록생성
//    paging.exec(1);
    cndSearch.exec('','');
});

/**
 * @brief 날짜 범위 설정 - 공통
 *
 * @param num = 범위
 */
var setDateVal = function(num, dvs) {

    var day = new Date();
    var time = day.getHours();
    var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));
    var last = new Date(day - (365 * 1000 * 60 * 60 * 24));

    if (num === "all") {
        $("#date_" + dvs + "_from").val("");
        $("#time_" + dvs + "_from").val("0");
        $("#date_" + dvs + "_to").val("");
        $("#time_" + dvs + "_to").val("0");

        return false;
    } else if (num === "last") {
        $("#date_" + dvs + "_from").datepicker("setDate", last);
        $("#time_" + dvs + "_from").val("0");
        $("#date_" + dvs + "_to").datepicker("setDate", last);
        $("#time_" + dvs + "_to").val(time);
        return false;
    }

    $("#date_" + dvs + "_from").datepicker("setDate", d_day);
    $("#time_" + dvs + "_from").val("0");
    $("#date_" + dvs + "_to").datepicker("setDate", '0');
    $("#time_" + dvs + "_to").val(time);
}

/**
 * @brief 회원별 검색 날짜 범위 설정
 *
 * @param num = 범위
 */
var setDateMemb = function(num) {
    setDateVal(num, "memb");
};

/**
 * @brief 팀별 검색 날짜 범위 설정
 *
 * @param num = 범위
 */
var setDateTeam = function(num) {
    setDateVal(num, "team");
};

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "tabDvs"     : "memb",
    "page"       : "1",
    "listSize"   : "30",
    "searchFlag" : false,
    "exec"       : function(dvs, val) {
        this.searchFlag = true;
        // 리스트 사이즈 변경시 마지막 seqno 초기화
        if (dvs === 'l') {
            this.listSize = val;
            this.page = 1;
            paging.exec(1);
        }
        
        // 페이지 변경시 활성화 변경
        if (dvs === 'p') {
            this.page = val;
            $("#paging").find("a.active").removeClass("active");
            $("#page_" + val).addClass("active");
        }
        
        var url = "/ajax/business/order_common_mng/load_order_list.php";
        var data = {
            "page"       : this.page,
            "list_size"  : this.listSize,
            "tab_dvs"    : this.tabDvs,
            "last_seqno" : $("#last_seqno").val()
        };
        var callback = function(result) {
            var rs = result.split("♪");
            $("#order_list").html(rs[0]);
            $("#order_total").html(rs[1]);
            $("#paging").html(rs[2]);
        };

        if (checkBlank($("#office_nick").val())) {
            $("#member_seqno").val("");
        }

	if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
            alert("검색창 팝업을 이용하시고 검색해주세요.");
	    $("#office_nick").focus();
	    return false;
	}

        if (this.tabDvs === "memb") {
            data.sell_site     = $("#sell_site").val();
            data.cate_sortcode = $("#cate_bot").val();
            data.member_seqno  = $("#member_seqno").val();
            data.from_date     = $("#date_memb_from").val();
            data.from_time     = $("#time_memb_from").val();
            data.to_date       = $("#date_memb_to").val();
            data.to_time       = $("#time_memb_to").val();
            data.status        = $("#status_memb").val();
            data.status_proc   = $("#status_proc_memb").val();
        } else {
            data.depar_code  = $("#depar_code").val();
            data.from_date   = $("#date_team_from").val();
            data.from_time   = $("#time_team_from").val();
            data.to_date     = $("#date_memb_to").val();
            data.to_time     = $("#time_memb_to").val();
            data.status      = $("#status_team").val();
            data.status_proc = $("#status_proc_team").val();
        }

        showMask();

        ajaxCall(url, "html", data, callback);
    }
};

/**
 * @brief 주문 상태 상세 조회팝업 출력
 *
 * @param seq = 주문일련번호
 */
var showOrderDetail = function(seqno) {

    $("#seqno").val(seqno);	
    var f = document.frm;
    window.open("", "POP");
    f.action = "/business/order_common_mng_detail_popup.html";
    f.target = "POP";
    f.method = "POST";
    f.submit();
    return false; 
};
