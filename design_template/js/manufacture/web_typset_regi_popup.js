var order_detail_dvs_num = "";
var page_order_detail_brochure_seqno = "";
var typset_num = "";
var target = "receipt";
var selected = "N";
var div_idx = 0;

/***********************************************************************************
*** 주문
***********************************************************************************/

var orderFn = {
    //주문상세 리스트
    "list"       : function(seqno) {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_order_detail_list.php";
        var data = { 
            "order_common_seqno" : seqno 
        };

        var callback = function(result) {
            $("#order_detail_brochure_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //주문 보기
    "view"       : function(order_detail_brochure_seqno, idx, typstNum, pageSeqno) {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_order_detail_view.php";
        var data = { 
            "order_detail_brochure_seqno" : order_detail_brochure_seqno 
        };

        var callback = function(result) {
            var rs = result.split("♪");
            $("#paper_info").html(rs[0]);
            $("#size_info").html(rs[1]);
            $("#tmpt_info").html(rs[2]);
            $("#after_info").html(rs[3]);
	    $(".b_list").removeClass("selbg");
	    $(".list_ctrl" + idx).addClass("selbg");
	    order_detail_dvs_num = rs[4];
	    typset_num = typstNum;
	    page_order_detail_brochure_seqno = pageSeqno;
	    $(".ctrl_btn").show();
            $("#after_work_info").hide();
	    selected = "Y";
	    if (target == "typset") {
                typsetFn.view();         
	    } else if (target == "paper") {
                paperFn.list();         
	    } else if (target == "output") {
                outputFn.view();
	    } else if (target == "print") {
                printFn.view();
	    } else if (target == "after") {
                afterFn.list();
	    } else if (target == "produce") {
                produceFn.list();
	    }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //페이지 분판 팝업
    "pop"       : function(detailNum) {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_board_div_pop.php";
        var data = { 
            "order_detail_dvs_num" : detailNum 
        };

        var callback = function(result) {
	    showBgMask();
            openRegiPopup(result, 350);
            if (div_idx == 0) {
                div_idx = Number($("#div_idx").val());
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "add"       : function() {
        var newNum = 0;
        newNum = div_idx + 1;
	    div_idx = newNum;
        var url = "/ajax/manufacture/web_typset_regi_popup/load_add_div.php";
        var data = { 
            "num" : newNum 
        };

        var callback = function(result) {
            $("#add_div").append(result);
            showBgMask();
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "sub"       : function(num) {
	    div_idx = div_idx - 1;
        $("#del_div" + num).remove();
    },
    //페이지 분판
    "save"       : function(seqno, tot_count) {

    	if (confirm("조판을 등록하신 것을 분판 하시면 조판은 삭제 됩니다.\n계속 진행 하시겠습니까?") == false) {
                return false;
    	}

        var data = {
            "order_detail_dvs_num" : seqno 
        };
        var url = "/proc/manufacture/web_typset_regi_popup/regi_brochure_div_save.php";
        var total = 0;
        var count = "";
        for (var i = 1; i <= div_idx; i++) {
            if (typeof($("#div_val" + i).val()) != "undefined") {
                if (!checkBlank($("#div_val" + i).val())) {
                   
                    //책자형의 경우 페이지수를 짝수로 분판 해야함 
                    if (Number($("#div_val" + i).val()) % 2 != 0) {
                        alert("책자형 인쇄물을 분판할경우 페이지수를 짝수로 설정해주세요..");
                        return false;
                    }

                    count += "," + $("#div_val" + i).val();
                    total = total + Number($("#div_val" + i).val());
                } else {
                    alert("빈 값이 존재합니다.");
                    return false;
                }
            }
        }
        count = count.substring(1);

        data.count = count;
        data.tot_count = tot_count;
        data.new_tot_count = total;

        var callback = function(result) {
	        showBgMask();
            if (result == 1) {
                alert("분판을 하였습니다.");
	    	orderFn.list($("#order_common_seqno").val());
	    	hideRegiPopup();
            } else if (result == 2) {
                alert("페이지 합이 분판된 총 페이지와 다릅니다.");
            } else {
                alert("분판을 실패하였습니다. \n 관리자에게 문의해주세요.");
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

/***********************************************************************************
*** 접수
***********************************************************************************/

var receiptFn = {
    //주문상세 리스트
    "list"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_order_after_list.php";
        var data = { 
            "order_detail_dvs_num" : order_detail_dvs_num
        };

        var callback = function(result) {
            $("#after_info").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "up"       : function(order_after_history_seqno, order_detail_dvs_num, seq) {
        var url = "/proc/manufacture/web_typset_regi_popup/modi_after_sequp.php";
        var data = {
            "order_detail_dvs_num" : order_detail_dvs_num,
            "seq"   : seq
        };
        var callback = function(result) {
            if (result == 1) {
                receiptFn.list();
            } else {
                alert("순서변경을 실패 하였습니다. \n 관리자에게 문의 바람니다.");
            }
        }
        ajaxCall(url, "html", data, callback);
    },
    "down"       : function(order_after_history_seqno, order_detail_dvs_num, seq) {
        var url = "/proc/manufacture/web_typset_regi_popup/modi_after_seqdown.php";
        var data = {
            "order_detail_dvs_num" : order_detail_dvs_num,
            "seq"   : seq
        };
        var callback = function(result) {
            if (result == 1) {
                receiptFn.list();
            } else {
                alert("순서변경을 실패 하였습니다. \n 관리자에게 문의 바람니다.");
            }
        }
        ajaxCall(url, "html", data, callback);
    },
    "view"     : function(order_after_history_seqno, order_detail_dvs_num) {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_order_after_view.php";
        var data = {
            "order_after_history_seqno" : order_after_history_seqno,
            "order_detail_dvs_num"      : order_detail_dvs_num
        };
        var callback = function(result) {
            $("#after_work_info").show();
	    $("#after_view").html(result);
        }
        ajaxCall(url, "html", data, callback);
    },
    "op"     : function(after_op_seqno) {

        var url = "/proc/manufacture/web_typset_regi_popup/modi_after_op.php";
        var data = {
            "after_op_seqno"            : after_op_seqno,
            "after_name"                : $("#after_name").val(),
            "depth1"                    : $("#depth1").val(),
            "depth2"                    : $("#depth2").val(),
            "depth3"                    : $("#depth3").val(),
            "detail"                    : $("#detail").val(),
            "extnl_brand_seqno"         : $("#extnl_brand_seqno").val(),
            "amt"                       : $("#after_amt").val(),
            "memo"                      : $("#after_memo").val(),
            "dlvrboard"                 : $("#dlvrboard").val(),
            "order_detail_dvs_num"      : order_detail_dvs_num,
            "order_common_seqno"        : $("#order_common_seqno").val(),
            "order_after_history_seqno" : $("#order_after_history_seqno").val(),
            "seq"                       : $("#seq").val() 
        };
        var callback = function(result) {
            if (result == 1) {
                alert("후공정 발주서 등록 및 수정 하였습니다.");
            } else {
                alert("후공정 발주를 실패 하였습니다. \n 관리자에게 문의 바람니다.");
            }
        }
        ajaxCall(url, "html", data, callback);
    },   
    "manu"     : function(val) {
        var url = "/ajax/common/load_barnd_option.php";
        var data = { 
            "el"    : "after",
            "seqno" : val
        };

        var callback = function(result) {
            $("#extnl_brand_seqno").html(result);
        };

        ajaxCall(url, "html", data, callback);
    }
}

/***********************************************************************************
*** 조판
***********************************************************************************/

var typsetFn = {
    "view"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_typset_view.php";
        var data = { 
            "page_order_detail_brochure_seqno" : page_order_detail_brochure_seqno, 
            "typset_num" : typset_num 
        };

        var callback = function(result) {
            $("#stab2").html(result);
	    if (selected == "Y") {
	        $(".ctrl_btn").show();
	    }
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

	    typsetFn.size();
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //조판등록
    "modi"       : function() {

	var opt = "";
        $("input[name=opt]:checked").each(function() {
            opt += $(this).val() + " ";
        });

        var url = "/proc/manufacture/web_typset_regi_popup/modi_typset_info.php";
        var data = { 
            "order_detail_dvs_num"             : order_detail_dvs_num, 
            "page_order_detail_brochure_seqno" : page_order_detail_brochure_seqno, 
            "typset_num"                       : typset_num,
            "affil"                            : $("#typset_affil").val(),
            "subpaper"                         : $("#typset_subpaper").val(),
            "wid_size"                         : $("#typset_wid_size").val(),
            "vert_size"                        : $("#typset_vert_size").val(),
            "beforeside_tmpt"                  : $("#typset_beforeside_tmpt").val(),
            "beforeside_spc_tmpt"              : $("#typset_beforeside_spc_tmpt").val(),
            "aftside_tmpt"                     : $("#typset_aftside_tmpt").val(),
            "aftside_spc_tmpt"                 : $("#typset_aftside_spc_tmpt").val(),
            "honggak_yn"                       : $(':radio[name="typset_honggak_yn"]:checked').val(),
            "dlvrboard"                        : $("#typset_dlvrboard").val(),
            "print_amt"                        : $("#typset_print_amt").val(),
            "prdt_page"                        : $("#prdt_page").val(),
            "prdt_page_dvs"                    : $("#prdt_page_dvs").val(),
            "after_list"                       : $("#after_list").val(),
            "opt_list"                         : $("#opt_list").val(),
            "specialty_items"                  : opt,
            "memo"                             : $("#typset_memo").val()
        };

        var callback = function(result) {
            if (result == 1) {
                alert("조판 생성 및 수정 하였습니다.");
		orderFn.list($("#order_common_seqno").val());
	        typsetFn.init();
            } else {
                alert("등록 및 수정을 실패 하였습니다. \n 관리자에게 문의 바람니다.");
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //조판사이즈
    "size"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_typset_size.php";
        var data = { 
            "affil"    : $("#typset_affil").val(),
            "subpaper" : $("#typset_subpaper").val()
        };

        var callback = function(result) {
            var rs = result.split("♪");
	    $("#typset_wid_size").val(rs[0]);
	    $("#typset_vert_size").val(rs[1]);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    "init"      : function() {
        $("#typset_affil").val("국");
        $("#typset_subpaper").val("전절");
        $("#typset_wid_size").val("");
        $("#typset_vert_size").val("");
        $("#typset_beforeside_tmpt").val("");
        $("#typset_beforeside_spc_tmpt").val("");
        $("#typset_aftside_tmpt").val("");
        $("#typset_aftside_spc_tmpt").val("");
        $("#typset_dlvrboard").val("서울판");
        $("#typset_print_amt").val("");
        $("#prdt_page").val("");
        $("#prdt_page_dvs").val("");
        $("#typset_memo").val("");
        $("#opt1").prop("checked",false);
        $("#opt2").prop("checked",false);
        $("#opt3").prop("checked",false);
        $("#opt4").prop("checked",false);
        $("#opt5").prop("checked",false);
        $("#opt6").prop("checked",false);
        $("#opt7").prop("checked",false);
        $("#opt8").prop("checked",false);
	$(".ctrl_btn").hide();
	typsetFn.size();
    }
}

/***********************************************************************************
*** 종이
***********************************************************************************/

/**
 * @brief 종이함수
 */
var paperFn = {
    //매입 종이 리스트
    "info"       : function(sorting) {
        var tmp = sorting.split('/');
        var url = "/ajax/manufacture/paper_op_mng/load_paper_info_list.php";
        var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "sorting"           : tmp[0],
            "sorting_type"      : tmp[1],
            "extnl_etprs_seqno" : $("#paper_extnl_etprs_seqno").val(),
    	    "name"              : $("#name").val(),
    	    "dvs"               : $("#dvs").val(),
    	    "color"             : $("#color").val(),
    	    "basisweight"       : $("#basisweight").val()
        };
        var callback = function(result) {
            
            if (result.trim() == "") {
                $("#paper_info_list").html(blank);
                return false;
            }

            $("#paper_info_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //종이발주 리스트
    "list"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_paper_op_list.php";
        var blank = "<tr><td colspan=\"13\">검색 된 발주서가 없습니다.</td></tr>";
        var data = {
	    "typset_num" : typset_num
        };
        var callback = function(result) {
            
            if (result.trim() == "") {
                $("#list").html(blank);
                return false;
            }

            $("#list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //종이발주 상세보기
    "view"       : function(seqno) {
        var url = "/ajax/manufacture/paper_op_mng/load_paper_op_detail.php";
        var data = { 
            "paper_op_seqno" : seqno 
        };

        var callback = function(result) {
            var rs = result.split("♪");
            $("#paper_name").val(rs[0]);
            $("#paper_dvs").val(rs[1]);
            $("#paper_color").val(rs[2]);
            $("#paper_basisweight").val(rs[3] + rs[4]);
            $("#paper_manu_name").val(rs[5]);
            $("#paper_affil").val(rs[6]);
            $("#paper_op_wid_size").val(rs[7]);
            $("#paper_op_vert_size").val(rs[8]);
            $("#stor_place").val(rs[9]);
            $("#paper_subpaper").val(rs[10]);
            $("#paper_stor_wid_size").val(rs[11]);
            $("#paper_stor_vert_size").val(rs[12]);
            $("#grain").val(rs[13]);
            $("#paper_amt").val(rs[14]);
            $("#paper_amt_unit").val(rs[15]);
            $("#paper_memo").val(rs[16]);
            $("#paper_brand_seqno").val(rs[17]);
            $("#paper_op_seqno").val(rs[18]);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //종이발주서 작성
    "ord"       : function() {
        var url = "/proc/manufacture/paper_op_mng/regi_paper_op_wait.php";
        var data = {
            "name"                : $("#paper_name").val(),
            "dvs"                 : $("#paper_dvs").val(),
            "color"               : $("#paper_color").val(),
            "basisweight"         : $("#paper_basisweight").val(),
            "op_affil"            : $("#paper_affil").val(),
            "op_size"             : $("#paper_op_wid_size").val() + "*" + $("#paper_op_vert_size").val(),
            "storplace"           : $("#storplace").val(),
            "stor_subpaper"       : $("#paper_subpaper").val(),
            "stor_size"           : $("#paper_stor_wid_size").val() + "*" + $("#paper_stor_vert_size").val(),
            "amt"                 : $("#paper_amt").val(),
            "amt_unit"            : $("#paper_amt_unit").val(),
            "memo"                : $("#paper_memo").val(),
            "brand_seqno"         : $("#paper_brand_seqno").val(),
            "grain"               : $("input[type=radio][name=paper_grain]:checked").val(),
            "paper_op_seqno"      : $("#paper_op_seqno").val(),
            "typset_num"          : typset_num 
        };
        var callback = function(result) {
            if (result == 1) {
                alert("종이발주서를 추가 및 수정을 하였습니다.");
                paperFn.list();                    
            } else {
                alert("종이발주서 추가 및 수정을 실패 하였습니다.");
            }
        };

        ajaxCall(url, "html", data, callback);
    },
    //종이발주 취소
    "cancel"       : function(seqno) {
        var url = "/proc/manufacture/paper_op_mng/modi_paper_op_cancel.php";
        var data = { 
            "paper_op_seqno" : seqno 
        };
        var callback = function(result) {
            if (result == 1) {
                alert("종이발주를 취소 하였습니다..");
                paperFn.list();                    
            } else {
                alert("종이발주취소를 실패 하였습니다. \n 관리자에게 문의 바람니다.");
            }
        };

        ajaxCall(url, "html", data, callback);
    },
    //종이발주
    "op"       : function() {
        if (checkBlank(getselectedNo())) {
            alert("선택한 항목이 없습니다.");
            return false;
        }

        var url = "/proc/manufacture/paper_op_mng/regi_paper_op.php";
        var data = { 
            "paper_op_seqno" : getselectedNo()
        };

        var callback = function(result) {
            if (result == 1) {
                alert("종이를 발주 하였습니다. \n완료 되거나, 취소 된 발주는 발주가 되지 않습니다.");
                paperFn.list();                    
            } else {
                alert("종이발주를 실패 하였습니다. \n관리자에게 문의 해주세요.");
            }
        };

        ajaxCall(url, "html", data, callback);
    }
};

// 종이명 선택시 셀렉트박스 설정
var selectPaperName = function() {

    if ($("#name").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_name.php";
    var data = { 
        "name" : $("#name").val() 
    };
    var callback = function(result) {
        var arr = result.split("♪");
        $("#dvs").html(arr[0]);
        $("#color").html(arr[1]);
        $("#basisweigth").html(arr[2]);
        
        $("#dvs").removeAttr("disabled");
        $("#color").removeAttr("disabled");
        $("#basisweight").removeAttr("disabled");
        paperFn.info("");                    
    };

    ajaxCall(url, "html", data, callback);
}

// 종이구분 선택시 구분 셀렉트박스 설정
var selectPaperDvs = function() {

    if ($("#dvs").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_dvs.php";
    var data = { 
          "name" : $("#name").val(),
          "dvs"  : $("#dvs").val() 
    };

    var callback = function(result) {
        var arr = result.split("♪");
        $("#color").html(arr[0]);
        $("#basisweigth").html(arr[1]);
        paperFn.info("");                    
    };

    ajaxCall(url, "html", data, callback);
}

// 종이색상 선택시 구분 셀렉트박스 설정
var selectPaperColor = function() {

    if ($("#name").val() == "") {
        return false;
    }

    var url = "/ajax/manufacture/paper_stock_mng/load_paper_color.php";
    var data = { 
        "name"   : $("#name").val(), 
        "dvs"    : $("#dvs").val(), 
        "color"  : $("#color").val() 
    };

    var callback = function(result) {
        $("#basisweight").html(result);
        paperFn.info("");                    
    };

    ajaxCall(url, "html", data, callback);
}

//종이발주 상세보기
var paperOpView = function(seqno) {
    paperFn.view(seqno);                    
}

//종이발주 취소
var paperOpCancel = function(seqno) {
    paperFn.cancel(seqno);                    
}

//종이발주
var paperOp = function() {
    paperFn.op();                    
}

/***********************************************************************************
*** 출력
***********************************************************************************/

/**
 * @brief 출력함수
 */
var outputFn = {
    //출력업체 리스트
    "info"       : function(sorting) {
        var tmp = sorting.split('/');
        var url = "/ajax/manufacture/web_typset_regi_popup/load_output_info_list.php";
        var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "sorting"           : tmp[0],
            "sorting_type"      : tmp[1]
        };
        var callback = function(result) {
            
            if (result.trim() == "") {
                $("#output_info_list").html(blank);
                return false;
            }

            $("#output_info_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //출력 보기
    "view"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_output_view.php";
        var data = { 
            "typset_num" : typset_num 
        };

        var callback = function(result) {
            $("#output_view").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //출력 발주
    "op"       : function() {
        //validation check
        if ($(':radio[name="output_porcess_use_yn"]:checked').val() == "N") {
            alert('공정여부를 사용으로 변경해주세요.');
            return;
        }
        if ($("#output_name").val() === "" || $("#output_manu_name").val() === "" 
                || $("#output_affil").val() === "" || $("#output_board").val() === "") {
            alert('등록업체를 선택해주세요');
            return;
        }
        if (checkBlank($("#output_amt").val())) {
            alert('수량을 입력해주세요');
            return;
        }

        var url = "/proc/manufacture/web_typset_regi_popup/modi_output_op.php";
        var data = { 
            "typset_num" : typset_num,
            "name"       : $("#output_name").val(),
            "affil"      : $("#output_affil").val(),
            "size"       : $("#output_wid_size").val() + "*" + $("#output_vert_size").val(),
            "board"      : $("#output_board").val(),
            "amt"        : $("#output_amt").val(),
            "amt_unit"   : $("#output_amt_unit").val(),
            "memo"       : $("#output_memo").val(),
            "brand_seqno": $("#output_brand_seqno").val(),
        };

        var callback = function(result) {
            if (result == 1) {
                alert("출력 지시서를 수정하였습니다.");
            } else {
                alert("출력 지시서 수정을 실패 하였습니다.");
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

/***********************************************************************************
*** 인쇄
***********************************************************************************/

/**
 * @brief 인쇄함수
 */
var printFn = {
    //인쇄업체 리스트
    "info"       : function(sorting) {
        var tmp = sorting.split('/');
        var url = "/ajax/manufacture/web_typset_regi_popup/load_print_info_list.php";
        var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "sorting"           : tmp[0],
            "sorting_type"      : tmp[1],
            "extnl_etprs_seqno" : $("#print_extnl_etprs_seqno").val()
        };
        var callback = function(result) {
            
            if (result.trim() == "") {
                $("#print_info_list").html(blank);
                return false;
            }

            $("#print_info_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //인쇄 보기
    "view"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_print_view.php";
        var data = { 
            "typset_num" : typset_num 
        };

        var callback = function(result) {
            $("#print_view").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //인쇄 보기
    "op"       : function() {
 
        //validation check
        if ($(':radio[name="print_porcess_use_yn"]:checked').val() == "N") {
            alert('사용으로 변경해주세요.');
            return;
        }
        if ($("#print_name").val() === "" || $("#print_manu_name").val() === "" 
                || $("#print_affil").val() === "") {
            alert('등록업체를 선택해주세요');
            return;
        }

        var url = "/proc/manufacture/web_typset_regi_popup/modi_print_op.php";
        var data = { 
            "typset_num"          : typset_num,
            "name"                : $("#print_name").val(),
            "affil"               : $("#print_affil").val(),
            "size"                : $("#print_wid_size").val() + "*" + $("#print_vert_size").val(),
            "beforeside_tmpt"     : $("#print_beforeside_tmpt").val(),
            "beforeside_spc_tmpt" : $("#print_beforeside_spc_tmpt").val(),
            "aftside_tmpt"        : $("#print_aftside_tmpt").val(),
            "aftside_spc_tmpt"    : $("#print_aftside_spc_tmpt").val(),
            "tot_tmpt"            : $("#print_tot_tmpt").val(),
            "memo"                : $("#print_memo").val(),
            "brand_seqno"         : $("#print_brand_seqno").val(),
        };

        var callback = function(result) {
            if (result == 1) {
                alert("인쇄 지시서를 수정하였습니다.");
            } else {
                alert("인쇄 지시서 수정을 실패 하였습니다.");
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

/***********************************************************************************
*** 조판후공정
***********************************************************************************/

/**
 * @brief 조판후공정함수
 */
var afterFn = {
    //후공정업체 리스트
    "info"       : function(sorting) {
        var tmp = sorting.split('/');
        var url = "/ajax/manufacture/web_typset_regi_popup/load_after_info_list.php";
        var blank = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "sorting"           : tmp[0],
            "sorting_type"      : tmp[1],
            "extnl_etprs_seqno" : $("#after_extnl_etprs_seqno").val()
        };
        var callback = function(result) {
            
            if (result.trim() == "") {
                $("#after_info_list").html(blank);
                return false;
            }

            $("#after_info_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //조판후공정 리스트
    "list"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_basic_after_list.php";
        var blank = "<tr><td colspan=\"7\">검색 된 내용이 없습니다.</td></tr>";
        var data = { 
            "typset_num" : typset_num 
        };

        var callback = function(result) {
            if (result.trim() == "") {
                $("#basic_after_list").html(blank);
		return false;
	    }
            $("#basic_after_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //조판후공정 보기
    "view"       : function(seqno) {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_after_view.php";
        var data = { 
            "basic_after_op_seqno" : seqno 
        };

        var callback = function(result) {
            var rs = result.split("♪");
            $("#after_name").val(rs[0]);
            $("#after_depth1").val(rs[1]);
            $("#after_depth2").val(rs[2]);
            $("#after_depth3").val(rs[3]);
            $("#after_amt").html(rs[4] + rs[5]);
            $("#after_memo").val(rs[6]);
            $("#after_manu_name").val(rs[7]);
            $("#basic_after_op_seqno").val(seqno);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //후공정 수정
    "op"       : function() {
 
        var url = "/proc/manufacture/web_typset_regi_popup/modi_basic_after_op.php";
        var data = { 
            "basic_after_op_seqno" : $("#basic_after_op_seqno").val(),
            "after_name"           : $("#after_name").val(),
            "depth1"               : $("#after_depth1").val(),
            "depth2"               : $("#after_depth2").val(),
            "depth3"               : $("#after_depth3").val(),
            "amt"                  : $("#after_amt").val(),
            "memo"                 : $("#after_memo").val(),
            "brand_seqno"          : $("#after_extnl_brand_seqno").val(),
        };

        var callback = function(result) {
            if (result == 1) {
                alert("후공정 지시서를 수정하였습니다.");
		afterFn.list();
            } else {
                alert("후공정 지시서 수정을 실패 하였습니다.");
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
}

/***********************************************************************************
*** 생산
***********************************************************************************/

/**
 * @brief 조판확인 리스트
 */
var produceFn = {
    //조판확인 리스트
    "list"       : function() {
        var url = "/ajax/manufacture/web_typset_regi_popup/load_produce_list.php";
        var data = { 
            "order_detail_dvs_num" : order_detail_dvs_num,
            "typset_num"           : typset_num 
        };

        var callback = function(result) {
            if (result.trim() == "") {
                $("#produce_list").html(blank);
		return false;
	    }
            $("#produce_list").html(result);
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    },
    //생산 시작 및 검수 완료
    "start"       : function() {
        var url = "/proc/manufacture/web_typset_regi_popup/modi_produce_start.php";
        var data = {
            "typset_num" : typset_num 
        };

        var callback = function(result) {
            if (result == 1) {
                window.opener.location.reload();
                window.close();
                alert("지시서를 발주하였습니다.");
            } else if (result == 2) {
                alert("조판을 등록 해주세요.");
            } else if (result == 3) {
                alert("출력을 등록 해주세요.");
            } else if (result == 4) {
                alert("인쇄를 등록 해주세요.");
            } else {
                alert("지시서발주를 실패하였습니다.");
            }
        }

        ajaxCall(url, "html", data, callback);
    }
}

/***********************************************************************************
*** 공통
***********************************************************************************/

//탭 변경시 호출
var tabCtrl = function(el) {

    target = el;
    if (el == "typset") {
        typsetFn.view();                    
    } else if (el == "paper") {
        paperFn.info("");                    
        paperFn.list();                    
    } else if (el == "output") {
        outputFn.info("");                    
        outputFn.view( );                   
    } else if (el == "print") {
        printFn.info("");                    
        printFn.view();                    
    } else if (el == "after") {
        afterFn.info("");                    
        afterFn.list();                    
    } else if (el == "produce") {
        produceFn.list();                    
    }
    $("#after_work_info").hide();
}

//제조사 변경시 등록업체 리스트 재호출
var changeManuListCall = function(el) {

    if (el == "paper") {
        paperFn.info("");   
    } else if (el ==  "output") {
        outputFn.info("");   
    } else if (el == "print") {
        printFn.info("");                    
    } else if (el == "after") {
        afterFn.info("");                    
    } 
}

//등록 업체 리스트 정렬
var sortList = function(val, el, target) {

    var flag = "";

    if ($(el).children().hasClass("fa-sort-desc")) {
        sortInit();
        $(el).children().addClass("fa-sort-asc");
        $(el).children().removeClass("fa-sort");
        flag = "ASC";
    } else {
        sortInit();
        $(el).children().addClass("fa-sort-desc");
        $(el).children().removeClass("fa-sort");
        flag = "DESC";
    }
var sorting = val + "/" + flag;
    var tabVal = "";

    if (target == "paper") {
        paperFn.info(sorting);
    } else if (target == "output") {
        outputFn.info(sorting);
    } else if (target == "print") {
        printFn.info(sorting);
    } else if (target == "after") {
        afterFn.info(sorting);
    }
}

//공정흐름상태 변경
var changePorcessUseYn = function(dvs, val) {

    showMask();
    var url = "/proc/manufacture/web_typset_regi_popup/modi_produce_process_flow.php";
    var data = {
        "typset_num" : typset_num,
        "dvs"        : dvs,
        "val"        : val
    };
    var callback = function(result) {
        if (result == 1) {
            alert("생산 공정여부를 수정하였습니다.");
        } else {
            alert("생산 공정여부 수정을 실패 하였습니다.");
        }
    }

    ajaxCall(url, "html", data, callback);
}
