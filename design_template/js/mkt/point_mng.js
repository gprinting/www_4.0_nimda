/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2015/12/14 왕초롱 생성
 *============================================================================
 *
 */
 $(document).ready(function() {

    loadPointStats();

});

var alertMSG = function(seqno){
    regiPopEtprs(seqno);
}

var alertLIST = function(seqno){
    regiPopEtprs2(seqno);
}

//매입업체 등록 팝업 레이어
var regiPopEtprs = function(val) {
    var url = "/ajax/mkt/point_mng/load_point_mng_pop.php";
    var data = {
        "seqno"   : val
    }
    var callback = function(result) {
	hideMask();
        showBgMask();
        openRegiPopup(result, 1200);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//매입업체 등록 팝업 레이어
var regiPopEtprs2 = function(val) {
    var url = "/ajax/mkt/point_mng/load_point_list_pop.php";
    var data = {
        "seqno"   : val
    }
    var callback = function(result) {
	hideMask();
        showBgMask();
        openRegiPopup(result, 1200);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var regicontext = function(){

    var formData = $("#regi_form").serializeArray();

    var url = "/proc/mkt/point_mng/regi_point_con.php";
    var callback = function(result) {
	alert(result);
        hideRegiPopup();
       // searchEtprsList();
    };

    showMask();
    ajaxCall(url, "html", formData, callback);

}

//관리 멤버 불러오기
var load_member = function(sPage, page)  {
    var data = {
        "search_dvs"   : $("#search_dvs").val(),
        "keyword"      : $("#keyword").val(),
        "version"      : $("#version option:selected").val(),
        "member_seqno" : $("#member_seqno2").val(),
        "date_from"    : $("#acc_date_from3").val(),
        "date_to"      : $("#acc_date_to3").val(),
    	"showPage"     : sPage,
    	"page"         : page
    };

    var url = "/proc/mkt/point_mng/load_member_check.php";

    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";

    showMask();
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
                $("#member_list").html(blank);
            } else {
                $("#member_list").html(rs[0]);
            }

          //  $("#att_page").html(rs[1]);
            $("#point_text7").html(rs[2]);
        }   
    });
}

// 포인트 정책 저장
var sendPoints = function() {

    var formData = $("#point_am").serialize();
    var id = $("#mb_id_point").val();
    var radio = $('input:radio[name="add_minus_check"]:checked').val();
    
    if(radio == "add"){
        radio = "지급(+)";    
    }else if( radio == "minus") {
        radio = "차감(-)";
    }else {
        radio = "관리등록";
    }

    var point = $("#send_points").val();
    var reason = $("#add_minus_reason").val();

    if(confirm("아이디 : " + id + "\n 포인트 : " + point + radio + "\n 지금사유 : " + reason + "\n 위 내용으로 "+radio+"하겠습니까?" )){
        $.ajax({
            type: "POST",
            data: formData,
            url: "/proc/mkt/point_mng/proc_point_send.php",
            success: function(result) {
                if(result == "1") {
                    alert(radio + "이 처리되었습니다.");
                } else {
                    alert(radio + "이 실패했습니다.");
                }
            }, 
            error: getAjaxError
        });
    }else{
        alert("취소 되었습니다.");
        
    }

   /* $.ajax({
        type: "POST",
        data: formData,
        url: "/proc/mkt/point_mng/proc_point_send.php",
        success: function(result) {
            if(result == "1") {
                alert("수정했습니다.");
            } else {
                alert("실패했습니다.");
            }
        }, 
        error: getAjaxError
    }); */
}

// 출석체크 정책 저장
var savePointAtt = function() {

    var formData = $("#Attform").serialize();

    $.ajax({
        type: "POST",
        data: formData,
        url: "/proc/mkt/point_mng/proc_attendance.php",
        success: function(result) {
            if(result == "1") {
                alert("수정했습니다.");
            } else {
                alert("실패했습니다.");
            }
        }, 
        error: getAjaxError
    });
}

//포인트 정책 저장
var savePointPolicy = function() {

    var formData = $("#point_form").serialize();

    $.ajax({

        type: "POST",
        data: formData,
        url: "/proc/mkt/point_mng/proc_point_policy.php",
        success: function(result) {
            if(result == "1") {

                alert("수정했습니다.");

            } else {

                alert("실패했습니다.");
            }
        }, 
        error: getAjaxError
    });
}

//포인트 정책 불러오기
var loadPointPolicy = function() {

    showMask();

    $.ajax({

        type: "POST",
        data: {},
        url: "/ajax/mkt/point_mng/load_point_policy.php",
        success: function(result) {
		var tmp = result.split('♪♭@');

        $("#give_join_point").val(tmp[0]);
        $("#order_rate").val(tmp[1]);
        hideMask();

        }, 
        error: getAjaxError
    });

}

//등급별 포인트 통계
var loadPointStats = function() {

    showMask();

    $.ajax({

        type: "POST",
        data: {
                "sell_site"  : $("#sell_site").val(),
                "year"       : $("#year").val(),
                "mon"        : $("#mon").val()
        },
        url: "/ajax/mkt/point_mng/load_point_stats.php",
        success: function(result) {
		    var point = result.split('♪♭‡');

            $("#join_point").val(point[0]);
            $("#order_point").val(point[1]);
            $("#admin_point").val(point[2]);
            $("#grade_point").val(point[3]);
            $("#tot_point").val(point[4]);
            $("#recoup_point").val(point[5]);

	        hideMask();
        }, 
        error: getAjaxError
    });
}

//보여줄 페이지 수
var showPage = "";
var showPage2 = "";


$(document).ready(function() {
//    dateSet('0');
    loadDeparInfo();
	PointListAjaxCall(30, 1);
    pUseListAjaxCall(30, 1);

});

// 포인트 리스트 호출
var PointListAjaxCall = function(sPage, page) {
    var data = {
        "search_dvs"   : $("#search_dvs").val(),
        "keyword"      : $("#keyword").val(),
        "version"      : $("#version option:selected").val(),
    	"showPage2"     : sPage,
    	"page2"         : page,
        "member_seqno" : $("#member_seqno2").val(),
        "date_from"    : $("#acc_date_from").val(),
        "date_to"      : $("#acc_date_to").val()
    };
    var url = "/proc/mkt/point_mng/point_check_list.php";

    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";

    showMask();
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
                $("#point_lists").html(blank);
            } else {
                $("#point_lists").html(rs[0]);
            }
            $("#point_page").html(rs[1]);
            $("#point_text").html(rs[2]);
            $("#point_text5").html(rs[3]);
            $("#point_text6").html(rs[2]-rs[3]);
        }   
    });
}
//회원 검색
var searchPoint = function() {
    PointListAjaxCall(30, 1);
}

//페이지 이동
var movePage2 = function(val) {
    PointListAjaxCall(showPage2, val);
}

//보여줄 페이지 수 설정
var showPageSetting2 = function(val) {
    showPage2 = val;
    PointListAjaxCall(val, 1);
}


$(document).ready(function() {
//    dateSet('0');
    loadDeparInfo();
    activeDate();
    evidDateSet('0');
    AttListAjaxCall(30, 1);
   // pUseListAjaxCall(30, 1);
});

//검색 날짜 범위 설정
var evidDateSet = function(num) {

    if (num == "all") {
        $("#" + search_type + "_date_from").val("");
        $("#" + search_type + "_date_to").val("");
        return false;
    }
    var day = new Date();
    var d_day = new Date(day - (num * 1000 * 60 * 60 * 24));

    $("#" + "acc_date_from").datepicker("setDate", d_day);
    $("#" + "acc_date_to").datepicker("setDate", '0');
    $("#" + "acc_date_from2").datepicker("setDate", d_day);
    $("#" + "acc_date_to2").datepicker("setDate", '0');
    $("#" + "acc_date_from3").datepicker("setDate", d_day);
    $("#" + "acc_date_to3").datepicker("setDate", '0');
};

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

    $('#acc_date_from2').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   

    $('#acc_date_to2').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

    $('#acc_date_from3').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   

    $('#acc_date_to3').datepicker({
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


//출석체크 리스트 호출
var AttListAjaxCall = function(sPage, page) {
    var data = {
        "search_dvs"   : $("#search_dvs").val(),
        "keyword"      : $("#keyword").val(),
        "version"      : $("#version option:selected").val(),
        "member_seqno" : $("#member_seqno2").val(),
        "date_from"    : $("#acc_date_from3").val(),
        "date_to"      : $("#acc_date_to3").val(),
    	"showPage"     : sPage,
    	"page"         : page
    };

    var url = "/proc/mkt/point_mng/load_attendance_check.php";

    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";

    showMask();
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
                $("#att_list").html(blank);
            } else {
                $("#att_list").html(rs[0]);
            }

            $("#att_page").html(rs[1]);
            $("#point_text2").html(rs[2]);
        }   
    });
}

//출석체크 리스트 호출
var pUseListAjaxCall = function(sPage, page) {
    
    /*if($("#member_seqno2").val() == ""){
        alert("회원명을 입력하세요");
        return false;
    }*/


    var data = {
        "search_dvs"   : $("#search_dvs").val(),
        "keyword"      : $("#keyword").val(),
        "member_seqno" : $("#member_seqno2").val(),
        "date_from"    : $("#acc_date_from3").val(),
        "date_to"      : $("#acc_date_to3").val(),
        "version"      : $("#version option:selected").val(),
    	"showPage"     : sPage,
    	"page"         : page
    };

    var url = "/proc/mkt/point_mng/proc_point_use_list.php";

    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";

    showMask();
    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {

            hideMask();
            var rs = result.split("♪");

            if (rs[0].trim() == "") {
                $("#puse_list").html(blank);
            } else {
                $("#puse_list").html(rs[0]);
            }

            $("#att_page").html(rs[1]);
            $("#point_text3").html(rs[2].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            $("#point_text4").html(Math.abs(rs[3]).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
        }   
    });
}

//회원 검색
var searchAtt = function() {
    AttListAjaxCall(30, 1);
}

//페이지 이동
var movePage = function(val) {
    AttListAjaxCall(showPage, val);
}

//보여줄 페이지 수 설정
var showPageSetting = function(val) {
    showPage = val;
    AttListAjaxCall(val, 1);
}