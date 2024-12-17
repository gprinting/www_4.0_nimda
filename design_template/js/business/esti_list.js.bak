/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/29 임종건 생성
 * 2016/04/25 전민재 수정 (파일업로더 추가)
 * 2016/05/19 임종건 수정 (파일업로더 수정)
 *=============================================================================
 *
 */

// 작업 파일 업로더 객체
var uploaderObj = "";

$(document).ready(function() {
    dateSet('0');
    // 팀 별 검색에서 팀구분 값 로드
    loadDeparInfo();
    cndSearch.exec(30, 1);
    
    $("#expec_order_date").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        todayBtn: "linked",
        todayHighlight: true,
    });

    fileUpload();
});

//보여줄 페이지 수
var showPage = "";

var paperIdx = 1;
var outputIdx = 1;
var printIdx = 1;
var afterIdx = 1;
var etcIdx = 1;

//전체 선택
var allCheck = function() {
    //만약 전체선택 체크박스가 체크 된 상태일 경우
    if ($("#allCheck").prop("checked")) {
        $("#list input[type=checkbox]").prop("checked", true);
    } else {
        $("#list input[type=checkbox]").prop("checked", false);
    }
}

//체크박스 선택시 value값 가져오는 함수
var getselectedNo = function() {

    var selectedValue = ""; 
    
    $("#list input[name=chk]:checked").each(function() {
        selectedValue += ","+ $(this).val();		    
    });

    if (selectedValue != "") {
        selectedValue = selectedValue.substring(1);
    }

    return selectedValue;
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "exec"       : function(listSize, page, pop="") {
        
        var url = "/ajax/business/esti_list/load_esti_list.php";
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
        data.status        = $("#status").val();
        data.listSize      = listSize;
        data.page          = page;

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

/**
* @brief 검색
*/
var searchEsti = function() {
    cndSearch.exec(30, 1);
}

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

/**
* @brief 견적삭제
*/
var quiescenceProc = function() {
 
    var seqno = getselectedNo();
    var url = "/proc/business/esti_list/del_esti_list.php";
    var data = {
    	"seqno" : seqno
    };
    var callback = function(result) {
        if (result == 1) {
            cndSearch.exec(30, 1);
            $(".check_box").prop("checked", false);
            alert("선택하신 견적을 삭제하였습니다.");
        } else {
            alert("견적삭제를 실패하였습니다.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/**
* @brief 견적관리
*/
var getEsti = {
    "exec"       : function(seqno) {
 
	$("#seqno").val(seqno);	
        var f = document.frm;
	window.open("", "POP");
	f.action = "/business/pop_esti_info.html";
	f.target = "POP";
	f.method = "POST";
	f.submit();
	return false; 
    }
}

/**
* @brief 견적등록 정보 로우 추가
*/
var addInfoGroup = {
    "exec"       : function(el, dvs, idx) {
        
        var url = "/ajax/business/esti_list/load_add_info.php";
	var newIdx = Number(idx) + 1;
        var data = {
            "dvs" : dvs,
            "idx" : newIdx
        };

	if (dvs === "paper") {
            paperIdx = newIdx;
	} else if (dvs === "output") {
            outputIdx = newIdx;
	} else if (dvs === "print") {
            printIdx = newIdx;
	} else if (dvs === "after") {
            afterIdx = newIdx;
	} else if (dvs === "etc") {
            etcIdx = newIdx;
	}

        var callback = function(result) {

            hideMask();
	    $("." + dvs + "_plus").hide();
	    $("." + dvs + "_minus").show();
	    $("." + dvs + "_plus" + newIdx).show();
	    $("." + dvs + "_minus" + newIdx).hide();
            $("#" + el).append(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

/**
* @brief 견적등록 정보 로우 삭제
*/
var delInfoGroup = function(el) {
    $("#" + el).remove();
}


var closePop = function() {
    cndSearch.exec(30, 1, "pop");
}

/**
* @brief 견적등록 종이 옵션 세팅
*/
var setPaperOption = function(result, dvs, idx) {

    var rs = result.split("♪");
    if (dvs == "NAME") {
        $("#paper_name" + idx).html(rs[0]);
        $("#paper_dvs" + idx).html(rs[1]);
        $("#ppaper_minusaper_color" + idx).html(rs[2]);
        $("#paper_basisweight" + idx).html(rs[3]);
    } else if (dvs == "DVS") {
        $("#paper_dvs" + idx).html(rs[0]);
        $("#paper_color" + idx).html(rs[1]);
        $("#paper_basisweight" + idx).html(rs[2]);
    } else if (dvs == "COLOR") {
        $("#paper_color" + idx).html(rs[0]);
        $("#paper_basisweight" + idx).html(rs[1]);
    } else if (dvs == "BASISWEIGHT") {
        $("#paper_basisweight" + idx).html(rs[0]);
    }
}

/**
* @brief 견적등록 출력 옵션 세팅
*/
var setOutputOption = function(result, dvs, idx) {

    var rs = result.split("♪");
    if (dvs == "BOARD") {
        $("#output_board" + idx).html(rs[0]);
        $("#output_size" + idx).html(rs[1]);
    } else if (dvs == "SIZE") {
        $("#output_size" + idx).html(rs[0]);
    }
}

/**
* @brief 견적등록 인쇄 옵션 세팅
*/
var setPrintOption = function(result, dvs, idx) {

    var rs = result.split("♪");
    if (dvs == "NAME") {
        $("#print_name" + idx).html(rs[0]);
        $("#print_purp" + idx).html(rs[1]);
    } else if (dvs == "PURP") {
        $("#print_purp" + idx).html(rs[0]);
    }
}

/**
* @brief 견적등록 옵션 html 세팅
*/
var setOption = function(cont) {

    return "<option value=\"\">" + cont + "</option>";
}

/**
* @brief 견적등록 후공정 옵션 세팅
*/
var setAfterOption = function(result, dvs, idx) {
	 
    if (checkBlank($("#after_name" + idx).val())) {
        $("#after_depth_one" + idx).html(setOption("Depth1"));
        $("#after_depth_two" + idx).html(setOption("Depth2"));
        $("#after_depth_thr" + idx).html(setOption("Depth3"));
        return false;
    }

    var rs = result.split("♪");
    if (dvs == "DEPTH1") {
        $("#after_depth_one" + idx).html(rs[0]);
    } else if (dvs == "DEPTH2") {
        $("#after_depth_two" + idx).html(rs[0]);
    } else if (dvs == "DEPTH3") {
        $("#after_depth_thr" + idx).html(rs[0]);
    }
}

/**
* @brief 견적등록 옵션 세팅
*/
var selectOptionVal = {
    "exec"       : function(sort, dvs, idx) {
        
        var url = "/ajax/business/esti_list/load_" + sort + "_info.php";
        var data = {
            "dvs" : dvs
        };

        var callback = function(result) {
            hideMask();
            if (sort === "paper") {
                setPaperOption(result, dvs, idx);
            } else if (sort === "output") {
                setOutputOption(result, dvs, idx);
            } else if (sort === "print") {
                setPrintOption(result, dvs, idx);
            } else if (sort === "after") {
                setAfterOption(result, dvs, idx);
            }
        };

        if (sort === "paper") {
            data.paper_name = $("#paper_name" + idx).val();
            data.paper_dvs = $("#paper_dvs" + idx).val();
            data.paper_color = $("#paper_color" + idx).val();
        } else if (sort === "output") {
            data.output_name = $("#output_name" + idx).val();
            data.output_board = $("#output_board" + idx).val();
        } else if (sort === "print") {
            data.cate_sortcode = $("#cate_sortcode" + idx).val();
            data.print_name = $("#print_name" + idx).val();
        } else if (sort === "after") {
            data.after_name = $("#after_name" + idx).val();
            data.after_depth1 = $("#after_depth_one" + idx).val();
            data.after_depth2 = $("#after_depth_two" + idx).val();
	}

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "price" :  function(sort, idx) {
        var url = "/ajax/business/esti_list/load_" + sort + "_price_info.php";
        var data = {};

        var callback = function(result) {
            hideMask();
	    if (!checkBlank(result)) {
		result = Number(result) * Number($("#" + sort + "_amt" + idx).val());
	        $("#" + sort + "_price" + idx).val(result.format());
	    } else {
	        $("#" + sort + "_price" + idx).val("가격정보가 없음");
	    }
        };
 
 	if (sort == "paper") {
            data.paper_name = $("#paper_name" + idx).val();
            data.paper_dvs = $("#paper_dvs" + idx).val();
            data.paper_color = $("#paper_color" + idx).val();
            data.paper_basisweight = $("#paper_basisweight" + idx).val();
            data.paper_unit = $("#paper_unit" + idx).val();
	} else if (sort == "output") {
	    data.output_name = $("#output_name" + idx).val();
	    data.output_board = $("#output_board" + idx).val();
	    data.output_size = $("#output_size" + idx).val();
	} else if (sort == "print") {
            data.cate_sortcode = $("#cate_sortcode" + idx).val();
            data.print_name = $("#print_name" + idx).val();
            data.print_purp = $("#print_purp" + idx).val();
            data.print_unit = $("#print_unit" + idx).val();
        } else if (sort === "after") {
            data.after_name = $("#after_name" + idx).val();
            data.after_depth1 = $("#after_depth_one" + idx).val();
            data.after_depth2 = $("#after_depth_two" + idx).val();
            data.after_depth3 = $("#after_depth_thr" + idx).val();
            data.after_unit = $("#after_unit" + idx).val();
        } 

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

/**
* @brief 견적등록
*/
var regiEsti = function(seqno) {

    showMask();
    var param = "";
    param += "?seqno="+ seqno;
    param += "&memo="+ encodeURIComponent($("#memo").val());
    param += "&answ_cont="+ encodeURIComponent($("#answ_cont").val());
    param += "&expec_order_date="+ encodeURIComponent($("#expec_order_date").val());
    param += "&supply_price="+ encodeURIComponent($("#supply_price").val());
    param += "&esti_price="+ encodeURIComponent($("#esti_price").val());
    param += "&sale_price="+ encodeURIComponent($("#sale_price").val());
    param += "&vat="+ encodeURIComponent($("#vat").val());

    var uploader = uploaderObj;
    var url = "/proc/business/esti_list/upload_file.php" + param;
    uploader.settings.url = url;
    uploader.start();
}

//공급가액 구하는 함수
var getSupply = function() {

    var paper_price = 0;
    var output_price = 0;
    var print_price = 0;
    var after_price = 0;
    var etc_price = 0;
    var spc_pattern = /[^(가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9)]/gi; 

    //종이가격
    for (var i = 1; i <= paperIdx; i++) {
        if ($("#paper_price" + i).val() != "가격정보가 없음") {
            paper_price = paper_price + Number($("#paper_price" + i).val().replace(spc_pattern, ""))
        }
    }
    //출력가격
    for (var i = 1; i <= outputIdx; i++) {
        if ($("#output_price" + i).val() != "가격정보가 없음") {
            output_price = output_price + Number($("#output_price" + i).val().replace(spc_pattern, ""))
        }
    }
    //인쇄가격
    for (var i = 1; i <= printIdx; i++) {
        if ($("#print_price" + i).val() != "가격정보가 없음") {
            print_price = print_price + Number($("#print_price" + i).val().replace(spc_pattern, ""))
        }
    }
    //후공정가격
    for (var i = 1; i <= afterIdx; i++) {
        if ($("#after_price" + i).val() != "가격정보가 없음") {
            after_price = after_price + Number($("#after_price" + i).val().replace(spc_pattern, ""))
        }
    }
    //기타가격
    for (var i = 1; i <= etcIdx; i++) {
        if ($("#etc_price" + i).val() != "가격정보가 없음") {
            etc_price = etc_price + Number($("#etc_price" + i).val().replace(spc_pattern, ""))
        }
    }

    var supply_price = paper_price + output_price + print_price + after_price + etc_price;
    var vat = supply_price / 10;
    var esti_price = supply_price + vat - $("#sale_price").val().replace(spc_pattern, "");
    $("#supply_price").val(supply_price.format());
    $("#vat").val(vat.format());
    $("#esti_price").val(esti_price.format());
}

var getEtcPrice = function(event, el) {
    event = event || window.event;

    var val = "";
    var spc_pattern = /[^(가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9)]/gi; 
    var keyID = (event.which) ? event.which : event.keyCode;
    if (keyID == 46 || keyID == 37 || keyID == 39) {
        return;
    } else {
        event.target.value = event.target.value.replace(/[^0-9]/g, "").format();
    }
}

//견적가액 구하는 함수
var getEstimated = function(event, el) {
    event = event || window.event;

    var val = "";
    var spc_pattern = /[^(가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9)]/gi; 
    var keyID = (event.which) ? event.which : event.keyCode;
    if (keyID == 46 || keyID == 37 || keyID == 39) {
        return;
    } else {
        event.target.value = event.target.value.replace(/[^0-9]/g, "").format();
	    val = Number($("#supply_price").val().replace(spc_pattern, "")) 
        + Number($("#vat").val().replace(spc_pattern, "")) 
        - Number(el.value.replace(spc_pattern, ""));
	    val = val.format();

        $("#esti_price").val(val);
    }
}

//관리자가올린 견적파일 다운로드
var adminEstiFileDown = function(seq) {
    var url = "/common/admin_esti_file_down.php?seqno=" + seq;
    location.href = url;
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
  //var uploadId = "work_file_upload";
    var delId    = "work_file_del";

    var uploader = new plupload.Uploader({
        url                 : "/proc/business/esti_list/upload_file.php",
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
                // 기존에 업로드 된 파일이 있는 경우
                if ($("#uploaded_work_file_seqno").val()) {
                    if (confirm("기존 파일은 삭제합니다." + 
                                "\n계속 하시겠습니까?") === false) {
                        return false;
                    }

                    var fileSeqno = $("#uploaded_work_file_seqno").val();
                    removeFile(fileSeqno, false);
                    $("#uploaded_work_file").html('');
                }

                // 파일을 새로 추가 할 경우
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
		                    "<span id=\"" + file.id + "\" style=\"margin-left: 105px;\">" +
		                    file.name + " (" +
		                    plupload.formatSize(file.size) +
		                    ")<b></b>" +
                            "&nbsp;" +
                            "<img src=\"/design_template/images/btn_circle_x_red.png\"" +
                            "     id=\"work_file_del\"" +
                            "     file_seqno=\"\"" +
                            "     alt=\"X\"" +
                            "     onclick=\"removeFile('', true);\"" +
                            "     style=\"cursor:pointer;\" /></span>";
                });
            },
            FilesRemoved : function(up, files) {
                document.getElementById(listId).innerHTML = '';
                $("#work_file_seqno").val('');
            },
            UploadProgress : function(up, file) {
                fileFlag = 1;
                document.getElementById(file.id)
                        .getElementsByTagName("b")[0]
                        .innerHTML = "<span>" + file.percent + "%</span>";
            },
            FileUploaded : function(up, file, response) {
                var jsonObj = JSON.parse(response.response);
                var fileSeqno = jsonObj.file_seqno;
		var result = jsonObj.result;
                
		console.log(jsonObj);
                $("#" + delId).attr(
                    {"onclick"    : "removeFile('" + fileSeqno + "', true);",
                     "file_seqno" : fileSeqno}
                );

                fileFlag = 2;

                $("#work_file_seqno").val(fileSeqno);

                if (result == 1) {
	            hideMask();
                    cndSearch.exec(30, 1, "pop");
                    $("#esti_cont_ctrl").remove();
                    alert("견적을 등록하였습니다.");
                } else {
                    alert("견적등록을 실패하였습니다.");
                }

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
 * @brief 업로드된 기존파일 삭제버튼 클릭시
 *
 * @param seqno = 주문 파일 일련번호
 */
var removeUploadedFile = function(seqno) {

    if (confirm("기존 파일을 삭제합니다." + 
                "\n계속 하시겠습니까?") === false) {
        return false;
    }

    var fileSeqno = seqno;

    removeFile(fileSeqno, false);
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

    var url = "/proc/business/esti_list/del_esti_file.php";
    var data = {
        "admin_esti_file_seqno"  : seqno
    };
    var callback = function(result) {

        if (result == "F") {
            alert("파일 정보 삭제에 실패했습니다.");
            return false;
        }

        $("#uploaded_work_file_seqno").val('');
        $("#uploaded_work_file").html('');

        if (flag === true) {
            var uploader = uploaderObj;
            var files = uploader.files;
            uploader.removeFile(files[0]);
        }
    };

    showMask();
    ajaxCall(url, "text", data, callback);
};
