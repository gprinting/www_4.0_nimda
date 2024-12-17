/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/01/06 임종건 생성
 * 2016/04/27 전민재 수정 (파일업로더 추가)
 * 2017/08/09 이청산 수정 (함수 추가)
 * 2017/08/21 이청산 수정 (기능 추가)
 *=============================================================================
 *
 */
// 작업 파일 업로더 객체
var uploaderObj = "";
// 작업 파일 업로더 확인여부
var fileFlag = 0;

$(document).ready(function() {
    dateSet('0');
    // 팀 별 검색에서 팀구분 값 로드
    loadDeparInfo();
    loadRespDvs();
    loadApproval();
    
    if ($("#agree_yn").val() == "Y") {
        $("#agree_btn").hide();   
        $("#extnl_etprs").attr("disabled", true);   
        $("#accident_cause").attr("readonly", true);   
        $("#accident_type").attr("readonly", true);   
        $("#occur_price").attr("readonly", true);   
        $("#outsource_burden_percent").attr("disabled", true);   
        $("#outsource_burden_price").attr("readonly", true);   
        $("#member_dvs").attr("disabled", true);   
        $("#cust_burden_price").attr("readonly", true);   
        $("#claim_status").attr("disabled", true);   
        $("#deal_date").attr("disabled", true);   
    }
    if ($("#order_yn").val() == "Y") {
        $("#order_btn").hide();   
        $("#count").attr("readonly", true);   
        $("#work_file").attr("disabled", true);   
        $("#work_file_upload").attr("disabled", true);   
        $("#file_uplode_btn").hide();
    } else {
        fileUpload();
    }

    // 데이트피커
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');
    
});

//보여줄 페이지 수
var showPage = "";

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "data"       : null,
    "pop"        : function(listSize, page, pop) {
        
        var url = "/ajax/business/claim_list/load_claim_list.php";
        var data = {
       	    "date_from"      : $("#date_from").val(),       // 날짜-시작 
    	    "date_to"        : $("#date_to").val(),         // 날짜-끝
    	    "time_from"      : $("#time_from").val(),       // 시간-시작
    	    "time_to"        : $("#time_to").val(),         // 시간-끝

    	    "depar"          : $("#depar").val(),           // 팀
    	    "empl"           : $("#empl").val(),            // 담당자

            "member_typ"     : $("#member_typ").val(),      // 회원등급
            "member_grade"   : $("#member_grade").val(),    // 회원등급-단계

    	    "search_dvs"     : $("#search_dvs").val(),      // 키워드검색 조건
    	    "search_keyword" : $("#search_keyword").val(),  // 키워드검색 키워드
	    };
        var callback = function(result) {
            console.log(result);
            var rs = result.split("♪");
                if (rs[0].trim() == "") {
                    $("#list", opener.document).html(blank);
                    return false;
                }
                $("#list", opener.document).html(rs[0]);
                $("#page", opener.document).html(rs[1]);
                $("#thead_count", opener.document).html(rs[2]);
                window.close();
        };

        data.sell_site     = $("#sell_site").val();
        data.claim_dvs     = $("#dvs", opener.document).val();
        data.listSize      = listSize;
        data.page          = page;

        this.data = data;
        ajaxCall(url, "html", data, callback);
    },
    "exec"       : function(listSize, page) {
        var url = "/json/business/claim_list/load_claim_list.php";
        var data = {
       	    "basic_from"      : $("#basic_from").val(),       // 날짜-시작 
    	    "basic_to"        : $("#basic_to").val(),         // 날짜-끝

    	    "depar"          : $("#depar").val(),           // 팀
    	    "empl"           : $("#empl").val(),            // 담당자

            "member_typ"     : $("#member_typ").val(),      // 회원등급
            "member_grade"   : $("#member_grade").val(),    // 회원등급-단계

    	    "search_dvs"     : $("#search_dvs").val(),      // 키워드검색 조건
    	    "search_keyword" : $("#search_keyword").val(),  // 키워드검색 키워드
	    };
        var callback = function(result) {
            hideLoadingMask();
            $("#list").html(result.list);
            $("#total").html(result.total);
            pagingCommonUpgv("claim_list_page",
                         "changePageClaimList",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };

        data.sell_site     = $("#sell_site").val();
        data.claim_dvs     = $("#dvs").val();
        data.listSize      = listSize;
        data.page          = page;

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
var changePageClaimList = function(page) {
    if ($("#claim_list_page_" + page).hasClass("page_accent")) {
        return false;
    }
    if (isNaN(page)) {
        return false;
    }

    $(".claim_list_page").removeClass("page_accent");
    $("#claim_list_page_" + page).addClass("page_accent");
    
    var url = "/json/business/claim_list/load_claim_list.php";
    var data = cndSearch.data;
    data.page = page;
    data.page_dvs = '1';
    var callback = function(result) {
        hideLoadingMask();
        $("#list").html(result.list);
        //선택 영역 기억
        //$("#" + saveSelectedBarArea.str_stats + "").addClass("active_tr");
    };
    
    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 검색
 */
var searchClaim = function() {
    cndSearch.exec(5, 1);
}

//클레임관리 열린 팝업창 닫기
var closePop = function(seqno) {
    var url = "/proc/business/claim_list/modi_claim_order_state.php";
    var data = {
    	"seqno" : seqno
    };
    var callback = function(result) {
        var page = $("#claim_page").val();
        cndSearch.pop(5, page, "pop");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
* @brief 클레임관리
*/
var getClaim = {
    "exec"       : function(seqno, page) {
        console.log("page : " + page);
        $("#seqno").val(seqno);	
        $("#claim_page").val(page);	
        var f = document.frm;
        window.open("", "POP")
        f.action = "/business/pop_claim_info.html";
        f.target = "POP";
        f.method = "POST";
        f.submit();
        return false; 
    }
}

//클레임 처리
var procOrderClaim = {
     "save"       : function(seqno) {
        var url = "/proc/business/claim_list/regi_claim_save_info.php";
        var data = {"seqno":seqno};
        var callback = function(result) {
            hideMask();
            if (result == 1) {
                alert("클레임 처리정보를 저장하였습니다.");
            } else {
                alert("클레임 처리정보를 저장실패하였습니다.");
            }
        };

        data.dvs = $("#claim_dvs").val();
        data.dvs_detail = $("#dvs_detail").val();
        data.mng_cont = $("#mng_cont").val();
        showMask();
        ajaxCall(url, "html", data, callback);
    },
     "modi"        : function() {
        $("#agree_btn").show();
        $("#extnl_etprs").attr("disabled", false);   
        $("#accident_cause").attr("disabled", false);   
        $("#accident_type").attr("disabled", false);   
        $("#outsource_burden_percent").attr("disabled", false);   
        $("#occur_price").attr("readonly", false);   
        $("#outsource_burden_price").attr("readonly", false);   
        $("#cust_burden_price").attr("readonly", false);   
        $("#claim_status").attr("disabled", false);   
        $("#deal_date").attr("disabled", false);   

    },
     "agree"       : function(seqno) {
        var url = "/proc/business/claim_list/regi_claim_agree_info.php";
        var data = {"seqno":seqno};
        var callback = function(result) {
            hideMask();
            if (result == 1) {
                $("#agree_btn").hide();   
                $("#extnl_etprs").attr("disabled", true);   
                $("#accident_cause").attr("disabled", true);   
                $("#accident_type").attr("disabled", true);   
                $("#occur_price").attr("readonly", true);   
                $("#outsource_pur_prdt").attr("disabled", true);   
                $("#outsource_pur_manu").attr("disabled", true);   
                $("#outsource_pur_brand").attr("disabled", true);   
                $("#outsource_memo").attr("disabled", true);   
                $("#occur_price").attr("readonly", true);   
                $("#outsource_burden_price").attr("readonly", true);   
                $("#member_dvs").attr("disabled", true);   
                $("#cust_burden_percent").attr("disabled", true);   
                $("#cust_burden_price").attr("readonly", true);   
                $("#claim_status").attr("disabled", true);   
                $("#deal_date").attr("disabled", true);   
                alert("저장하였습니다.");
            } else {
                alert("저장을 실패하였습니다.");
            }
        };
        if (checkBlank($("#accident_cause").val())) {
            alert("사고 원인이 빈값입니다.");
            $("#refund_prepay").focus();
            return false;
        }
        if (checkBlank($("#accident_type").val())) {
            alert("사고 판독 유형이 빈값입니다.");
            $("#refund_money").focus();
            return false;
        }
        if (checkBlank($("#cust_burden_price").val())) {
            alert("고객부담금이 빈값입니다.");
            $("#cust_burden_price").focus();
            return false;
        }
        if (checkBlank($("#outsource_burden_price").val())) {
            alert("책임비용업체 금액이 빈값입니다.");
            $("#outsource_burden_price").focus();
            return false;
        }

        data.extnl_etprs            = $("#extnl_etprs").val();
        data.accident_cause         = $("#accident_cause").val();
        data.accident_type          = $("#accident_type").val();
        data.occur_price            = $("#occur_price").val();
        data.resp_dvs               = $("#outsource_pur_prdt").val() 
                                    + "!" 
                                    + $("#outsource_pur_manu").val()
                                    + "!" 
                                    + $("#outsource_pur_brand").val()
                                    + "!" 
                                    + $("#outsource_memo").val(); 
        data.outsource_burden_price = $("#outsource_burden_price").val();
        data.cust_burden_price      = $("#cust_burden_price").val();
        data.claim_status           = $("#claim_status").val();
        data.deal_date              = $("#deal_date").val();

        showMask();
        ajaxCall(url, "html", data, callback);
    },
     "order"       : function(seqno) {
        
        var url = "/proc/business/claim_list/regi_claim_order_info.php";
        var count = $("#count").val();
        var order_file_seqno = $("#work_file_seqno").val();
        var is_today = $("#today_pan").is(":checked");
        if (is_today == true) {
            is_today = 1;               // 당일판임
        } else if (is_today == false) {
            is_today = 0;               // 당일판이 아님
        }
        /**
         * fileFlag = 0 , 업로드를 전혀 하지 않은 상황 or 업로드된 파일이 삭제된 
         *                상황으로 견적문의 클릭시 OK
         * fileFlag = 1 , 업로드 진행중 상황으로 견적문의 클릭시 NO
         * fileFlag = 2 , 업로드 완료 후 상황으로 견적문의 클릭시 OK 
         */
        if (fileFlag == 1) {
            alert("파일 업로드 진행 중입니다.");
            return;
        }
        
        if (checkBlank(order_file_seqno)) {
            alert("첨부된 파일이 없습니다.");
            return false;
        }
        
        if (checkBlank(count)) {
            alert("건수가 빈값입니다.");
            $("#count").focus();
            return false;
        }
        
        var formData = new FormData();
        formData.append("seqno", seqno);
        formData.append("count", count);
        formData.append("order_file_seqno", order_file_seqno);
        formData.append("is_today", is_today);

        showMask();
        $.ajax({
            type: "POST",
            data: formData,
            url: url,
            dataType : "html",
            processData : false,
            contentType : false,
            success: function(result) {
                hideMask();
                if (result == 1) {
                    $("#order_btn").hide();   
                    $("#count").attr("readonly", true);   
                    alert("주문을 하였습니다.");
                } else {
                    alert("주문을 실패하였습니다.");
                }
            },
            error    : getAjaxError   
        });
    }
}

/**
 * @brief 파일업로드
 */
var fileUpload = function() {
    var runtimes = "html5,flash,silverlight,html4";
    var mimeTypes = [
        {title : "Zip files", extensions: "zip"} 
    ];

    var btnId    = "work_file";
    var listId   = "work_file_list";
    var uploadId = "work_file_upload";
    var delId    = "work_file_del";

    var uploader = new plupload.Uploader({
        url                 : "/proc/business/claim_list/upload_file.php",
        runtimes            : runtimes,
        browse_button       : btnId, // you can pass in id...
        flash_swf_url       : "/design_template/js/uploader/Moxie.swf",
        silverlight_xap_url : "/design_template/js/uploader/Moxie.xap",
        multi_selection     : false,

        filters : {
                max_file_size : "4096mb",
                mime_types    : mimeTypes 
        },
        init : {
            PostInit : function() {
                document.getElementById(listId).innerHTML = '';
            },
            FilesAdded : function(up, files) {
                // 파일을 새로 추가할 경우
                if (up.files.length > 1) {
                    var fileSeqno = $("#" + delId).attr("file_seqno");

                    // 파일이 업로드 된 상태(fileSeqno !== empty)에서
                    // 다른 파일을 새로 업로드 할 경우
                    if (checkBlank(fileSeqno) === false &&
                            confirm("기존 파일은 삭제합니다." + 
                                    "\n계속 하시겠습니까?") === false) {
                        return false;
                    }
                    up.removeFile(up.files[0]);
                    
                    if (checkBlank(fileSeqno) === false) {
                        removeFile(fileSeqno, false);
                    }
                }

                plupload.each(files, function(file) {
                    document.getElementById(listId).innerHTML =
		                    "<div id=\"" + file.id + "\">" +
		                    file.name + " (" +
		                    plupload.formatSize(file.size) +
		                    ")<b></b>" +
                            "&nbsp;" +
                            "<img src=\"/design_template/images/btn_circle_x_red.png\"" +
                            "     id=\"work_file_del\"" +
                            "     file_seqno=\"\"" +
                            "     alt=\"X\"" +
                            "     onclick=\"removeFile('', true);\"" +
                            "     style=\"cursor:pointer;\" /></div>";
                });
            },
            FilesRemoved : function(up, files) {
                document.getElementById(listId).innerHTML = '';
                fileFlag = 0;
                $("#work_file_seqno").val('');
            },
            UploadProgress : function(up, file) {
                fileFlag = 1;
                fileId = file.id;

                document.getElementById(file.id)
                        .getElementsByTagName("b")[0]
                        .innerHTML = "<span>" + file.percent + "%</span>";
            },
            FileUploaded : function(up, file, response) {
                var jsonObj = JSON.parse(response.response);
                var fileSeqno = jsonObj.file_seqno;
                
                $("#" + delId).attr(
                    {"onclick"    : "removeFile('" + fileSeqno + "', true);",
                     "file_seqno" : fileSeqno}
                );

                fileFlag = 2;

                $("#work_file_seqno").val(fileSeqno);
            },
            Error : function(up, err) {
                document.getElementById(listId).innerHTML +=
                        "\nError #" + err.code + ": " + err.message;
            }
        }
    });

    uploader.init();
    uploaderObj = uploader;
}

/**
 * @brief 작업파일 부분 삭제
 *
 * @param seqno = 주문 파일 일련번호
 * @param flag  = uploader.removeFile 여부
 */
var removeFile = function(seqno, flag) {

    if (checkBlank(seqno) === true) {
        var uploader = uploaderObj;
        var files = uploader.files;
        uploader.removeFile(files[0]);

        return false;
    }

    if (flag === true) {
        if (confirm("작업파일을 삭제하시겠습니까?" +
                    "\n삭제된 파일은 복구되지 않습니다.") === false) {
            return false;
        }
    }

    var url = "/proc/business/claim_list/del_claim_file.php";
    var data = {
        "order_file_seqno"  : seqno
    };
    var callback = function(result) {

        if (result == "F") {
            alert("파일 정보 삭제에 실패했습니다.");
            return false;
        }

        if (flag === true) {
            var uploader = uploaderObj;
            var files = uploader.files;
            uploader.removeFile(files[0]);
        }
    };

    showMask();
    ajaxCall(url, "text", data, callback);
};

/**
 * @brief 작업파일 업로드
 */
var uploadFile = function() {
    var uploader = uploaderObj;
    var url = "/proc/business/claim_list/upload_file.php";
    uploader.settings.url = url;
    uploader.start();
};

/**
 * @brief 원단위 절사(반올림) 함수
 *
 */
var roundBpPrice = function(num, digits) {
    digits = Math.pow(10, digits);
    return Math.round(num / digits) * digits;
};

/**
 * @brief 협의금액 퍼센트 비율에 따라 계산
 * @param total = 총발생비용
 *       ,perc  = 비율
 *       ,tid   = 적용할 id값
 */
var calBpPrice = function(total, perc, tid) {
    var res = "";

    // 계산을 위해 콤마 제거
    var totRmComma = total.replace(/,/g, "");

    // String->Int
    var totalPrice = parseInt(totRmComma);
    var percent    = parseInt(perc);

    // 백분율 계산
    res = ((totalPrice / 100) * percent);

    res = Math.round(res);

    // 1의자리에서 반올림(원단위 절사) - 10의자리에서 하려면 2로 올려야함
    res = roundBpPrice(res, 1);

    var result = $('#' + tid);

    result.val(res);
};

/**
 * @brief 협의금액 퍼센트 설정에 따른 값 변경
 *
 */
var bpStatusChange = {
    "data" : null,
    "exec" : function(dvs) {
        var tot       = $("#occur_price").val();
        var pointer   = "";
        var outsource = $("#outsource_burden_price");
        var cust      = $("#cust_burden_price");
        var com       = $("#com_burden_price");
        var data      = "";
   
        var tar = ""; 
        pointer = $('#' + dvs).val();

        var dvs_s = dvs.split("_");
        var dvs_t = dvs_s[0];

        var priceCalc = function(dvs_t) {

            if (pointer == "직접입력") {
                $("#" + dvs_t + '_burden_price').prop("disabled", false);
                $("#" + dvs_t + '_burden_price').val("");
            } else {
                $("#" + dvs_t + '_burden_price').prop("disabled", true);
                tar  = dvs_t + "_burden_price";
                data = calBpPrice(tot, pointer, tar);
            }

        }
        if (dvs_t == "outsource") {
            priceCalc("outsource");
        } else if (dvs_t == "com") {
            priceCalc("com");
        } else if (dvs_t == "cust") {
            priceCalc("cust");
        }
        this.data = data;
    }
};

/**
 * @brief 클레임 상태 변경시 하단 클레임 처리상태 변경
 */
var changeClaimStatus = function() {
    
    var ori = $("#claim_status").val();
    $("#claim_status_info").val(ori).attr("selected", "selected");
};

/**
 * @brief 클레임 문자 고객에게 전송
 */
var sendClaimMms = function() {
    var checkSendMms = confirm("문자 메시지를 전송 하시겠습니까?");
    //예 누른 경우
    if (checkSendMms) {
        var url  = "/proc/business/claim_list/insert_claim_mms.php";
        var data = {
           "cell_num"  : $("#cell_num").val(),
           "msg"       : $("#mng_cont").val()
        };
        
        var callback = function(result) {
            if (!checkBlank(result)) {
                return alertReturnFalse(result);
            }
            alert('문자가 발송되었습니다.');
        };

        ajaxCall(url, "text", data, callback);
    //아니오 누른 경우
    } else {
        return false;
    }
};

/**
 * @brief 클레임 승인 체크박스 컨트롤(전결처리)
 * @comment html부분 주석해제해야 사용가능
 * @param dvs = 체크된 아이디값
 *
 */
var claimApprovalCheck = { 
    "data" : null,
    "exec" : function(dvs) {
        if (checkBlank(dvs)) {
            return false;
        }

        console.log(dvs);
        // 담당자일 때
        if (dvs == "el") {
            if ($('input:checkbox[id="el"]').is(":checked") == true) {
            } else {
                $('input:checkbox[id="el"]').prop("checked", false);
                $('input:checkbox[id="tl"]').prop("checked", false);
                $('input:checkbox[id="dl"]').prop("checked", false);
                $('input:checkbox[id="hl"]').prop("checked", false);
            }
        // 팀장일 때
        } else if (dvs == "tl") {
            if ($('input:checkbox[id="tl"]').is(":checked") == true) {
                $('input:checkbox[id="el"]').prop("checked", true);
            } else {
                $('input:checkbox[id="el"]').prop("checked", false);
                $('input:checkbox[id="tl"]').prop("checked", false);
                $('input:checkbox[id="dl"]').prop("checked", false);
                $('input:checkbox[id="hl"]').prop("checked", false);
            }
        // 본부장일 때
        } else if (dvs == "dl") {
            if ($('input:checkbox[id="dl"]').is(":checked") == true) {
                $('input:checkbox[id="el"]').prop("checked", true);
                $('input:checkbox[id="tl"]').prop("checked", true);
            } else {
                $('input:checkbox[id="el"]').prop("checked", false);
                $('input:checkbox[id="tl"]').prop("checked", false);
                $('input:checkbox[id="dl"]').prop("checked", false);
                $('input:checkbox[id="hl"]').prop("checked", false);
            }
        // 이사일 때
        } else if (dvs == "hl") {
            if ($('input:checkbox[id="hl"]').is(":checked") == true) {
                $('input:checkbox[id="el"]').prop("checked", true);
                $('input:checkbox[id="tl"]').prop("checked", true);
                $('input:checkbox[id="dl"]').prop("checked", true);
            } else {
                $('input:checkbox[id="el"]').prop("checked", false);
                $('input:checkbox[id="tl"]').prop("checked", false);
                $('input:checkbox[id="dl"]').prop("checked", false);
                $('input:checkbox[id="hl"]').prop("checked", false);
            } 
        // 예외처리
        } else {
            return false;
        } 

    }
};

/**
 * @brief 승인 내용 저장 
 *
 */ 
var saveApproval = function(seqno) {
    var el = $("#el").is(":checked")?"1":"0";
    var tl = $("#tl").is(":checked")?"1":"0";
    var dl = $("#dl").is(":checked")?"1":"0";
    var hl = $("#hl").is(":checked")?"1":"0";
    
    code = el + tl + dl + hl;
    var data = {
        "seqno" : seqno,
        "code"  : code
    }
    var url = "/proc/business/claim_list/save_approval.php";
    var callback = function(result) {
        alert(result);
    }

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 승인 내용 로드
 *
 */
var loadApproval = function() {
    var aprvlCode = $("#aprvl_code").val();
    if (checkBlank(aprvlCode)) {
        return false;
    }

    var el_dat = aprvlCode.substr(0, 1);
    var tl_dat = aprvlCode.substr(1, 1);
    var dl_dat = aprvlCode.substr(2, 1);
    var hl_dat = aprvlCode.substr(3, 1);

    // 체크박스 컨트롤
    var applyApproval = function(id, dvs) {
        if (dvs == 1) {
            $('input:checkbox[id=' + id + ']').prop("checked", true);
            //$('input:checkbox[id=' + id + ']').attr("disabled", true);
        } else if (dvs == 0) {
            $('input:checkbox[id=' + id + ']').prop("checked", false);
            //$('input:checkbox[id=' + id + ']').attr("disabled", false);
        }
    }
    applyApproval("el", el_dat);
    applyApproval("tl", tl_dat);
    applyApproval("dl", dl_dat);
    applyApproval("hl", hl_dat);
};

// 책임업체 리스트 불러오기
var loadExtnlEtprs = function(val, el) {

    $.ajax({
        type: "POST",
        data: {"val" : val},
        url: "/ajax/basic_mng/pur_etprs_list/load_extnl_etprs.php",
        success: function(result) {
            $("#" + el).html(result);

        },
        error: getAjaxError
    });
};

// 책임업체 브랜드 리스트 가져오기
var loadExtnlBrand = function(id, val) {
    var brand_id = "";
    var tid = id.split("_");
    tid = tid[0];
    
    if (tid == "com") {
        $.ajax({
            type: "POST",
            data: {
                    "etprs_seqno" : val
            },
            url: "/ajax/basic_mng/pur_etprs_list/load_extnl_brand.php",
            success: function(result) {
                $("#com_pur_brand").html(result);
            }, 
            error: getAjaxError
        });
    } else if (tid == "outsource") {
        $.ajax({
            type: "POST",
            data: {
                    "etprs_seqno" : val
            },
            url: "/ajax/basic_mng/pur_etprs_list/load_extnl_brand.php",
            success: function(result) {
                $("#outsource_pur_brand").html(result);
            }, 
            error: getAjaxError
        });
    }
};

/**
 * @brief 책임업체 정보 불러오기
 *
 */
var loadRespDvs = function() {
    var purPrdt  = $("#pur_prdt_hid").val();
    var purManu  = $("#pur_manu_hid").val();
    var purBrand = $("#pur_brand_hid").val();
    
    if (checkBlank(purPrdt)) {
        return false;
    }

    var url = "/json/business/claim_mng/load_resp_info.php";
    var data = {
        "pur_prdt"  : purPrdt,
        "pur_manu"  : purManu,
        "pur_brand" : purBrand
    };
    var callback = function(result) {
        $("#outsource_pur_prdt").val(result.pur_prdt);
        $("#outsource_pur_manu").html(result.pur_manu);
        $("#outsource_pur_brand").html(result.pur_brand);


    };

    ajaxCall(url, "json", data, callback);

};
