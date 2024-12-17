/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2015/12/20 왕초롱 생성
 *============================================================================
 *
 */

var oevent_seqno = "";
var nowadays_seqno = "";
var overto_seqno = "";

$(document).ready(function() {

    loadOeventList(2);

});

/**********************************************************************
                         * 이벤트 리스트 부분 *
 **********************************************************************/

//오특이 이벤트 리스트
var loadOeventList = function(type) {

    showMask();

    //탭 이동시에 팝업 초기화
    if (type == "1") {

	    resetTabEvent();
    }

    $.ajax({

        type: "POST",
        data: {},
        url: "/ajax/mkt/event_mng/load_oevent_list.php",
        success: function(result) {

            if($.trim(result) == "") {

                $("#oevent_list").html("<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {
	    
	    	    $("#oevent_list").html(result);

	        }
            hideMask();
        }, 
        error: getAjaxError
    });
}

//요즘바빠요 이벤트 리스트
var loadNowadaysList = function(type) {

    showMask();

    //탭 이동시에 팝업 초기화
    if (type == "1") {

	    resetTabEvent();
    }

    $.ajax({

        type: "POST",
        data: {},
        url: "/ajax/mkt/event_mng/load_nowadays_list.php",
        success: function(result) {

            if($.trim(result) == "") {

                $("#nowadays_list").html("<tr><td colspan='5'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {
	    
	    	    $("#nowadays_list").html(result);

	        }
            hideMask();
        }, 
        error: getAjaxError
    });
}

//골라담자 이벤트 리스트
var loadOvertoList = function(type) {

    showMask();

    //탭 이동시에 팝업 초기화
    if (type == "1") {

	    resetTabEvent();
    }

    $.ajax({

        type: "POST",
        data: {},
        url: "/ajax/mkt/event_mng/load_overto_list.php",
        success: function(result) {

            if($.trim(result) == "") {

                $("#overto_list").html("<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>"); 

	        } else {
	    
	    	    $("#overto_list").html(result);

	        }

            hideMask();

            if (type == "2") {
                showBgMask();
            }
        }, 
        error: getAjaxError
    });

}

//탭이동시 초기화
var resetTabEvent = function() {

    hideRegiPopup();

}

/**********************************************************************
                       * 오특이 이벤트 관련 부분  *
 **********************************************************************/

//오특이 이벤트 등록창 팝업
var popOeventLayer = function() {

    $.ajax({

        type: "POST",
        data: {
        },
        url: "/ajax/mkt/event_mng/load_oevent_popup.php",
        success: function(result) {

            openRegiPopup(result, 700);
            activeDate();

        }, 
        error: getAjaxError
    });
}

//오특이 이벤트 수정창 팝업
var popOeventDetailLayer = function(seq) {

    oevent_seqno = seq;
    showMask();

    $.ajax({

        type: "POST",
        data: {
        	"oevent_seqno"  : oevent_seqno
        },
        url: "/ajax/mkt/event_mng/load_oevent_popup_detail.php",
        success: function(result) {

            var tmp = result.split('♪♥♭');

            hideMask();
            openRegiPopup(tmp[0], 700);
            activeDate();

            var event_info = tmp[1].split('♪♡♭');

            $("#start_hour").val(event_info[0]);
            $("#start_min").val(event_info[1]);
            $("#end_hour").val(event_info[2]);
            $("#end_min").val(event_info[3]);
            $("#sell_site").val(event_info[4]);

        }, 
        error: getAjaxError
    });
}

//달력 활성화
var activeDate = function() {

    $('#oevent_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

}

/**
 * @brief 상세검색정보 리셋
 */
var resetDetailInfo = function() {

    // 종이 부분 초기화
    $("#paper_name").html(makeOption("종이명"));
    resetPaperInfo();

    // 사이즈 초기화
    $("#output_size").html(makeOption("사이즈명"));

    // 인쇄 부분 초기화
    $("#print_tmpt").html(makeOption("인쇄도수"));

    $("#amt").html(makeOption("수량"));

};

/**
 * @brief 종이 정보 리셋
 */
var resetPaperInfo = function() {
    var paper = "paper";
    $("#" + paper + "_dvs").html("<option value=\"\">구분</option>");
    $("#" + paper + "_color").html("<option value=\"\">색상</option>");
    $("#" + paper + "_basisweight").html("<option value=\"\">평량</option>");
};

/**
 * @brief 상세검색정보(종이/사이즈/인쇄) 초기화
 *
 * @param cateSortcode = 정보검색용 카테고리 분류코드
 */
var initDetailInfo = function(cateSortcode) {
    if (checkBlank(cateSortcode) === true) {
        resetDetailInfo();
        return false;
    }

    var url = "/ajax/mkt/event_mng/load_detail.php"
    var data = {
        "cate_sortcode" : cateSortcode
    };
    var callback = function(result) {
 
	showBgMask();
        $("#paper_name").html(result.paper);
        $("#paper_dvs").html("<option value=\"\">구분</option>");
        $("#paper_color").html("<option value=\"\">색상</option>");
        $("#paper_basisweight").html("<option value=\"\">평량</option>");
        $("#output_size").html(result.size);
        $("#print_tmpt").html(result.print);
	resetAmtPriceInfo();
    };

    ajaxCall(url, "json", data, callback);
};

var resetAmtPriceInfo = function() {

    //가격 부분 초기화
    $("#basic_price").val("");
    $("#sale_price").val("");
    $("#sum_price").val("");

    $("#amt").html(makeOption("수량"));
    $("#amt_unit").val("");
}

/**
 * @brief 카테고리 선택시 하위 카테고리 정보 검색
 *
 * @param paperType = 종이 선택 구분(NAME, DVS, COLOR)
 * @param val       = 선택한 값
 */
var paperSelect = {
    "type" : null,
    "pop" : function(paperType, val) {
        if (paperType === "name" && checkBlank(val) === true) {
            resetPaperInfo();
            return false;
        }

        this.type = paperType;

        var url = "/json/basic_mng/prdt_price_list/load_paper_info.php";
        var data = {
            "cate_sortcode" : $("#cate_bot").val(),
            "type"          : paperType,
            "paper_name"    : $("#paper_name").val(),
            "paper_dvs"     : $("#paper_dvs").val(),
            "paper_color"   : $("#paper_color").val()
        };
      

        var callback = function(result) {
	    showBgMask();
            if (paperSelect.type === "NAME") {
                $("#paper_dvs").html(result.dvs);
                $("#paper_color").html(result.color);
                $("#paper_basisweight").html(result.basisweight);
            } else if (paperSelect.type === "DVS") {
                $("#paper_color").html(result.color);
                $("#paper_basisweight").html(result.basisweight);
            } else if (paperSelect.type === "COLOR") {
                $("#paper_basisweight").html(result.basisweight);
            }

	    resetAmtPriceInfo();
	};

        ajaxCall(url, "json", data, callback);
    }
};

//수량 가져오기
var loadAmtUnit = function() {

    //인쇄 도수가 비었을때
    if ($("#print_tmpt").val() == "") {

        alert("인쇄 도수를 선택해주세요.");
        return false;
    }

    $.ajax({

        type: "POST",
        data: {
		"cate_sortcode"     : $("#cate_bot").val(),
		"sell_site"         : $("#sell_site").val(),
		"mono_yn"           : "0",
		"paper_name"        : $("#paper_name").val(),
		"paper_dvs"         : $("#paper_dvs").val(),
		"paper_color"       : $("#paper_color").val(),
		"paper_basisweight" : $("#paper_basisweight").val(),
		"print_tmpt"        : $("#print_tmpt").val(),
		"output_size"       : $("#output_size").val(),
		
	},
        url: "/ajax/mkt/event_mng/load_amt_unit.php",
        success: function(result) {

	    resetAmtPriceInfo();

            var tmp = result.split('♪♥♭');

            $("#amt").html(tmp[0]);
            $("#amt_unit").val(tmp[1]);

        }, 
        error: getAjaxError
    });
}

//가격 가져오기
var loadPrice = function() {

    $.ajax({

        type: "POST",
        data: {
		"cate_sortcode"     : $("#cate_bot").val(),
		"sell_site"         : $("#sell_site").val(),
		"mono_yn"           : "0",
		"paper_name"        : $("#paper_name").val(),
		"paper_dvs"         : $("#paper_dvs").val(),
		"paper_color"       : $("#paper_color").val(),
		"paper_basisweight" : $("#paper_basisweight").val(),
		"print_tmpt"        : $("#print_tmpt").val(),
		"output_size"       : $("#output_size").val(),
		"amt"               : $("#amt").val()
	},
        url: "/ajax/mkt/event_mng/load_price.php",
        success: function(result) {

	    $("#sale_price").val("");
	    $("#sum_price").val("");

            var tmp = result.split('♪♥♭');
	    $("#basic_price").val(tmp[0]);

        }, 
        error: getAjaxError
    });
}

//오특이 이벤트 설정 저장
var saveOeventInfo = function(seq) {

    //오특이 이벤트 이름이 비었을때
    if ($("#event_name").val() == "") {

        alert("이벤트 이름을 입력해주세요.");
	$("#event_name").focus();
        return false;
    }

    //이벤트 일자가 비었을때
    if ($("#oevent_date").val() == "") {

        alert("이벤트 일자를 선택해주세요.");
        return false;
    }

    //종료시간이 시작시간보다 앞설때
    if ($("#start_hour").val() + $("#start_min").val() > $("#end_hour").val() + $("#end_min").val()) {

        alert("일자별 시간 설정을 확인해주세요.");
        return false;
    }

    //카테고리 대분류가 비었을때
    if ($("#cate_top").val() == "") {

        alert("카테고리 대분류를 선택해주세요.");
	$("#cate_top").focus();
        return false;
    }

    //카테고리 중분류가 비었을때
    if ($("#cate_mid").val() == "") {

        alert("카테고리 중분류를 선택해주세요.");
        return false;
    }

    //카테고리 소분류가 비었을때
    if ($("#cate_bot").val() == "") {

        alert("카테고리 소분류를 선택해주세요.");
        return false;
    }

    //종이명이 비었을때
    if ($("#paper_name").val() == "") {

        alert("종이명을 선택해주세요.");
        return false;
    }

    //종이 구분이 비었을때
    if ($("#paper_dvs").val() == "") {

        alert("종이 구분을 선택해주세요.");
        return false;
    }

    //종이 색상이 비었을때
    if ($("#paper_color").val() == "") {

        alert("종이 색상을 선택해주세요.");
        return false;
    }

    //종이 평량이 비었을때
    if ($("#paper_basisweight").val() == "") {

        alert("종이 평량을 선택해주세요.");
        return false;
    }

    //사이즈가 비었을때
    if ($("#output_size").val() == "") {

        alert("사이즈를 선택해주세요.");
        return false;
    }

    //인쇄 도수가 비었을때
    if ($("#print_tmpt").val() == "") {

        alert("인쇄 도수를 선택해주세요.");
        return false;

    }

    //수량이 비었을때
    if ($("#amt").val() == "") {

        alert("수량을 선택해주세요.");
        return false;
    }

    //기준 가격이 비었을때
    if ($("#basic_price").val() == "") {

        alert("기준 가격이 비었습니다.");
        return false;
    }

    //할인금액이 비었을때
    if ($("#sale_price").val() == "") {

        alert("할인 금액을 입력해주세요.");
	$("#sale_price").focus();
        return false;
    }

    //계산금액이 비었을때
    if ($("#sum_price").val() == "") {

        alert("계산 금액을 입력해주세요.");
	$("#sum_price").focus();
        return false;
    }

    //파일이 비었을때
    if ($("#upload_file").val() == "" && $("#file_name").val() == "") {

        alert("파일을 업로드해주세요.");
        return false;
    }

    var formData = new FormData($("#oevent_form")[0]);
    formData.append("cate_bot", $("#cate_bot").val());
    formData.append("paper_name", $("#paper_name").val());
    formData.append("paper_dvs", $("#paper_dvs").val());
    formData.append("paper_color", $("#paper_color").val());
    formData.append("paper_basisweight", $("#paper_basisweight").val());
    formData.append("print_tmpt", $("#print_tmpt").val());
    formData.append("stan_mpcode", $("#output_size").val());
    formData.append("basic_price", $("#basic_price").val());
    formData.append("oevent_seqno", seq);
    
    $.ajax({

        type: "POST",
        data: formData,
	processData : false,
	contentType : false,
        url: "/proc/mkt/event_mng/proc_oevent_info.php",
        success: function(result) {

    	    if ($.trim(result) == "1") {
       		alert("저장하였습니다.");
                saveOeventHtml();
                hideRegiPopup();
        	loadOeventList('2');
    	    } else {
        	alert("저장에 실패하였습니다.");
    	    }
        }, 
        error: getAjaxError
    });
}

//오특이 이벤트 삭제
var delOevent = function(seq) {

    $.ajax({

        type: "POST",
        data: {
                "oevent_seqno" : seq
        },
        url: "/proc/mkt/event_mng/del_oevent.php",
        success: function(result) {

	    if ($.trim(result) == "1") {

                alert("삭제되었습니다.");
                hideRegiPopup();
                loadOeventList('2');

	        } else {

                alert("삭제에 실패하었습니다.");
	    
	        }
        }, 
        error: getAjaxError
    });
}

//오특이 이벤트  파일 삭제
var delOeventFile = function(seq) {

    $.ajax({

        type: "POST",
        data: {
		"file_seqno" : seq
        },
        url: "/proc/mkt/event_mng/del_oevent_file.php",
        success: function(result) {

            	if($.trim(result) == "1") {

		    $("#file_area").hide();

	    	    alert("삭제했습니다.");

	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}


//계산 가격 구하기
var loadSumPrice = function(event, val) {

    event = event || window.event;

    var keyID = (event.which) ? event.which : event.keyCode;
    var basic_price = $("#basic_price").val();

    //숫자,방향키,del 외 막기
    if ( (keyID >= 48 && keyID <= 57) || (keyID >= 96 && keyID <= 105) 
            || keyID == 8 || keyID == 46 || keyID == 37|| keyID == 39 
            || keyID == 190) {

	return $("#sum_price").val(basic_price - val);

    } else {

        event.target.value = event.target.value.replace(/[^0-9,.]/g, "");
        return false;

    }
}

//메인페이지 오특이 이벤트 
var saveOeventHtml = function() {
    $.ajax({
        type: "POST",
        data: {},
        url: "/ajax/mkt/event_mng/load_oevent_html.php",
        dataType : 'html',
        success: function(result) {
			
		}, 
        error: getAjaxError
    });
}


/**********************************************************************
                       * 요즘 바빠요 이벤트 관련 부분  *
 **********************************************************************/

//요즘바빠요 이벤트 등록창 팝업
var popNowadaysLayer = function() {

    $.ajax({

        type: "POST",
        data: {
        },
        url: "/ajax/mkt/event_mng/load_nowadays_popup.php",
        success: function(result) {
	
            openRegiPopup(result, 700);
            activeDate();

        }, 
        error: getAjaxError
    });
}

//요즘바빠요 이벤트 수정창 팝업
var popNowadaysDetailLayer = function(seq) {

    nowadays_seqno = seq;
    showMask();

    $.ajax({

        type: "POST",
        data: {
        	"nowadays_seqno"  : nowadays_seqno
        },
        url: "/ajax/mkt/event_mng/load_nowadays_popup_detail.php",
        success: function(result) {
            var tmp = result.split('♪♥♭');

            hideMask();
            openRegiPopup(tmp[0], 700);
            $("#sell_site").val(tmp[1]);
        }, 
        error: getAjaxError
    });
}

//요즘바빠요 이벤트 설정 저장
var saveNowadaysInfo = function(seq) {

    //오특이 이벤트 이름이 비었을때
    if ($("#event_name").val() == "") {

        alert("이벤트 이름을 입력해주세요.");
	$("#event_name").focus();
        return false;
    }

    //카테고리 대분류가 비었을때
    if ($("#cate_top").val() == "") {

        alert("카테고리 대분류를 선택해주세요.");
	$("#cate_top").focus();
        return false;
    }

    //카테고리 중분류가 비었을때
    if ($("#cate_mid").val() == "") {

        alert("카테고리 중분류를 선택해주세요.");
        return false;
    }

    //카테고리 소분류가 비었을때
    if ($("#cate_bot").val() == "") {

        alert("카테고리 소분류를 선택해주세요.");
        return false;
    }

    //종이명이 비었을때
    if ($("#paper_name").val() == "") {

        alert("종이명을 선택해주세요.");
        return false;
    }

    //종이 구분이 비었을때
    if ($("#paper_dvs").val() == "") {

        alert("종이 구분을 선택해주세요.");
        return false;
    }

    //종이 색상이 비었을때
    if ($("#paper_color").val() == "") {

        alert("종이 색상을 선택해주세요.");
        return false;
    }

    //종이 평량이 비었을때
    if ($("#paper_basisweight").val() == "") {

        alert("종이 평량을 선택해주세요.");
        return false;
    }

    //사이즈가 비었을때
    if ($("#output_size").val() == "") {

        alert("사이즈를 선택해주세요.");
        return false;
    }

    //인쇄 도수가 비었을때
    if ($("#print_tmpt").val() == "") {

        alert("인쇄 도수를 선택해주세요.");
        return false;

    }

    //수량이 비었을때
    if ($("#amt").val() == "") {

        alert("수량을 선택해주세요.");
        return false;
    }

    //기준 가격이 비었을때
    if ($("#basic_price").val() == "") {

        alert("기준 가격이 비었습니다.");
        return false;
    }

    //할인금액이 비었을때
    if ($("#sale_price").val() == "") {

        alert("할인 금액을 입력해주세요.");
	$("#sale_price").focus();
        return false;
    }

    //계산금액이 비었을때
    if ($("#sum_price").val() == "") {

        alert("계산 금액을 입력해주세요.");
	$("#sum_price").focus();
        return false;
    }

    //파일이 비었을때
    if ($("#upload_file").val() == "" && $("#file_name").val() == "") {

        alert("파일을 업로드해주세요.");
        return false;
    }

    var formData = new FormData($("#nowadays_form")[0]);
    formData.append("cate_bot", $("#cate_bot").val());
    formData.append("paper_name", $("#paper_name").val());
    formData.append("paper_dvs", $("#paper_dvs").val());
    formData.append("paper_basisweight", $("#paper_basisweight").val());
    formData.append("print_tmpt", $("#print_tmpt").val());
    formData.append("stan_mpcode", $("#output_size").val());
    formData.append("nowadays_seqno", seq);

    $.ajax({

        type: "POST",
        data: formData,
	processData : false,
	contentType : false,
        url: "/proc/mkt/event_mng/proc_nowadays_info.php",
        success: function(result) {

            if($.trim(result) == "1") {

		alert("저장하였습니다.");
		saveNowadaysHtml();
        	hideRegiPopup();
		loadNowadaysList('2');

	    } else {

		alert("저장에 실패하였습니다.");

	    }
        }, 
        error: getAjaxError
    });
}

//요즘바빠요 이벤트 삭제
var delNowadays = function(seq) {

    $.ajax({

        type: "POST",
        data: {
                "nowadays_seqno" : seq
        },
        url: "/proc/mkt/event_mng/del_nowadays_event.php",
        success: function(result) {

            if($.trim(result) == "1") {

                alert("삭제되었습니다.");
                hideRegiPopup();
                loadNowadaysList('2');

	        } else {

                alert("삭제에 실패하었습니다.");
	    
	        }
        }, 
        error: getAjaxError
    });
}

//요즘바빠요 파일 삭제
var delNowadaysFile = function(seq) {

    $.ajax({

        type: "POST",
        data: {
		"file_seqno" : seq
        },
        url: "/proc/mkt/event_mng/del_nowadays_file.php",
        success: function(result) {

            	if($.trim(result) == "1") {

		    $("#file_area").hide();

	    	    alert("삭제했습니다.");

	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

//메인페이지 요즘바빠요 이벤트 
var saveNowadaysHtml = function() {
    $.ajax({
        type: "POST",
        data: {},
        url: "/ajax/mkt/event_mng/load_nowadays_html.php",
        dataType : 'html',
        success: function(result) {
			console.log(result);
		},	 
        error: getAjaxError
    });
}



/**********************************************************************
                       * 골라담자 이벤트 관련 부분  *
 **********************************************************************/

//골라담자 이벤트 등록창 팝업
var popOvertoLayer = function(seq) {

    showMask();
    overto_seqno = seq;

    $.ajax({

        type: "POST",
        data: {
		        "overto_seqno" : seq
        },
        url: "/ajax/mkt/event_mng/load_overto_popup.php",
        success: function(result) {

            var tmp = result.split('♪♥♭');

            hideMask();
            openRegiPopup(tmp[0], 700)

            if (tmp[1]) {

            //판매채널 선택
            $("#sell_site").val(tmp[1]);
            }
        }, 
        error: getAjaxError
    });
}

//골라담자 이벤트 그룹 저장
var saveOvertoGroup = function() {

    //이벤트 이름이 비었을때
    if ($("#event_name").val() == "") {

        alert("이벤트 이름을 입력해주세요.");
	    $("#event_name").focus();
        return false;
    }

    //전체주문 금액이 비었을때
    if ($("#tot_order_price").val() == "") {

        alert("전체주문 금액 조건을 입력해주세요.");
	    $("#tot_order_price").focus();
        return false;
    }

    //할인 요율이 비었을때
    if ($("#sale_rate").val() == "") {

        alert("할인 요율을 입력해주세요.");
	    $("#sale_rate").focus();
        return false;

    }

    //파일이 비었을때
    if ($("#repre_upload_file").val() == "") {
        alert("대표이미지 파일을 업로드해주세요.");
        $("#repre_upload_file").focus();
        return false;
    }

    var formData = new FormData($("#group_form")[0]);
    formData.append("overto_seqno", overto_seqno);
    formData.append("event_name", $("#event_name").val());
    formData.append("use_yn", $("input:radio[name='use_yn']:checked").val());
    formData.append("order_price", $("#tot_order_price").val());
    formData.append("sale_rate", $("#sale_rate").val());
    formData.append("sell_site", $("#sell_site").val());

    $.ajax({

        type: "POST",
        data: formData,
        url: "/proc/mkt/event_mng/proc_overto_group.php",
        processData: false,
        contentType: false,
        success: function(result) {
    		var tmp = result.split("♪♥♭");

           	if($.trim(tmp[0]) == "1") {
			
    			alert("그룹을 저장했습니다.");
    			if (!overto_seqno) {

		    	    $('#del_group_btn').removeAttr("disabled");
	    		    $('#prdt_add_btn').removeAttr("disabled");
    			    overto_seqno = tmp[1];
    			}	

                popOvertoLayer(overto_seqno);
    			loadOvertoList('2');
    		} else {

    			alert("그룹 저장에 실패했습니다.")

    		}
        }, 
        error: getAjaxError
    });

}

//골라담자 이벤트 그룹 삭제
var delOvertoGroup = function() {

    	$.ajax({

        	type: "POST",
        	data: {
			"overto_seqno"  : overto_seqno
        	},
        	url: "/proc/mkt/event_mng/del_overto_group.php",
        	success: function(result) {

            		if($.trim(result) == "1") {
	        		    alert("그룹을 삭제했습니다.")
                        hideRegiPopup(); 
        			    loadOvertoList('1');
                        return false;

                        /*
        			    $('#del_group_btn').attr("disabled","disabled")
        			    $('#prdt_add_btn').attr("disabled","disabled")
        			    resetOvertoGroup();
        			    loadOvertoList('2');
                        */
        			} else {
        			    alert("그룹 삭제에 실패했습니다.")
        			}
    	}, 
        error: getAjaxError
    	});
}

//골라담자 그룹 셋팅 초기화
var resetOvertoGroup = function() {

	//골라담자 그룹 일련번호 초기화
	overto_seqno = "";
	//이벤트 이름 초기화
	$('#event_name').val("");
	//사용여부 초기화
	$("input:radio[name='use_yn']:radio[value='N']").prop("checked",true);
	//전체 주문 금액 초기화
	$('#tot_order_price').val("");
	//할인 요율 초기화
	$('#sale_rate').val("");
	//상품 리스트 초기화
	$('#prdt_list').html("<tr><td colspan='6'>검색된 결과가 없습니다.</td></tr>");
	
	//그룹 상세 셋팅 초기화
	resetOvertoDetail();

}

//골라담자 상세 셋팅 초기화
var resetOvertoDetail = function() {

	//카테고리 초기화
	selectFlattypCate();
	//파일 업로드 초기화
	$('#upload_file').val("");
}

//카테고리 낱장형(합판) 콤보박스 셋팅
var selectFlattypCate = function() {

    	$.ajax({

        	type: "POST",
        	data: {},
        	url: "/ajax/mkt/event_mng/load_flattyp_cate.php",
        	success: function(result) {

			$("#cate_top").html(result);
			cateSelect.exec("top", "");

        	}, 
        error: getAjaxError
    	});

}

//골라담자 이벤트 상세 품목 추가
var saveOvertoPrdt = function(seq) {

    //카테고리 대분류가 비었을때
    if ($("#cate_top").val() == "") {
        alert("카테고리 대분류를 선택해주세요.");
	    $("#cate_top").focus();
        return false;
    }

    //카테고리 중분류가 비었을때
    if ($("#cate_mid").val() == "") {
        alert("카테고리 중분류를 선택해주세요.");
        return false;
    }

    //카테고리 소분류가 비었을때
    if ($("#cate_bot").val() == "") {
        alert("카테고리 소분류를 선택해주세요.");
        return false;
    }

    //종이명이 비었을때
    if ($("#paper_name").val() == "") {
        alert("종이명을 선택해주세요.");
        return false;
    }

    //종이 구분이 비었을때
    if ($("#paper_dvs").val() == "") {
        alert("종이 구분을 선택해주세요.");
        return false;
    }

    //종이 색상이 비었을때
    if ($("#paper_color").val() == "") {
        alert("종이 색상을 선택해주세요.");
        return false;
    }

    //종이 평량이 비었을때
    if ($("#paper_basisweight").val() == "") {
        alert("종이 평량을 선택해주세요.");
        return false;
    }

    //사이즈가 비었을때
    if ($("#output_size").val() == "") {
        alert("사이즈를 선택해주세요.");
        return false;
    }

    //인쇄 도수가 비었을때
    if ($("#print_tmpt").val() == "") {
        alert("인쇄 도수를 선택해주세요.");
        return false;
    }

    var formData = new FormData($("#group_form")[0]);
    formData.append("cate_bot", $("#cate_bot").val());
    formData.append("paper_name", $("#paper_name").val());
    formData.append("paper_dvs", $("#paper_dvs").val());
    formData.append("paper_basisweight", $("#paper_basisweight").val());
    formData.append("print_tmpt", $("#print_tmpt").val());
    formData.append("stan_mpcode", $("#output_size").val());
    formData.append("overto_seqno", overto_seqno);
    formData.append("overto_detail_seqno", seq);

    $.ajax({

        type: "POST",
        data: formData,
        url: "/proc/mkt/event_mng/proc_overto_prdt.php",
	    processData : false,
        contentType : false,
        success: function(result) {
            
            if ($.trim(result) == "1") {
    			alert("상품을 저장했습니다.")
    			loadOvertoPrdt();
    			if (seq) {
    				resetOvertoAddDetail();
    			} else {
    				resetOvertoDetail();
    			}
    		} else {
    			alert("상품 저장에 실패했습니다.")
    		}
    
        }, 
        error: getAjaxError
    	});
}

//골라담자 이벤트 대표이미지 파일 삭제
var delOvertoRepreFile = function(seq) {

    	$.ajax({

        	type: "POST",
        	data: {
			    "overto_repre_file_seqno"  : seq,
        	},
        	url: "/proc/mkt/event_mng/del_overto_repre_file.php",
        	success: function(result) {

			    if ($.trim(result) == "1") {
				    $("#file_area").hide();
    				alert("삭제했습니다.")
    			} else {
    				alert("삭제에 실패했습니다.")
    			}
        	}, 
            error: getAjaxError
    	});
}

//골라담자 이벤트 상품 리스트
var loadOvertoPrdt = function() {

    	$.ajax({

        	type: "POST",
        	data: {
			"overto_seqno"  : overto_seqno
        	},
        	url: "/ajax/mkt/event_mng/load_overto_prdt.php",
        	success: function(result) {

			$("#prdt_list").html(result);
	
        	}, 
        error: getAjaxError
    	});
}

//골라담자 이벤트 상품 삭제
var delOvertoPrdt = function(seq) {

    	$.ajax({

        	type: "POST",
        	data: {
			"overto_detail_seqno"  : seq
        	},
        	url: "/proc/mkt/event_mng/del_overto_prdt.php",
        	success: function(result) {
			if ($.trim(result) == "1") {

				alert("삭제했습니다.")
				loadOvertoPrdt();

			} else {

				alert("삭제에 실패했습니다.")
			}

	
        	}, 
        error: getAjaxError
    	});
}

//골라담자 이벤트 상품 수정
var editOvertoPrdt = function(seq) {

	showMask();

    $.ajax({
        	type: "POST",
        	data: {
			    "overto_detail_seqno"  : seq,
    			"overto_seqno"  : overto_seqno
        	},
        	url: "/ajax/mkt/event_mng/load_overto_detail.php",
        	success: function(result) {
    			$("#detail_area").html(result);
               	hideMask();
	            showBgMask();
        	}, 
        error: getAjaxError
    	});
}

//골라담자 이벤트 파일 삭제
var delOvertoDetailFile = function(seq) {

    	$.ajax({

        	type: "POST",
        	data: {
			"overto_detail_file_seqno"  : seq,
        	},
        	url: "/proc/mkt/event_mng/del_overto_file.php",
        	success: function(result) {

			if ($.trim(result) == "1") {

				$("#file_area").hide();

				alert("삭제했습니다.")

			} else {

				alert("삭제에 실패했습니다.")
			}

        	}, 
        error: getAjaxError
    	});
}

//골라담자 상품 수정 취소 후 재셋팅
var resetOvertoAddDetail = function() {

	resetOvertoDetail();
    $("div[name='file_area']").eq(1).html("");
	$("#edit_cancle_btn").hide();
	$("#prdt_btn_area").html("<button type=\"button\" id=\"prdt_add_btn\" onclick=\"saveOvertoPrdt(''); return false;\" class=\"btn btn-sm btn-success\">상품추가</button>");

}

