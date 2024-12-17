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

    loadGradePointStats();

});

//등급 정책 저장
var saveGradePolicy = function() {

    var formData = $("#grade_form").serialize();

    $.ajax({

        type: "POST",
        data: formData,
        url: "/proc/mkt/grade_mng/proc_grade_policy.php",
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

//등급 정책 불러오기
var loadGradePolicy = function() {

    showMask();

    $.ajax({

        type: "POST",
        data: {},
        url: "/ajax/mkt/grade_mng/load_grade_policy.php",
        success: function(result) {
		var tmp = result.split('♥♪@');
        
        $("input:checkbox[id='mon1']").prop("checked", tmp[0]);
        $("input:checkbox[id='mon2']").prop("checked", tmp[1]);
        $("input:checkbox[id='mon3']").prop("checked", tmp[2]);
        $("input:checkbox[id='mon4']").prop("checked", tmp[3]);
        $("input:checkbox[id='mon5']").prop("checked", tmp[4]);
        $("input:checkbox[id='mon6']").prop("checked", tmp[5]);
        $("input:checkbox[id='mon7']").prop("checked", tmp[6]);
        $("input:checkbox[id='mon8']").prop("checked", tmp[7]);
        $("input:checkbox[id='mon9']").prop("checked", tmp[8]);
        $("input:checkbox[id='mon10']").prop("checked", tmp[9]);
        $("input:checkbox[id='mon11']").prop("checked", tmp[10]);
        $("input:checkbox[id='mon12']").prop("checked", tmp[11]);

        $("#set_day").val(tmp[12]);
        $("#grade_list").html(tmp[13]);
	hideMask();

        }, 
        error: getAjaxError
    });

}

//등급별 포인트 통계
var loadGradePointStats = function() {

    showMask();

    $.ajax({

        type: "POST",
        data: {
                "sell_site"  : $("#sell_site").val(),
                "year"       : $("#year").val(),
                "mon"        : $("#mon").val()
        },
        url: "/ajax/mkt/grade_mng/load_point_stats.php",
        success: function(result) {
            if(result.trim() == "") {

                $("#gradePointList").html("<tr><td colspan='4'>검색된 내용이 없습니다.</td></tr>"); 

            } else {

                $("#gradePointList").html(result);

            }
	        hideMask();
        }, 
        error: getAjaxError
    });

}
