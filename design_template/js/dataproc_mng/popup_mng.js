/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/12 왕초롱 생성
 *============================================================================
 *
 */

//팝업 리스트
var loadPopupList = function() {

    $.ajax({
            type: "POST",
            data: {},
            url: "/ajax/dataproc_mng/popup_mng/load_popup_list.php",
            success: function(result) {

	    	$("#popup_list").html(result);
           },
           error: getAjaxError
    });

}

//팝업 설정 팝업
var popPopupSet = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"popup_seq" : seq 
	    
	    },
            url: "/ajax/dataproc_mng/popup_mng/load_popup_set_popup.php",
            success: function(result) {

                var tmp = result.split('♪♥♭');
                var popup_info = tmp[1].split('♪♡♭');
        	    openRegiPopup(tmp[0], 700);

                $("#start_hour").val(popup_info[0]);
                $("#start_min").val(popup_info[1]);
                $("#end_hour").val(popup_info[2]);
                $("#end_min").val(popup_info[3]);
		activeDate();

           },
           error: getAjaxError
    });
}

//팝업 미리보기(파일만)
var previewPopup = function(seq) {

    $.ajax({
            type: "POST",
            data: {
	    		"popup_seq" : seq 
	    
	    },
            url: "/ajax/dataproc_mng/popup_mng/load_preview_popup.php",
            success: function(result) {

        	openRegiPopup(result, 480);

           },
           error: getAjaxError
    });
}

//달력 활성화
var activeDate = function() {

    $('#start_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

    $('#end_date').datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true
    });

}

//팝업 설정 저장
var savePopupSet = function(seq) {

    //팝업제목이 비었을때
    if ($("#popup_name").val() == "") {

        alert("팝업제목을 입력해주세요.");
	    $("#popup_name").focus();
        return false;
    }

    //시작일자가 비었을때
    if ($("#start_date").val() == "") {

        alert("시작일자를 입력해주세요.");
        return false;
    }

    //종료일자가 비었을때
    if ($("#end_date").val() == "") {

        alert("종료일자를 입력해주세요.");
        return false;
    }

    //종료일자가 시작일자보다 빠를때
    if ($("#start_date").val() > $("#end_date").val()) {

	    alert("일자 설정을 확인해주세요");
	    return false;

    }

    //종료시간이 시작시간보다 빠를때
    if ($("#start_hour").val() + $("#start_min").val() > $("#end_hour").val() + $("#end_min").val()) {

	    alert("시간 설정을 확인해주세요");
	    return false;

    }

    //가로사이즈가 비었을때
    if ($("#wid_size").val() == "") {

        alert("가로사이즈를 입력해주세요.");
	    $("#wid_size").focus();
        return false;
    }

    //세로사이즈가 비었을때
    if ($("#vert_size").val() == "") {

        alert("세로사이즈를 입력해주세요.");
	    $("#vert_size").focus();
        return false;
    }

    //파일이 비었을때
    if (($("#upload_file").val() == "") && ($("#uploaded_file").html() == "")) {

        alert("파일을 업로드해주세요.");
        return false;
    }

    var formData = new FormData($("#popup_form")[0]);
        formData.append("popup_seq", seq);

    $.ajax({
            type: "POST",
            data: formData,
            processData : false,
            contentType : false,
            url: "/proc/dataproc_mng/popup_mng/proc_popup_set.php",
            success: function(result) {
                if($.trim(result) == "1") {

                    alert("저장했습니다.");
                    loadPopupList();
                    hideRegiPopup();

                } else {

                    alert("저장에 실패했습니다.");
		}

           },
           error: getAjaxError
    });

}

//팝업 파일 삭제
var delPopupFile = function(seq) {

    $.ajax({

        type: "POST",
        data: {
		    "popup_seqno" : seq
        },
        url: "/proc/dataproc_mng/popup_mng/del_popup_file.php",
        success: function(result) {
            if($.trim(result) == "1") {

	    	    alert("삭제했습니다.");
		    $("#uploaded_file").html('');
		    $("#file_btn").hide();

	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

