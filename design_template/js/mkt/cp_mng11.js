$(document).ready(function() {

    loadCpList();

});

var cpn_admin_seqno = "";
var cp_seqno = "";

//달력 활성화
var activeDate = function() {

    //일자별 검색 datepicker 기본 셋팅
    $('#public_date_from').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });   

    $('#public_date_to').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

    $('#expire_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

}

//쿠폰 리스트 불러오기
var loadCpList = function() {

    $.ajax({

        type: "POST",
        data: {
		"cpn_seqno" : $("#policy_sell_site").val()
        },
        url: "/ajax/mkt/cp_mng/load_cp_list.php",
        success: function(result) {

            if($.trim(result) == "") {

                $("#cp_list").html("<tr><td colspan='9'>검색된 내용이 없습니다.</td></tr>"); 

	    } else {
	    
	    	$("#cp_list").html(result);

	    }

        }, 
        error: getAjaxError
    });
}

//쿠폰 등록창 
var popCpLayer = function(event) {

    $.ajax({

        type: "POST",
        data: {
		"type" : "add"
        },
        url: "/ajax/mkt/cp_mng/load_cp_detail.php",
        success: function(result) {

            var tmp = result.split('♪♥♭');
            var cp_info = tmp[1].split('♪♡♭');

            $("#cp_popup_layer").html(tmp[0]);

	        activeDate();
	        layerPopup(event, "cp_popup_layer");

	        $("input:radio[name='sale_dvs']:radio[value='%']").attr("checked",true);
	        $("input:radio[name='period_limit']:radio[value='Y']").attr("checked",true);
	        $("input:radio[name='hour_limit']:radio[value='Y']").attr("checked",true);
	        $("input:radio[name='object_appoint']:radio[value='Y']").attr("checked",true);
	        $("input:radio[name='use_yn']:radio[value='N']").attr("checked",true);
	        $("input:radio[name='cate_sortcode']:radio[value='001001']").attr("checked",true);

        }, 
        error: getAjaxError
    });
}

//대상 지정 등록창
var loadObjectAppoint = function(event, cp_seq, cpn_seq) {

    delAppointTmpTable();

    cpn_admin_seqno = cpn_seq;
    cp_seqno = cp_seq;

    $.ajax({

        type: "POST",
        data: {
		"cp_seqno" : cp_seq,
		"cpn_seqno": cpn_seq
        },
        url: "/ajax/mkt/cp_mng/load_object_detail.php",
        success: function(result) {

            $("#object_popup_layer").html(result);
	    layerPopup(event, "object_popup_layer");

        }, 
        error: getAjaxError
    });
}

//쿠폰 상세 불러오기
var loadCpDetail = function(event, seq) {

    showMask();

    $.ajax({

        type: "POST",
        data: {
		"cp_seqno" : seq,
		"type"     : "edit"
        },
        url: "/ajax/mkt/cp_mng/load_cp_detail.php",
        success: function(result) {

            var tmp = result.split('♪♥♭');
            var cp_info = tmp[1].split('♪♡♭');

            hideMask();
	    $("#cp_popup_layer").html(tmp[0]);

	    activeDate();
	    layerPopup(event, "cp_popup_layer");
	    
            $("#pop_sell_site").val(cp_info[0]);
	        $("input:radio[name='cate_sortcode']:radio[value='" + cp_info[1] + "']").attr("checked",true);
            $("#start_hour").val(cp_info[2]);
            $("#start_min").val(cp_info[3]);
            $("#end_hour").val(cp_info[4]);
            $("#end_min").val(cp_info[5]);

        }, 
        error: getAjaxError
    });
}

//레이어 팝업
var layerPopup = function(event, pop_id) { 

    var layer = "";

    layer = document.getElementById(pop_id);
    var ua = window.navigator.userAgent;

    //마우스로 선택한곳의 x축(화면에서 좌측으로부터의 거리)를 얻는다.
    var _x = null;

    if (ua.indexOf("Chrome") === -1) {
        _x = event.clientX + document.documentElement.scrollLeft;
    } else {
        _x = event.clientX + document.body.scrollLeft;
    }
    //마우스로 선택한곳의 y축(화면에서 상단으로부터의 거리)를 얻는다.
    var _y = event.clientY + document.documentElement.scrollTop;

    if (ua.indexOf("Chrome") === -1) {
        _y = event.clientY + document.documentElement.scrollTop;
    } else {
        _y = event.clientY + document.body.scrollTop;
    }

    //마우스로 선택한 위치의 값이 -값이면 0으로 초기화. (화면은 0,0으로 시작한다.)
    if(_x < 0) _x = 0;
    //마우스로 선택한 위치의 값이 -값이면 0으로 초기화. (화면은 0,0으로 시작한다.)
    if(_y < 0) _y = 0;

    //레이어팝업의 좌측으로부터의 거리값을 마우스로 클릭한곳의 위치값으로 변경.
    layer.style.left = _x-120+"px";
    //레이어팝업의 상단으로부터의 거리값을 마우스로 클릭한곳의 위치값으로 변경.
    layer.style.top = _y+180+"px";

    $('#' + pop_id).css("display", "block");

};

//레이어 닫기
var closeLayer = function(layer) {

    $(layer).css("display", "none");
}

//쿠폰 파일 삭제
var delCpFile = function(seq) {

    $.ajax({

        type: "POST",
        data: {
		"file_seqno" : seq
        },
        url: "/proc/mkt/cp_mng/del_cp_file.php",
        success: function(result) {

            	if($.trim(result) == "1") {

	    	    alert("삭제했습니다.");
		    $("#file_area").hide();

	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

//쿠폰 정보 수정
var saveCpInfo = function(seq) {

    //쿠폰명이 비었을때
    if ($("#pop_cp_name").val() == "") {

        alert("쿠폰명을 입력해주세요.");
	$("#pop_cp_name").focus();
        return false;
    }

    //기간제일때
    if ($("input:radio[name='period_limit']:checked").val() == "Y") {

    	if ($("#public_date_from").val() > $("#public_date_to").val()) {

		alert("기간제 설정을 확인해주세요");
		return false;

	    }
    }

    //시간제 설정일때
    if ($("input:radio[name='hour_limit']:checked").val() == "Y") {

    	if ($("#start_hour").val() + $("#start_min").val() > $("#end_hour").val() + $("#end_min").val()) {

		alert("시간제 설정을 확인해주세요");
		return false;

	    }
    }

    var formData = new FormData($("#cp_form")[0]);
        formData.append("cp_seqno", seq);

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
    			closeLayer("#cp_popup_layer");

	        } else {

	    	    alert("실패했습니다.");
	       }
	},
        error: getAjaxError
    });

}

//회원명(사내닉네임) 가져오기
var loadMemberName = function(event, search_str, dvs) {
    
    if (event.keyCode != 13) {
        return false;
    }

    showMask();
 
    $.ajax({
            type: "POST",
            data: {
                "search_str"     : search_str,
		"cpn_admin_seqno": cpn_admin_seqno
            },
            url: "/ajax/mkt/cp_mng/load_member_name.php",
            success: function(result) {
		if (dvs != "select") {

                    searchPopShow(event, 'loadMemberName', 'loadMemberName');

                } else {

                    showBgMask();

                }
                hideMask();
                $("#search_list").html(result);
           }   
    });
}

//팝업 검색된 사내닉네임 클릭시
var nickClick = function(val) {

    hideRegiPopup();
    $("#office_nick").val(val);

}


//발행 매수 show/hide
var showAmtArea = function(val) {

    if(val == "Y") {

	    $("#amt_area").hide();

    } else {

	    $("#amt_area").show();

    }
}

//회원 정보 list
var loadMemberInfoList = function() {
 
    $.ajax({
            type: "POST",
            data: {
		"cpn_admin_seqno" : cpn_admin_seqno,
                "office_nick"     : $("#office_nick").val(),
                "depar_dvs"       : $("#depar_dvs").val(),
                "member_typ"      : $("#member_typ").val(),
                "grade"           : $("#grade_dvs").val()
            },
            url: "/ajax/mkt/cp_mng/load_member_info_list.php",
            success: function(result) {
                $("#member_list").html(result);
           }   
    });
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
    
    $.ajax({
            type: "POST",
            data: {
                "member_arr"   : member_arr,
		"cp_seqno"     : cp_seqno
            },
            url: "/proc/mkt/cp_mng/insert_appoint_member.php",
            success: function(result) {
            var tmp = result.split('♪♥♭');
	    	if (tmp[0] == 0) {

		    alert("추가하였습니다.");

		} else {

		    alert("추가에 실패하였습니다.");

		}
                $("#appoint_list").html(tmp[1]);
           }   

    });
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

    $.ajax({
            type: "POST",
            data: {
		"member_arr"      : member_arr,
		"cp_seqno"        : cp_seqno
            },
            url: "/proc/mkt/cp_mng/del_appoint_member.php",
            success: function(result) {

            var tmp = result.split('♪♥♭');
	    	if (tmp[0] == 0) {

		    alert("삭제하였습니다.");

		} else {

		    alert("삭제에 실패하였습니다.");

		}
                $("#appoint_list").html(tmp[1]);
           }   
    });
}

//쿠폰 지정 회원 발급 추가
var addCpIssue = function() {

    $.ajax({
            type: "POST",
            data: {
		"cp_seqno"      : cp_seqno
	    },
            url: "/proc/mkt/cp_mng/insert_cp_issue.php",
            success: function(result) {
            	var tmp = result.split('♪♥♭');
	    	if (tmp[0] == 0) {

		    alert("쿠폰을 발급했습니다.");
		    delAppointTmpTable();

		} else {

		    alert("쿠폰을 발급에 실패했습니다.");

		}	
		$("#appoint_list").html(tmp[1]);
           }   
    });
}

//쿠폰 지정 회원 임시테이블 데이터 삭제
var delAppointTmpTable = function() {

    $.ajax({
            type: "POST",
            data: {
            },
            url: "/proc/mkt/cp_mng/del_appoint_tmp_table.php",
            success: function(result) {

           }   
    });
}
