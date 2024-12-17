/*
 *
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/09/25 이청산 생성
 *=============================================================================
 *
 */
$(document).ready(function() {

    // 데이트피커
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    $("#empl_top_dept").trigger("change");
    
});

/**
 * @brief 직원 검색리스트
 *
 */
var searchEmpl = {
    "data" : null,
    "exec" : function() {
        var data = {
            "search_keyword" : $("#search_keyword").val()
        };
        var url = "/json/business/empl_info/load_empl_info.php";  
        var callback = function(result) {
            $("#list").html(result.list);
            pagingCommon("empl_page",
                         "changePageEmplList",
                         5,
                         result.result_cnt,
                         5,
                         "init");

        };

        this.data = data;
 
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 직원 검색리스트 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageEmplList = function(page) {
    if ($("empl_page_" + page).hasClass("page_accent")) {
        return false;
    }
    /*var seqno = loadMemberStatsInfo.seqno;
    if(checkBlank(seqno)) {
        return false;
    }*/
    if (isNaN(page)) {
        return false;
    }

    $(".empl_page").removeClass("page_accent");
    $("#empl_page_" + page).addClass("page_accent");
    
    var url = "/json/business/empl_info/load_empl_info.php";
    var data = searchEmpl.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        $("#list").html(result.list);
    };
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 직원 등록페이지로 이동
 *
 */
var insertEmpl = function() {
    location.href = "/business/empl_regi.html";
};

/**
 * @brief 직원 정보 등록
 *
 */
var regiEmpl = function() {
    var url = "/proc/business/empl_info/insert_empl_info.php"; 
    var data = {
        "empl_num"       : $("#empl_num").val(),  
        "empl_name"      : $("#empl_name").val(),  
        "empl_duty"      : $("#empl_duty").val(),  
        "empl_posi"      : $("#empl_posi").val(),  
        "empl_top_dept"  : $("#empl_top_dept").val(),  
        "empl_mid_dept"  : $("#empl_mid_dept").val(),  
        "empl_mail"      : $("#empl_mail").val(),  
        "empl_cell_top"  : $("#empl_cell_top").val(),  
        "empl_cell_mid"  : $("#empl_cell_mid").val(),  
        "empl_cell_end"  : $("#empl_cell_end").val(),  
      //  "empl_phone_top" : $("#empl_phone_top").val(),  
      //  "empl_phone_mid" : $("#empl_phone_mid").val(),  
        "empl_phone_end" : $("#empl_phone_end").val(),
        "empl_status"    : $("#empl_status").val(),
        "empl_auth"      : $("#empl_auth").val()
    };

    if (checkBlank(data.empl_num)) {
        alert("사번을 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_name)) {
        alert("직원 이름을 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_posi)) {
        alert("직원 직급을 입력해 주십시오.");
        return false;
    }
 
    if (checkBlank(data.empl_top_dept)) {
        alert("직원 상위부서를 입력해 주십시오.");
        return false;
    }    

    if (checkBlank(data.empl_mid_dept)) {
        alert("직원 하위부서를 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_mail)) {
        alert("직원 이메일을 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_cell_mid)) {
        alert("휴대폰번호를 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_cell_end)) {
        alert("휴대폰번호를 입력해 주십시오.");
        return false;
    }

    /*
    if (checkBlank(data.empl_phone_mid)) {
        alert("전화번호를 입력해 주십시오.");
        return false;
    }
    */

    if (checkBlank(data.empl_phone_end)) {
        alert("내선번호를 입력해 주십시오.");
        return false;
    }

    var callback = function(result) {
        if (checkBlank(result)) {
            alert('yes');
            return alertReturnFalse(result); 
        }

        alert("등록되었습니다.");
                
    };

    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 직원 등록
 *
 */
var goListEmpl = function() {
    var conf = confirm("목록으로 이동하시겠습니까? 저장하지 않은 정보는 사라집니다.");

    if (conf) {
        location.href = "/business/empl_info.html";
    } 
};

/**
 * @brief 상위 부서 선택시 하위 부서 변경
 * 
 * @param deparCode = 부서코드
 */
var changeDept = function(deparCode) {
    if(checkBlank(deparCode)) {
        var html = "<option value=''>전체</option>";
        $("#empl_mid_dept").html(html);
        return false;
    }
    
    var url = "/ajax/business/empl_info/load_empl_info.php";
    var data = {
        "depar_code" : deparCode
    };
    var callback = function(result) {
        $("#empl_mid_dept").html(result);
        midDeptChange();
    };

    ajaxCall(url, "html", data, callback);

};

/**
 * @brief 직원 수정으로 이동
 *
 * @param seqno = 직원 일련번호
 */
var modiEmpl = function(seqno) {
    var tarPage  = "/business/empl_modi.html";
        tarPage += "?seqno=" + seqno;

    location.href = tarPage;   
};

/**
 * @brief 직원 수정 시 부서 변경
 *
 *
 */
var midDeptChange = function() {
    var midDeptVal = $("#empl_mid_val").val();
    
    if (midDeptVal) {
        $("#empl_mid_dept").val(midDeptVal);
    }
};

/**
 * @brief 직원 정보 수정 저장
 *
 */
var saveChanges = function() {
    var url = "/proc/business/empl_info/update_empl_info.php"; 
    var data = {
        "empl_seqno"     : $("#empl_seqno").val(),  
        "empl_duty"      : $("#empl_duty").val(),  
        "empl_posi"      : $("#empl_posi").val(),  
        "empl_top_dept"  : $("#empl_top_dept").val(),  
        "empl_mid_dept"  : $("#empl_mid_dept").val(),  
        "empl_mail"      : $("#empl_mail").val(),  
        "empl_cell_top"  : $("#empl_cell_top").val(),  
        "empl_cell_mid"  : $("#empl_cell_mid").val(),  
        "empl_cell_end"  : $("#empl_cell_end").val(),  
        //"empl_phone_top" : $("#empl_phone_top").val(),  
        //"empl_phone_mid" : $("#empl_phone_mid").val(),  
        "empl_phone_end" : $("#empl_phone_end").val(),
        "empl_status"    : $("#empl_status").val(),
        "empl_auth"      : $("#empl_auth").val()
    };

    if (checkBlank(data.empl_posi)) {
        alert("직원 직급을 입력해 주십시오.");
        return false;
    }
 
    if (checkBlank(data.empl_top_dept)) {
        alert("직원 상위부서를 입력해 주십시오.");
        return false;
    }    

    if (checkBlank(data.empl_mid_dept)) {
        alert("직원 하위부서를 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_mail)) {
        alert("직원 이메일을 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_cell_mid)) {
        alert("휴대폰번호를 입력해 주십시오.");
        return false;
    }

    if (checkBlank(data.empl_cell_end)) {
        alert("휴대폰번호를 입력해 주십시오.");
        return false;
    }

    /*
    if (checkBlank(data.empl_phone_mid)) {
        alert("전화번호를 입력해 주십시오.");
        return false;
    }
    */

    if (checkBlank(data.empl_phone_end)) {
        alert("내선번호를 입력해 주십시오.");
        return false;
    }

    var callback = function(result) {
        if (checkBlank(result)) {
            return alertReturnFalse(result); 
        }

        alert("수정되었습니다.");

    };

    ajaxCall(url, "text", data, callback);
};


