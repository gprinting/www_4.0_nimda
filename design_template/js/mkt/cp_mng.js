/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2015/12/15 왕초롱 생성
 * 2016/04/14 임종건 팝업, 데이트 피커, 리스트, 소스 수정 및 재개발
 * 2016/05/30 전민재 erd 변경후 validation 수정
 *============================================================================
 *
 */

var cpn_admin_seqno = "";
var cp_seqno = "";
var member_seqno = "";

$(document).ready(function() {
    loadCpStatsList();
});

var change_coupon_kind = function() {
    var kind = $("input:radio[name='coupon_kind']:checked").val();
    if(kind == "input_coupon") {
        $("#lb_coupon_number").show();
    } else {
        $("#lb_coupon_number").hide();
    }
}

//수정시 달력 활성화
var editActiveDate = function() {

    var date = new Date();
    date.setDate(date.getDate());

    //시작날짜
    $('#release_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    })
    //종료날짜
    $('#expired_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });
}

//등록시 달력활성화
var addActiveDate = function() {

    var date = new Date();
    date.setDate(date.getDate());

    //일자별 검색 datepicker 기본 셋팅
    //기간제-시작날짜
    $('#public_period_start_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true,
        startDate: date
    }).on('changeDate', function(ev) {
        $('#public_period_end_date').datepicker({
            autoclose:true,
            format: "yyyy-mm-dd",
            todayBtn: "linked",
            startDate: ev.date
        });
    });   
    
    //소멸날짜
    $('#expire_extinct_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true,
        startDate: date
    });
}

//쿠폰 통계 리스트 불러오기
var loadCpStatsList = function() {

    var url = "/ajax/mkt/cp_mng/load_cp_stats_list.php";
    var blank = "<tr><td colspan='8'>검색된 내용이 없습니다.</td></tr>";
    var data = {
         "cpn_seqno" : $("#stats_sell_site").val()
        ,"year" : $("#year").val()
        ,"mon" : leadingZeros($("#mon").val(), 2)
    }
    var callback = function(result) {
        if ($.trim(result) == "") {
            $("#cp_stats_list").html(blank);
        } else {
            $("#cp_stats_list").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//숫자 앞에 0 붙이는 함수 (쿠폰 통계의 'mon' 값을 넘겨줄때 사용 
var leadingZeros = function(n, digits) {
    var zero = '';
    n = n.toString();

    if (n.length < digits) {
        for (var i = 0; i < digits - n.length; i++)
            zero += '0';
    }
    return zero + n;
}

//쿠폰 리스트 불러오기
var loadCpList = function() {

    var url = "/ajax/mkt/cp_mng/load_cp_list.php";
    var blank = "<tr><td colspan='9'>검색된 내용이 없습니다.</td></tr>";
    var data = {
        "cpn_seqno" : $("#policy_sell_site").val()
    }
    var callback = function(result) {
        if ($.trim(result) == "") {
            $("#cp_list").html(blank);
        } else {
            $("#cp_list").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var clickAllSortcode = function() {
    var checked = $("#cate_sortcode").prop("checked");
    $("input[name='cate_sortcode[]']").each(function(idx, select) {
        $(select).prop("checked", checked);
    });
}

//쿠폰 등록 팝업
var regiCpPopup = function() {
 
    var url = "/ajax/mkt/cp_mng/load_cp_detail.php";
    var callback = function(result) {
        var tmp = result.split('♪♥♭');
	    openRegiPopup(tmp[0], "900");
        $("input:radio[name='sale_dvs']:radio[value='%']").prop("checked",true);
        $("input:radio[name='public_dvs']:radio[value='1']").prop("checked",true);
        $("input:radio[name='expire_dvs']:radio[value='1']").prop("checked",true);
        $("input:radio[name='object_appoint']:radio[value='Y']").prop("checked",true);
        $("input:radio[name='cp_expo_yn']:radio[value='N']").prop("checked",true);
        $("input[name=cp_expo_yn]").attr("disabled",true);
	    addActiveDate();
    };
    var data = {
        "type" : "add"
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

//대상 지정 등록창
var loadObjectAppoint = function(cp_seq, cpn_seq) {

    showBgMask();
    //임시테이블 데이터 삭제
    delAppointTmpTable();

    cpn_admin_seqno = cpn_seq;
    cp_seqno = cp_seq;

    var url = "/ajax/mkt/cp_mng/load_object_detail.php";
    var data = {
        "cp_seqno" : cp_seq,
        "cpn_seqno": cpn_seq
    }
    var callback = function(result) {
        openRegiPopup(result, "1020");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//쿠폰 상세 불러오기
var loadCpDetail = function(seq, objectVal) {

    var url = "/ajax/mkt/cp_mng/load_cp_detail.php";
    var callback = function(result) {

        var tmp = result.split('♪♥♭');
        var cp_info = tmp[1].split('♪♡♭');
	    openRegiPopup(tmp[0], "880");
        //cpn_admin_seqno
        $("#pop_sell_site").val(cp_info[0]);
       
        //선택된 대상상품(checkbox) 
        var sortcode = cp_info[1].split(':!:');
        for(var i in sortcode) {
            $("input:checkbox[name='cate_sortcode[]']:" +
                    "checkbox[value='" + sortcode[i] + "']").prop("checked",true);
        }
       
        $("#start_hour").val(cp_info[2]);
        $("#start_min").val(cp_info[3]);
        $("#end_hour").val(cp_info[4]);
        $("#end_min").val(cp_info[5]);
	    
        editActiveDate();
    };
    var data = {
        "coupon_seqno" : seq,
        "type"     : "edit"
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

var releaseCoupon = function(seq) {
    if (confirm("쿠폰을 발행하시겠습니까?") == false) {
        return false;
    }

    var url = "/ajax/mkt/cp_mng/release_coupon.php";
    var callback = function(result) {
        hideMask();
    };
    var data = {
        "coupon_seqno" : seq
    }

    showMask();
    ajaxCall(url, "html", data, callback);
}

//쿠폰 파일 삭제
var delCpFile = function(seq) {

    var url = "/proc/mkt/cp_mng/del_cp_file.php";
    var data = {
        "file_seqno" : seq
    }
    var callback = function(result) {
        if($.trim(result) == "1") {
	    showBgMask();
            alert("삭제했습니다.");
            $("#file_seqno").val("");
            $("#file_area").hide();
        } else {
            alert("삭제에 실패했습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//쿠폰 정보 수정
var saveCpInfo = function(seq) {
    //쿠폰명이 비었을때
    if ($("input:radio[name='coupon_kind']:checked").val() == null) {
        alert("발행종류를 선택해주세요.");
        return false;
    }

    //쿠폰명이 비었을때
    if ($("#pop_cp_name").val() == "") {
        alert("쿠폰명을 입력해주세요.");
	    $("#pop_cp_name").focus();
        return false;
    }

    if ($("input:radio[name='sale_dvs']:checked").val() == "%") {
    //할인 내용 요율할인 일때
        //할인 요율이 비었을때
        if ($("#per_val").val() == "") {
            alert("할인요율을 입력해주세요.");
            $("#per_val").focus();
            return false;
        }

        //최대 할인금액이 비었을때
        if ($("#max_sale_price").val() == "") {
            alert("최대 할인금액을 입력해주세요.");
            $("#max_sale_price").focus();
            return false;
        }

    } else {
    //할인 내용 금액할인 일때
        //할인 최소 구매 금액이 비었을때
        if ($("#min_order_price").val() == "") {
            alert("최소 구매금액을 입력해주세요.");
            $("#min_order_price").focus();
            return false;
        }

        //할인 금액이 비었을때
        if ($("#won_val").val() == "") {
            alert("할인금액을 입력해주세요.");
            $("#won_val").focus();
            return false;
        }

        //최소금액은 할인금액보다 무조건 커야한다
        if (parseInt($("#min_order_price").val()) < parseInt($("#won_val").val())) {
            alert("할인금액이 구매금액을 초과할 수 없습니다.");
            $("#min_order_price").focus();
            return false;
        }
    }


    //발행일 
    var start_date = $("#release_date").val();
    var end_date = $("#expired_date").val();

    //시작일자가 비었을때
    if (start_date == "") {
        alert("시작일자를 입력해주세요.");
        return false;
    }

    //종료일자가 비었을때
    if (end_date == "") {
        alert("종료일자을 입력해주세요.");
        return false;
    }

    //시작일자 및 종료일자 설정이 잘못됬을때
    if (start_date > end_date) {
        alert("기간제 설정을 확인해주세요");
        return false;
    }

    //대상상품선택시 1개 이상 선택해야함
    if ($("input:checkbox[name='cate_sortcode[]']:checked").length == 0) {
        alert("대상상품을 적어도 1개이상 선택해주세요.");
        return false;
    }
       
    var formData = new FormData($("#cp_form")[0]);
        formData.append("coupon_seqno", seq);

    $.ajax({
        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/proc/mkt/cp_mng/proc_cp_info.php",
        success: function(result) {
           if($.trim(result) == "1") {
	    	    alert("저장했습니다.");
                hideRegiPopup();
			    loadCpList();
	        } else {
	    	    alert("실패했습니다.");
	       }
	},
        error: getAjaxError
    });
}

//팝업창 검색 버튼 클릭 검색시
var clickSearchName = function(event, search_str, dvs) {

    loadMemberName(event, $("#search_pop").val(), "click");

}

//회원명(사내닉네임) 가져오기
var loadMemberName = function(event, search_str, dvs) {

    if (dvs != "click") {
        if (event.keyCode != 13) {
            return false;
        }
    }

    var url = "/ajax/mkt/cp_mng/load_member_name.php";
    var data = {
        "search_str"     : search_str,
	"cpn_admin_seqno": cpn_admin_seqno
    }
    var callback = function(result) {
        showBgMask();
        if (dvs == "") {
            showPopMask();
            searchPopPopShow(event, 'loadMemberName', 'clickSearchName');
        }
        $("#search_list").html(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//검색창 팝업 show
var searchPopPopShow = function(event, fn1, fn2) {

    var html = "";
    html += "\n  <dl>";
    html += "\n    <dt class=\"tit\">";
    html += "\n      <h4>검색창 팝업</h4>";
    html += "\n    </dt>";
    html += "\n    <dt class=\"cls\">";
    html += "\n      <button type=\"button\" onclick=\"hidePopPopup();\" class=\"btn btn-sm btn-danger fa fa-times\">";
    html += "\n      </button>";
    html += "\n    </dt>";
    html += "\n  </dl>";
    html += "\n  <div class=\"pop-base\">";
    html += "\n    <div class=\"pop-content\">";
    html += "\n      <label for=\"search_pop\" class=\"con_label\">";
    html += "\n        Search : ";
    html += "\n        <input id=\"search_pop\" type=\"text\" class=\"search_btn fix_width180\" onkeydown=\"" + fn1 + "(event, this.value, 'select');\">";
    html += "\n        <button type=\"button\" class=\"btn btn-sm btn-info fa fa-search\" onclick=\"" + fn2 + "(event, this.value, 'select');\">";
    html += "\n        </button>";
    html += "\n      </label>";
    html += "\n      <hr class=\"hr_bd3\">";
    html += "\n      <div class=\"list_scroll fix_height120\" id=\"search_list\">";
                     //<ul style="ofh">
                     //  <li onclick=\"selectResult('%s');\" style=\"cursor: pointer;\"></li>
                     //</ul>
    html += "\n      </div>";
    html += "\n    </div>";
    html += "\n  </div>";
    html += "\n</div>";

    openPopPopup(html, 440);
    $("#search_pop").focus();
}



//팝업 검색된 사내닉네임 클릭시
var nickClick = function(val, name) {

    member_seqno = val;
    hidePopPopup();
    $("#office_nick").val(name);

}

//발행 매수 show/hide
var showAmtArea = function(val) {

    if(val == "Y") {
        $("#amt_area").hide();
        $("input[name=cp_expo_yn]").attr("disabled",true);
    } else {
        $("#amt_area").show();
        $("input[name=cp_expo_yn]").attr("disabled",false);
    }
}

//회원 정보 list
var loadMemberInfoList = function() {

    //사내 닉네임이 비었을때
    if ($("#office_nick").val() == "") {
        member_seqno = "";
    }
 
    var url = "/ajax/mkt/cp_mng/load_member_info_list.php";
    var blank = "<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>";
    var data = {
        "cpn_admin_seqno" : cpn_admin_seqno,
        "member_seqno"    : member_seqno,
        "depar_dvs"       : $("#depar_dvs").val(),
        "member_typ"      : $("#member_typ").val(),
        "grade"           : $("#grade_dvs").val()
    }
    var callback = function(result) {
        showBgMask();
        if (result.trim() == "") {
            $("#member_list").html(blank); 
        } else {
            $("#member_list").html(result);
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//전체 선택
var allCheck = function(id, tbody_id) {

    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#" + id).prop("checked")) {
        $("#" + tbody_id + " input[type=checkbox]").prop("checked", true);
    } else {
        $("#" + tbody_id + " input[type=checkbox]").prop("checked", false);
    }
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function(el) {

    var selectedValue = ""; 
    
    $("#" + el + "_list input[name=" + el + "_chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

//회원 지정 임시 저장
var addMemberAppoint = function() {

    var val = "";
    val = getselectedNo("member");

    if (val == "") {
        alert("추가 하실 회원을 선택해주세요.");
        return false;
    }

    var member_arr = val.split(",");
    var url = "/proc/mkt/cp_mng/insert_appoint_member.php";
    var data = {
        "member_arr"   : member_arr,
        "cp_seqno"     : cp_seqno
    }
    var callback = function(result) {
        showBgMask();
        var tmp = result.split('♪♥♭');
	if (tmp[0] == 0) {
            $("input[type=checkbox][name=member_chk]").prop("checked", false);
            alert("추가하였습니다.");
        } else {
            alert("추가에 실패하였습니다.");
        }
        $("#appoint_list").html(tmp[1]);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//지정 회원 임시 삭제
var delAppointMember = function() {

    var del_val = "";
    del_val = getselectedNo("appoint");

    if (del_val == "") {
        alert("삭제 하실 회원을 선택해주세요.");
        return false;
    }

    var member_arr = del_val.split(",")
    var blank = "<tr><td colspan='3'>검색된 내용이 없습니다.</td></tr>";
    var url = "/proc/mkt/cp_mng/del_appoint_member.php";
    var data = {
        "member_arr"   : member_arr,
        "cp_seqno"     : cp_seqno
    }
    var callback = function(result) {
        showBgMask();
        var tmp = result.split('♪♥♭');
        if (tmp[0] == 0) {
            alert("삭제하였습니다.");
        } else {
            alert("삭제에 실패하였습니다.");
        }
        if (tmp[1].trim() == "") {
            $("#appoint_list").html(blank);
        } else {
            $("#appoint_list").html(tmp[1]);
        }
        $("input[name=all_appoint_chk]").prop("checked",false);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//쿠폰 지정 회원 발급 추가
var addCpIssue = function() {

    var url = "/proc/mkt/cp_mng/insert_cp_issue.php";
    var data = {
        "cp_seqno"     : cp_seqno
    }
    var callback = function(result) {
        showBgMask();
        var tmp = result.split('♪♥♭');
        if (tmp[0].trim() == 0) {
            alert("쿠폰을 발급했습니다.");
            delAppointTmpTable();
        } else if (tmp[0].trim() == 2){
            alert("이미 쿠폰을 발급했거나 발급할 회원이 없습니다.");
        } else {
            alert("쿠폰을 발급에 실패했습니다.");
        }	
        
        if (tmp[1].trim() == "") {
            $("#appoint_list").html("<tr><td colspan='3'>검색된 결과가 없습니다.</td></tr>");
        } else {
            $("#appoint_list").html(tmp[1]);
        }
        
        $("input[name=all_appoint_chk]").prop("checked",false);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//쿠폰 지정 회원 임시테이블 데이터 삭제
var delAppointTmpTable = function() {

    var url = "/proc/mkt/cp_mng/del_appoint_tmp_table.php";
    var callback = function(result) {};
    showMask();
    ajaxCall(url, "html", {}, callback);
}

