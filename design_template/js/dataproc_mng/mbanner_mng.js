/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/10/10 harry 생성
 *============================================================================
 *
 */

//게시물수
var objCountView = function(count) {

    var url = "/ajax/dataproc_mng/mbanner_mng/load_banner_contents.php";
    var callback = function(result) {
        $("#banner_contents").html(result);
    };

    ajaxCall(url, "html", {"count" : count}, callback);
}

//팝업 설정 저장
var savePopupSet = function(seq) {

    var seqno = $("#banner_seqno" + seq).val();
    //파일이 비었을때
    if (checkBlank(seqno) && checkBlank($("#upload_file" + seq).val())) {
        alert("파일을 업로드해주세요.");
        return false;
    }

    var formData = new FormData($("#banner_form" + seq)[0]);
    formData.append("seq", seq);
    formData.append("seqno", seqno);

    $.ajax({
        type: "POST",
        data: formData,
        processData : false,
        contentType : false,
        url: "/proc/dataproc_mng/mbanner_mng/regi_banner_contents.php",
        success: function(result) {
            var rs = result.split("♪");
            var img = "<img src=\"" + rs[1] + "\" width=\"500px\" height=\"255px\" >";
            var down = "<a href=\"/common/main_banner_file_down.php?seqno=" + seqno + "\">" + rs[2] + "</a>";
            if ($.trim(rs[0]) == "1") {
                alert("저장했습니다.");
                $("#img_div" + seq).html(img);
                $("#file_name" + seq).html(down);
                $("#banner_seqno" + seq).html(rs[3]);
            } else {
                alert("저장에 실패했습니다.");
            }
        },
        error: getAjaxError
    });
}

//적용
var apply = function() {
 
    var data = {
        "seqno"       : $("#set_seqno").val(),
        "mb_count"    : $("#mb_count").val(),
        "slide_timer" : $(':radio[name="slide_timer"]:checked').val()
    }
    var url = "/proc/dataproc_mng/mbanner_mng/regi_banner_set.php";
    var callback = function(result) {
        var rs = result.split("♪");
	if ($.trim(rs[0]) == "1") {
            alert("저장했습니다.");
	    $("#set_seqno").val(rs[1]);   
	} else {
            alert("저장에 실패했습니다.");
	}
    };

    ajaxCall(url, "html", data, callback);
}

//초기화
var init = function() {

    if (confirm("초기화 하시면 저장된 베너가 전부 삭제 됩니다. \n삭제하시겠습니까?") == false) {
	return false;
    }

    var url = "/proc/dataproc_mng/mbanner_mng/init_banner.php";
    var callback = function(result) {
	if (result == "1") {
	    window.location.reload();
            alert("초기화 되었습니다.");
	}
    };

    ajaxCall(url, "html", {}, callback);
}
