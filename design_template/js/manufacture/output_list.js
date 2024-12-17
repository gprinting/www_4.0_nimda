var previous;
$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');

    //dateSet('0'); 
    searchProcess(30, 1);
    //$("#extnl_etprs").change();
});

//보여줄 페이지 수
var showPage = 30;
	
//선택 조건으로 검색
var searchProcess = function(showPage, page) {

    if($("#state").val() == "3120") {
        $("#btn_complete").hide();
    } else {
        $("#btn_complete").show();
    }

    var url = "/ajax/manufacture/output_list/load_output_list.php";
    var blank = "<tr><td colspan=\"9\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "state"       : $("#state").val(),
        "category"       : $("#category").val(),
        "preset_cate" : $("#preset_cate").val(),
        "typset_num"  : $("#typset_num").val(),
        "date_cnd"    : $("#date_cnd").val(),
        "date_from"   : $("#basic_from").val(),
        "date_to"     : $("#basic_to").val()
    };
    var callback = function(result) {
        var rs = result.split("♪");
        if (rs[0].trim() == "") {
            $("#list").html(blank);
            return false;
        }
        $("#list").html(rs[0]);
        $("#page").html(rs[1]);
	$("#allCheck").prop("checked", false);
    };

    data.showPage      = showPage;
    data.page          = page;

    showMask();
    ajaxCall(url, "html", data, callback);
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    showPage = val;
    searchProcess(showPage, 1);
}

//상품리스트 페이지 이동
var movePage = function(val) {
    searchProcess(showPage, val);
}

var extnl_etprs_changed = function(seqno, element) {
    newValue = element.value;
    if(confirm("인쇄소를 변경하시겠습니까?")) {
        var url = "/ajax/manufacture/output_list/update_extnl_etprs.php";
        var data = {
            "extnl_etprs" : newValue,
            "seqno"       : seqno
        };

        var callback = function(result) {
            if(result == 1) {
                alert("변경완료");
            } else {
                alert("변경에 실패하였습니다. 잠시후 다시 시도해주십시오.");
                element.defaultValue = previous;
            }
            showBgMask();
        };

        showMask();
        ajaxCall(url, "html", data, callback);

    } else  {
        element.defaultValue = previous;
    }
}

var downloadURL = function(url) {
    window.open(url, 'Download');
}

//출력 작업금액
var getWorkPrice = function() {

    var url = "/ajax/manufacture/output_list/load_output_work_price.php";
    var data = { 
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "output_name"       : $("#output_name").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "board"             : $("#board_dvs").val(),
        "size"              : $("#size").val()
    };

    var callback = function(result) {
	var price_val = result + " 원";
        $("#work_price_val").html(price_val);
        $("#work_price").val(result);
        showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 상세보기
var openDetailView = function(seqno) {
    var url = "/ajax/manufacture/output_list/load_output_detail_popup.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
        previous = $('#extnl_etprs option:selected').val();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var toPaperOp = function(seqno) {
    if($("#brand_seqno").val() == "") {
        alert('입고처를 선택해주세요');
        return;
    }

    var url = "/proc/manufacture/paper_op_mng/regi_paper_op_wait.php";
    var data = {
        "name"                : $("#paper_name").val(), //v
        "dvs"                 : "",//v
        "color"               : "",//v
        "basisweight"         : "",//v
        "op_affil"            : $("#paper_affil").val(),
        "op_size"             : $("#op_size > option:selected").val(),//v
        "storplace"           : $("#storplace").val(), //v
        "stor_subpaper"       : "1", // 절수
        "stor_size"           : $("#stor_size > option:selected").val(), // V
        "amt"                 : $("#paper_amt").val(), //V
        "amt_unit"            : "장", //V
        "memo"                : $("#paper_memo").val(), //V
        "brand_seqno"         : $("#brand_seqno").val(), // V
        //"grain"               : $("input[type=radio][name=paper_grain]:checked").val(), //V
        "typset_num"          : $("#typset_num1").val() //V
    };

    var callback = function(result) {
        if (result == 1) {
            $(".pop_add_box").hide();
            $(".li1").addClass("active");
            $("#output_seq_" + seqno).removeClass("orge");
            $("#output_seq_" + seqno).addClass("gray");
        } else {
            alert("종이발주서 추가 및 수정을 실패 하였습니다.");
        }
    }

    ajaxCall(url, "html", data, callback);
}

//출력 상세보기
var openPaperOpView = function(seqno) {
    var url = "/ajax/manufacture/output_list/load_paperop_detail_popup.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
        previous = $('#extnl_etprs option:selected').val();
        var size = $('#size').val();
        $('#op_size').val(size);
        $('#stor_size').val(size);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var openLabelView = function(seqno) {
    var url = "/ajax/manufacture/output_list/load_output_label_popup.php";

    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010", "727");
        $(document).ready(function() {
            $('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:7,
                vertical:true, //세로
                verticalHeight:664.55, //세로
                slideMargin: 0,
                // speed:500,
                // auto:true,
                loop:true,
                onSliderLoad: function() {
                    $('#image-gallery').removeClass('cS-hidden');
                }
            });
        });
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
//출력 이미지보기
var openImgView = function(seqno) {
    var url = "/ajax/manufacture/output_list/load_output_img_popup.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        openRegiPopup(result, "1010", "727");
        $(document).ready(function() {
            $('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:7,
                vertical:true, //세로
                verticalHeight:664.55, //세로
                slideMargin: 0,
               // speed:500,
               // auto:true,
                loop:true,
                onSliderLoad: function() {
                $('#image-gallery').removeClass('cS-hidden'); 
                }  
            });
        });
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 생산공정시작
var getStart = function(seqno) {

    var url = "/proc/manufacture/output_list/modi_output_process_start.php";
    var data = { 
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "board"             : $("#board_dvs").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("시작 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("시작을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 보류
var getHolding = function(seqno) {

    var url = "/proc/manufacture/output_list/modi_output_process_holding.php";
    var data = { 
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "board"             : $("#board_dvs").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("보류 하였습니다.");
	    hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("보류을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	    showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 생산공정 완료
var getFinish = function(seqno) {

    var url = "/proc/manufacture/output_list/modi_output_process_finish.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	    showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 생산공정 다중 완료
var multiFinish = function() {

    if (checkBlank(getselectedNo())) {
        alert("선택한 항목이 없습니다.");
        return false;
    }

    var url = "/proc/manufacture/output_list/modi_output_process_multi_finish.php";
    var data = { 
        "seqno" : getselectedNo() 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("완료 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("완료를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	    showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 생산공정 재작업
var getRestart = function(seqno) {

    var url = "/proc/manufacture/output_list/modi_output_process_restart.php";
    var data = { 
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "board"             : $("#board_dvs").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("재시작을 하였습니다.");
	    hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("재시작을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
	    showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

/*
//출력 생산공정 완료 후 추가 요인 수정
var getFinishUpdate = function(seqno) {

    var url = "/proc/produce/process_mng/modi_output_process_finish_update.php";
    var data = { 
        "seqno" : seqno,
	"amt"   : $("#modi_board_amt").val(),
	"memo"  : $("#modi_memo").val()
    };

    var callback = function(result) {
        showBgMask();
        if (result == 1) {
            alert("수정하였습니다.");
	    $("#amt").val($("#modi_board_amt").val());

        } else {
            alert("수정을 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 생산공정 취소 
var getCancel = function(seqno) {

    var url = "/proc/produce/process_mng/modi_output_process_cancel.php";
    var data = { 
        "seqno" : seqno 
    };

    var callback = function(result) {
        if (result == 1) {
            alert("작업취소 하였습니다.");
	    hideRegiPopup();
            searchProcess(30, 1);
        } else {
            alert("작업취소를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
*/

var multiPrint = function() {
    var selectedValue = "";

    $($("#list input[name=chk]:checked").get().reverse()).each(function() {
        var typset_num = $("#seqno_" + $(this).val()).text();
        openPrint(typset_num);
    });
}

// 생산 작업 지시 인쇄(전단만)
function openPrint(typset_num) {

    var url = "/ajax/manufacture/output_list/load_output_ord_html.php";
    var data = { 
        "typset_num" : typset_num 
    };

    var callback = function(result) {
    
        var PrintPage = window.open("_blank");
        PrintPage.document.open(); 
        PrintPage.document.write(result); 
        PrintPage.document.close();

        setTimeout(function(){ PrintPage.print();PrintPage.close();  }, 300);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
