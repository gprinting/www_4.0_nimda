/*
 *
 * Copyright (c) 2016 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2016/01/19 왕초롱 생성
 *============================================================================
 *
 */

var changePasswd = function() {

    //이전 비밀번호가 비었을때
    if ($("#pw").val() == "") {

        alert("이전 비밀번호를 입력해주세요.");
	$("#pw").focus();
        return false;
    }

    //새로운 비밀번호가 비었을때
    if ($("#new_pw").val() == "") {

        alert("비밀번호를 입력해주세요.");
	$("#new_pw").focus();
        return false;
    }

    //비밀번호 확인이 비었을때
    if ($("#new_pw_verify").val() == "") {

        alert("비밀번호확인을 입력해주세요.");
	$("#new_pw_verify").focus();
        return false;
    }


    var formData = $("#passwd_form").serialize();

    $.ajax({
            type: "POST",
            data: formData,
            url: "/proc/dataproc_mng/passwd_change/proc_passwd.php",
            success: function(result) {
                if(result == "1") {

                    alert("변경 했습니다.");
		    document.passwd_form.reset();

                } else if (result == "2") {

                    alert("이전 비밀번호를 확인해주세요.");
		    $("#pw").focus();

		} else if (result == "3") {

                    alert("비밀번호를 확인해주세요.");
		    $("#new_pw").focus();

		} else {

                    alert("변경에 실패했습니다.");

		}

           },
           error: getAjaxError
    });
}
