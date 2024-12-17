/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/01/08 임종건 생성
 * 2016/05/01 박상용 팝업, 업로더 수정
 *=============================================================================
 *
 */

$(document).ready(function() {
    dateSet('0');
     // 데이트피커
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');
    
    loadDeparInfo();
    cndSearch.exec();
    //cndSearch.exec(30, 1);
});

/**
* @brief 보여줄 페이지 수 설정
*/
var showPageSetting = function(val) {
    showPage = val;
    cndSearch.exec(val, 1);
}

/**
* @brief 페이지 이동
*/
var movePage = function(val) {
    cndSearch.exec(showPage, val);
}

//보여줄 페이지 수
var showPage = "";

/**
 * @brief 선택조건으로 검색 클릭시
 *
var cndSearch = {
    "exec"       : function(listSize, page, pop="") {
        
        var url = "/ajax/business/oto_inq_mng/load_oto_inq_list.php";
        var blank = "<tr><td colspan=\"9\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
    	    "search_cnd"  : $("#search_cnd").val(),
       	    "date_from"   : $("#date_from").val(),
    	    "date_to"     : $("#date_to").val(),
    	    "time_from"   : $("#time_from").val(),
    	    "time_to"     : $("#time_to").val()
	    };
        var callback = function(result) {
            var rs = result.split("♪");

            if (pop) {
    
                if (rs[0].trim() == "") {
                    $("#list", opener.document).html(blank);
                    return false;
                }
                $("#list", opener.document).html(rs[0]);
                $("#page", opener.document).html(rs[1]);
                
                window.close();

            } else {
    
                if (rs[0].trim() == "") {
                    $("#list").html(blank);
                    return false;
                }
                $("#list").html(rs[0]);
                $("#page").html(rs[1]);

            }
        };

        if (checkBlank($("#office_nick").val())) {
            $("#member_seqno").val("");
        }

        if (checkBlank($("#member_seqno").val()) && !checkBlank($("#office_nick").val())) {
            alert("검색창 팝업을 이용하시고 검색해주세요.");
            $("#office_nick").focus();
            return false;
        }

        data.sell_site     = $("#sell_site").val();
        data.depar_code    = $("#depar_code").val();
        data.member_seqno  = $("#member_seqno").val();
        data.answ_yn       = $("#status").val();
        data.claim_dvs     = $("#dvs").val();
        data.listSize      = listSize;
        data.page          = page;

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};*/

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "data"       : null,
    "exec"       : function(pop) {
        var url = "/json/business/oto_inq_mng/load_oto_inq_list.php";
        var data = {
    	    "date_sel"    : $("#date_sel").val(),
       	    "basic_from"  : $("#basic_from").val(),
    	    "basic_to"    : $("#basic_to").val()
	    };
        var callback = function(result) {

            console.log(result);

            if (pop) { 

                $("#total").html(result.total);
                $("#list").html(result.list);
                pagingCommonUpgv("oto_inq_page",
                             "changePageOtoInqList",
                             5,
                             result.result_cnt,
                             5,
                             "init");
                $("#page").html(result.paging);
                hideLoadingMask();

                window.close();

            } else {


                $("#total").html(result.total);
                $("#list").html(result.list);
                pagingCommonUpgv("oto_inq_page",
                             "changePageOtoInqList",
                             5,
                             result.result_cnt,
                             5,
                             "init");
                $("#page").html(result.paging);
                hideLoadingMask();

            }

        };

        if (checkBlank($("#office_nick").val())) {
            $("#member_seqno").val("");
        }

        data.sell_site     = $("#sell_site").val();
        data.depar         = $("#depar").val();
        data.member_seqno  = $("#member_seqno").val();
        data.answ_yn       = $("#status").val();

        this.data = data;

        showLoadingMask();
        ajaxCall(url, "json", data, callback);
    } 
};

/**
 * @brief 회원 검색리스트 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageOtoInqList = function(page) {
    if ($("#oto_inq_page_" + page).hasClass("page_accent")) {
        return false;
    }
    
    if (isNaN(page)) {
        return false;
    }

    $(".oto_inq_page").removeClass("page_accent");
    $("#oto_inq_page_" + page).addClass("page_accent");
    
    var url = "/json/business/oto_inq_mng/load_oto_inq_list.php";
    var data = cndSearch.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask();
        $("#list").html(result.list);
    };
    
    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};


/**
* @brief 검색
*/
var searchInquire = function() {
    cndSearch.exec();
}

/**
* @brief 문의관리
*/
var getInq = {
    "exec"       : function(seqno) {
        $("#seqno").val(seqno);
        var f = document.frm;
        window.open("", "POP");
        f.action = "/business/pop_oto_inq_info.html";
        f.target = "POP";
        f.method = "POST";
        f.submit();

        return false;
/*    
        var url = "/ajax/business/oto_inq_mng/load_oto_inq_info.php";
        var data = {
	        "seqno" : seqno
        };
        var callback = function(result) {
            hideMask();
            var rs = result.split("♪");
            $("#oto_cont").html(rs[0]);
            if (rs[1] === "Y") {
                $("#reply_btn").hide();
                $("#reply_cont").attr("disabled", true);
                $("#file_search").attr("disabled", true);
                $("#upload_file").attr("disabled", true);
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
*/
    }
}

/**
* @brief 답변 등록
*/
var regiReply = {
     "exec"       : function(seqno) {
        var formData = new FormData();
        var url = "/proc/business/oto_inq_mng/regi_oto_inq_info.php";
        formData.append("seqno", seqno);
        formData.append("reply_cont", $("#reply_cont").val());
        formData.append("upload_file", $("#upload_file")[0].files[0]);
        var data = formData;

        showLoadingMask();
         
        $.ajax({
        type: "POST",
        data: data,
        url: url,
        dataType : "html",
        processData : false,
        contentType : false,
        success: function(result) {
            hideLoadingMask();
            if (result == 1) {
                cndSearch.exec("pop");
                alert("답변을 등록 하였습니다.");
            } else {
                alert("답변등록을 실패하였습니다.");
            }
        },
        error    : getAjaxError   
        });
    }
};

/**
 * @brief 답변 등록
 */
var cancelRegiReply = {
    "exec"       : function(seqno) {
        var formData = new FormData();
        var url = "/proc/business/oto_inq_mng/cancel_regi_oto_inq_info.php";
        formData.append("seqno", seqno);
        var data = formData;

        showLoadingMask();

        $.ajax({
            type: "POST",
            data: data,
            url: url,
            dataType : "html",
            processData : false,
            contentType : false,
            success: function(result) {
                hideLoadingMask();
                if (result == 1) {
                    cndSearch.exec("pop");
                    alert("답변을 등록 삭제하였습니다.");
                } else {
                    alert("답변등록을 실패하였습니다.");
                }
            },
            error    : getAjaxError
        });
    }
};

//파일찾기 
var fileSearchBtn = function(val) {
    return $("#upload_path").val(val);
}

//관리자가올린 문의답변 파일 다운로드
var adminFtfFileDown = function(seq) {
    var url = "/common/admin_ftf_file_down.php?seqno=" + seq;
    location.href = url;
}

//첨부파일 삭제
var delAdminFtfFile = function(seq) {
    alert("이미 답변된 파일은 지울 수 없습니다.");
    return false;
    var url = "/proc/business/oto_inq_mng/del_oto_inq_file.php";
    var data = {"seqno":seq};
    var callback = function(result) {
        hideMask();
        if (result == 1) {
            //getEsti.exec(seqno);
            location.reload();
            alert("첨부파일을 삭제하였습니다.");
        } else {
            alert("첨부파일 삭제에 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}


