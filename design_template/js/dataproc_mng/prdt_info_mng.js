/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/06 왕초롱 생성
 *============================================================================
 *
 */

//상품 정보 불러오기
var loadPrdtInfo = function(val) {

    showMask();

    $.ajax({
            type: "POST",
            data: {
                "cate_sortcode" : val
            },
            url: "/ajax/dataproc_mng/prdt_info_mng/load_prdt_info.php",
            success: function(result) {
	  	$("#prdt_info").html(result);
                hideMask();
           }, 
           error: getAjaxError
    });
}

//카테고리 사진 파일 삭제
var delPhotoFile = function(seq, no) {

    $.ajax({

        type: "POST",
        data: {
		"photo_seqno" : seq
        },
        url: "/proc/dataproc_mng/prdt_info_mng/del_photo_file.php",
        success: function(result) {

            	if($.trim(result) == "1") {

	    	    alert("삭제했습니다.");
		    $("#del_btn" + no).hide();
		    $("#file_name" + no).html('');

	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

//카테고리 배너 파일 삭제
var delBannerFile = function(seq) {

    $.ajax({

        type: "POST",
        data: {
		"banner_seqno" : seq
        },
        url: "/proc/dataproc_mng/prdt_info_mng/del_banner_file.php",
        success: function(result) {
            	if($.trim(result) == "1") {

	    	    alert("삭제했습니다.");
		    $("#banner_file_area").hide();

	        } else {

	    	    alert("삭제에 실패했습니다.");
	       }
        }, 
        error: getAjaxError
    });
}

//카테고리 정보 저장
var savePrdtInfo = function() {

    //카테고리 소분류가 비었을때
    if ($("#cate_bot").val() == "") {

        alert("카테고리 소분류를 선택해주세요.");
        return false;
    }

    var formData = new FormData($("#prdt_form")[0]);
    formData.append("cate_sortcode", $("#cate_bot").val());

    showMask();
    $.ajax({
        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/proc/dataproc_mng/prdt_info_mng/proc_prdt_info.php",
        success: function(result) {
            if($.trim(result) == "1") {
                alert("저장했습니다.");
                loadPrdtInfo($("#cate_bot").val());
	        } else {
	    	    alert("저장에 실패했습니다.");
	       }
           hideMask();
        }, 
        error: getAjaxError
    });
}

//썸네일 메인으로
var changePhoto = function(src) {

	$("#main_photo").attr("src", src);
}
