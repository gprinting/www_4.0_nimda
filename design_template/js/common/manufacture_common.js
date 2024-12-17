/**
 * @brief 날짜 범위 설정 - 공통
 *
 * @param prefix   = 데이트피커 객체구분값
 * @param dvs      = 연산구분값
 * @param fromCalc = 시작일 계산할 일자
 * @param toCalc   = 종료일 계산할 일자
 * @param txt      = 버튼 텍스트값
 * @param toFix    = 종료일 고정여부
 */
var setDateVal = function(prefix, dvs, fromCalc, toCalc, txt, toFix) {
    if (dvs === 'a') {
        $('#' + prefix + "_from").val('');
        return false;
    } else if (dvs === 't') {
        $('#' + prefix + "_from").datepicker("setDate", '0');
        $('#' + prefix + "_to").datepicker("setDate", '0');
        return false;
    }

    var from = $('#' + prefix + "_from").val();
    var to   = $('#' + prefix + "_to").val();
    if(to == null) return;
    to = $('#' + prefix + "_to").val().split('-');

    if (checkBlank(from) || toFix) {
        from = to;
    } else {
        from = from.split('-');
    }

    var calc = calcDate.exec({
        "dvs"      : dvs,
        "fromCalc" : fromCalc,
        "toCalc"   : toCalc,
        "from" : {
            'y' : from[0],
            'm' : from[1],
            'd' : from[2]
        },
        "to" : {
            'y' : to[0],
            'm' : to[1],
            'd' : to[2]
        }
    });

    $('#' + prefix + "_from").datepicker("setDate", calc.from);
    $('#' + prefix + "_to").datepicker("setDate", calc.to);
};

/**
 * @brief 날짜 계산
 *
 * @param param = 계산용 파라미터
 * {
 *     dvs      = 계산기준
 *     fromCalc = 시작일 계산할 값
 *     toCalc   = 종료일 계산할 값
 *     from{}   = 시작 일자
 *     to{}     = 끝 일자
 *     from/to.y = 계산될 연
 *     from/to.m = 계산될 월
 *     from/to.d = 계산될 일
 * }

* @return 계산된 from/to Date객체
 */
var calcDate = {
    "year" : function(param) {
        var year = param.year;
        var calc = param.calc;

        return year + calc;
    },
    "month" : function(param) {
        var y = param.y;
        var m = param.m;
        var d = param.d;
        var calc = param.calc;

        var ret = m + calc;
    
        if (ret <= 0) {
            // 1월에서 전월일 경우 1년 감소
            y--;
            m = 12;
        } else {
            m = ret;
        }

        // 현재 월의 마지막 일이 계산된 월의 마지막 일보다 클 경우
        var lastDay = new Date(y, (m).toString(), "00").getDate();
        if (d > lastDay) {
            d = lastDay;
        }

        return {
            "y" : y,
            "m" : m,
            "d" : d
        };
    },
    "day" : function(param) {
        var y = param.y;
        var m = param.m;
        var d = param.d;
        var calc  = param.calc;

        // 해당 월의 마지막 일 검색
        var temp = d + calc;

        if (temp <= 0) {
            // 현재 일에서 계산된 일이 현재 연/월을 벗어나는 경우(이전)
            // ex) 2017-01-03의 1주일 전 = 2016-12-27
            var calc = this.month({
                'y' : y,
                'm' : m,
                'd' : d,
                "calc" : -1
            });

            y = calc.y;
            m = calc.m;
            // 바뀐 월의 마지막 날에서 일자 감소
            var lastDay = new Date(calc.y, (m).toString(), "00").getDate();
            d = lastDay + temp;
        } else {
            d = temp;
        }

        return {
            'y' : y,
            'm' : m,
            'd' : d
        };
    },
    "exec" : function(param) {
        var dvs      = param.dvs;
        var fromCalc = parseInt(param.fromCalc);
        var toCalc   = parseInt(param.toCalc);
        var from = param.from;
        var to   = param.to;
         
        var fromY = parseInt(from.y);
        var fromM = parseInt(from.m);
        var fromD = parseInt(from.d);
    
        var toY = parseInt(to.y);
        var toM = parseInt(to.m);
        var toD = parseInt(to.d);

        if (dvs === 'y') {
            // 연 계산
            fromY = this.year({
                "year" : fromY,
                "calc" : fromCalc
            });
            toY = this.year({
                "year" : toY,
                "calc" : toCalc
            });
        } else {
            var calcFrom = null;
            var calcTo   = null;

            if (dvs === 'm') {
                // 월 계산
                //
                calcFrom = this.month({
                    'y' : fromY,
                    'm' : fromM,
                    'd' : fromD,
                    'calc' : fromCalc
                });
                calcTo = this.month({
                    'y' : toY,
                    'm' : toM,
                    'd' : toD,
                    'calc' : toCalc
                });
            } else if (dvs === 'd') { 
                // 일 계산
                calcFrom = this.day({
                    'y' : fromY,
                    'm' : fromM,
                    'd' : fromD,
                    'calc' : fromCalc
                });
                calcTo = this.day({
                    'y' : toY,
                    'm' : toM,
                    'd' : toD,
                    'calc' : toCalc
                });
            }

            fromY = calcFrom.y;
            fromM = calcFrom.m;
            fromD = calcFrom.d;
            toY = calcTo.y;
            toM = calcTo.m;
            toD = calcTo.d;
        } 

        fromM = ("0" + fromM).substr(-2,2);
        toM   = ("0" + toM).substr(-2,2);
        fromD = ("0" + fromD).substr(-2,2);
        toD   = ("0" + toD).substr(-2,2);
    
        var fromStr = fromY + '-' + fromM + '-' + fromD;
        if (fromStr == "2017-08-8") {
            fromStr = "2017-08-08";
        }
        if (fromStr == "2017-08-1") { 
            fromStr = "2017-08-01";
        }

        var toStr = toY + '-' + toM + '-' + toD;

        return {
            "from" : new Date(fromStr),
            "to"   : new Date(toStr)
        };
    }
};

//작업자 메모 선택
var changeMemo = function(val) {

    if (val == "기타") {
        $("#worker_memo").prop("disabled", false);
        $("#worker_memo").val("");
        $("#worker_memo").focus();
    } else {
        $("#worker_memo").prop("disabled", true);
        $("#worker_memo").val(val);
    }
}

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

//수주처 변경시
var changeManu = function(el, val) {
 
    var url = "/ajax/produce/process_mng/load_brand_option.php";
    var data = { 
        "el"    : el,
        "seqno" : val
    };

    var callback = function(result) {
        $("#extnl_brand_seqno").html(result);
	    showBgMask();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//제조사 변경시
var changeManu = function(val) {
 
    var url = "/ajax/manufacture/common/load_output_brand_option.php";
    var data = { 
        "seqno" : val
    };

    var callback = function(result) {
        $("#extnl_brand_seqno").html(result);
	    showBgMask();
        getWorkPrice();
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//출력 값 적용 버튼
var getOutput = function() {

    var seqno = $("input[type=radio][name=output_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/manufacture/common/load_output_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#output_name").val(rs[0]);
        $("#output_manu_name").val(rs[1]);
        $("#output_affil").val(rs[2]);
        $("#output_wid_size").val(rs[3]);
        $("#output_vert_size").val(rs[4]);
        $("#output_board").val(rs[5]);
        $("#output_brand_seqno").val(rs[6]);
        $("#output_seqno").val(rs[7]);
    }

    ajaxCall(url, "html", data, callback);
}

//인쇄 값 적용 버튼
var getPrint = function() {

    var seqno = $("input[type=radio][name=print_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/manufacture/common/load_print_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#print_name").val(rs[0]);
        $("#print_manu_name").val(rs[1]);
        $("#print_affil").val(rs[2]);
        $("#print_wid_size").val(rs[3]);
        $("#print_vert_size").val(rs[4]);
        $("#print_brand_seqno").val(rs[5]);
        $("#print_seqno").val(rs[6]);
    }

    ajaxCall(url, "html", data, callback);
}

//종이값 적용
var getPaper = function() {

    var seqno = $("input[type=radio][name=paper_info]:checked").val();

    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/manufacture/paper_op_mng/load_paper_info.php";
    var data = {
        "seqno"       : seqno
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
        $("#paper_brand_seqno").val(rs[9]);
        $("#paper_seqno").val(rs[10]);


        $("#paper_stor_wid_size").val(rs[7]);
        $("#paper_stor_vert_size").val(rs[8]);
    }

    ajaxCall(url, "html", data, callback);
}

//후공정 값 적용 버튼
var getAfter = function() {

    var seqno = $("input[type=radio][name=after_info]:checked").val();
 
    if (checkBlank(seqno)) {
        alert("선택 된 라디오 버튼이 없습니다.");
        return false;
    }

    showMask();
    var url = "/ajax/produce/typset_list/load_after_info.php";
    var data = {
        "seqno"       : seqno
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#after_name").val(rs[0]);
        $("#after_manu_name").val(rs[1]);
        $("#after_extnl_brand_seqno").val(rs[2]);
        $("#after_depth1").val(rs[3]);
        $("#after_depth2").val(rs[4]);
        $("#after_depth3").val(rs[5]);
        $("#after_seqno").val(rs[6]);
    }

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 종이 절수별 사이즈 변경 
 */
var changeSubpaper = function(val) {
    if (checkBlank($("#paper_affil").val())) {
        $("#paper_subpaper").val("");
        alert("매입업체 종이가 입력 되어야 됩니다.");
        return false;
    }

    if(val == "") {
        var wid = $("#paper_op_wid_size").val();
        var vert = $("#paper_op_vert_size").val();

        $("#paper_stor_wid_size").val(wid);
        $("#paper_stor_vert_size").val(vert);
    } else {
        showMask();
        var url = "/ajax/produce/typset_list/load_subpaper_size_info.php";
        var data = {
            "affil": $("#paper_affil").val(),
            "subpaper": val
        };
        var callback = function (result) {
            var rs = result.split("♪");
            $("#paper_stor_wid_size").val(rs[0]);
            $("#paper_stor_vert_size").val(rs[1]);
        }

        ajaxCall(url, "html", data, callback);
    }
}

//종이발주 추가
var regiPaperOp = function(el) {

    if (checkBlank($("#paper_op_vert_size").val()) || checkBlank($("#paper_stor_vert_size").val())) {
        alert("사이즈를 입력해주세요");
	    return false;
    }

    if (checkBlank($("#brand_seqno").val())) {
        alert("발주처를 선택해주세요.");
        return false;
    }

    if (checkBlank($("#paper_amt").val())) {
        alert("수량을 입력해주세요.");
	    return false;
    }

    showMask();
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
        "brand_seqno"         : $("#brand_seqno").val(),
        "grain"               : $("input[type=radio][name=paper_grain]:checked").val(),
        "paper_op_seqno"      : $("#paper_op_seqno").val(),
        "typset_num"          : $("#typset_num").val() 
    };

    var callback = function(result) {
        if (result == 1) {

            if (el == "pop") {

            } else {
                cndSearch.exec(showPage, 1);
                getPaperOpViewInit();
                tabView("list");
                $(".li2").removeClass("active");
    	        $(".li1").addClass("active");
            }

            alert("종이발주서를 추가 및 수정을 하였습니다.");
        } else {
            alert("종이발주서 추가 및 수정을 실패 하였습니다.");
        }
    }

    ajaxCall(url, "html", data, callback);
}

$(document).ready(function() {
    //dateSet('7');
    //$(".datepicker_input").datepicker({
    //    format         : "yyyy-mm-dd",
    //    autoclose      : true,
    //    todayBtn       : "linked",
    //    todayHighlight : true,
    //    language       : "kr"
    //}).datepicker("setDate", '0').attr('readonly', 'readonly');
    //setDateVal('basic', 'd', -7,  0, '일주일', false);
    //searchProcess(30, 1);
});

//보여줄 페이지 수
var showPage = 30;

//선택 조건으로 검색
var searchProcess = function(showPage, page) {
    //var url = "/ajax/manufacture/after_list/load_after_list.php";
    //var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";
    //var data = {
    //    "state"             : $("#state").val(),
    //    "cate_sortcode"     : $("#cate_sortcode").val(),
    //    "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
    //    "date_cnd"          : $("#date_cnd").val(),
    //    "date_from"         : $("#basic_from").val(),
    //    "date_to"           : $("#basic_to").val(),
    //    "after"           : $("#after").val(),
    //};
    //var callback = function(result) {
    //    var rs = result.split("♪");
    //    $("#list1").html(rs[0]);
    //    $("#list2").html(rs[1]);
    //    $("#allCheck").prop("checked", false);
    //};
//
    //showMask();
    //ajaxCall(url, "html", data, callback);
}

//선택 조건으로 검색
var searchProcessByImposition = function(showPage, page) {

    var url = "/ajax/manufacture/basic_after_list/load_basic_after_list.php";
    var blank = "<tr><td colspan=\"10\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "state"             : $("#state").val(),
        "cate_sortcode"     : $("#cate_sortcode").val(),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val(),
        "date_cnd"          : $("#date_cnd").val(),
        "date_from"         : $("#basic_from").val(),
        "date_to"           : $("#basic_to").val(),
        "after"           : $("#after").val(),
    };
    var callback = function(result) {
        var rs = result.split("♪");
        $("#list1").html(rs[0]);
        $("#list2").html(rs[1]);
        $("#allCheck").prop("checked", false);
    };

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

//후공정 작업금액
var getWorkPrice = function() {

    var url = "/ajax/produce/process_mng/load_after_work_price.php";
    var data = {
        "extnl_brand_seqno" : $("#extnl_brand_seqno").val(),
        "after_name"        : $("#after_name").val(),
        "depth1"            : $("#depth1").val(),
        "depth2"            : $("#depth2").val(),
        "depth3"            : $("#depth3").val(),
        "amt"               : $("#amt").val().replace(/,/gi, ""),
        "amt_unit"          : $("#amt_unit").val()
    };

    var callback = function(result) {
        var val = result + " 원";
        $("#work_price_val").html(val);
        $("#work_price").val(result);
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 상세보기
var openDetailView = function(seqno) {
    var url = "/ajax/manufacture/after_list/load_after_detail_popup.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1010");
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 이미지보기
var openImgView = function(seqno) {
    var url = "/ajax/manufacture/after_list/load_after_img_popup.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        openRegiPopup(result, "1250");
        $(document).ready(function() {
            $('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:5,
                vertical:true, //세로
                verticalHeight:350, //세로
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

//후공정 생산공정시작
var getStart = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_start.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
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

//후공정 보류
var getHolding = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_holding.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
    };

    var callback = function(result) {
        if (result == 1) {
            alert("보류 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("보류를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

//후공정 생산공정완료
var getFinish = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_finish.php";
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

//후공정 생산공정 다중 완료
var multiFinish = function(seqno, state) {
    var url = "/proc/manufacture/after_list/modi_after_process_multi_finish.php";
    var data = {
        "seqno" : seqno,
        "state" : state
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

//후공정 생산공정 재작업
var getRestart = function(seqno) {

    var url = "/proc/produce/process_mng/modi_after_process_restart.php";
    var data = {
        "seqno"             : seqno,
        "worker_memo"       : $("#worker_memo").val(),
        "adjust_price"      : $("#adjust_price").val().replace(/,/gi, ""),
        "work_price"        : $("#work_price").val().replace(/,/gi, ""),
        "extnl_etprs_seqno" : $("#extnl_etprs_seqno").val()
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

//생산공정취소
var getCancel = function(seqno) {

    var url = "/proc/manufacture/after_list/modi_after_process_cancel.php";
    var data = {
        "seqno" : seqno
    };

    var callback = function(result) {
        if (result == 1) {
            alert("작업취소 하였습니다.");
            hideRegiPopup();
            searchProcess(showPage, 1);
        } else {
            alert("작업취소를 실패 하였습니다. \n 관리자에게 문의 하십시오.");
            showBgMask();
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
}
