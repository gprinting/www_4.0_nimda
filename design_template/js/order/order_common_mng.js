/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2015/12/23 엄준현 생성
 * 2015/12/29 임종건 일자 검색 관련 함수 수정
 * 2015/12/29 임종건 회원 팝업 검색 수정
 *=============================================================================
 *
 */

$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '-0');

    $("#check_all").change(function() {
        if($(this).is(':checked')) {
            $("input:checkbox[name='chk_list']").prop("checked", true);
        } else {
            $("input:checkbox[name='chk_list']").prop("checked", false);
        }
    });

    if($("#val_basic_from").val() != "")
        $("#basic_from").datepicker("setDate", $("#val_basic_from").val());
    if($("#val_basic_to").val() != "")
        $("#basic_to").datepicker("setDate", $("#val_basic_to").val());

    if($("#val_cate_top").val() != "") {
        var cate_top = $("#val_cate_top").val();
        $("#cate_top").val(cate_top);
        cateSelect.exec('top', cate_top, null,() => {
            if($("#val_cate_mid").val() != "") {
                var cate_mid = $("#val_cate_mid").val();
                $("#cate_mid").val(cate_mid);
                cateSelect.exec('mid', cate_mid, null, () => {
                    if($("#val_cate_bot").val() != "") {
                        var cate_bot = $("#val_cate_bot").val();
                        $("#cate_bot").val(cate_bot);
                    }
                });
            }
        });
    }

    if($("#val_search_dvs").val() != "") {
        var search_dvs = $("#val_search_dvs").val();
        if(search_dvs == "order_num" ||
            search_dvs == "order_mng" ||
            search_dvs == "typset_num" ||
            search_dvs == "invo_num")
            $("#search_dvs").val(search_dvs);
    }

    if($("#val_after").val() != "") {
        var after = $("#val_after").val();
        $("#after").val(after);
    }

    if($("#val_version").val() != "") {
        var version = $("#val_version").val();
        $("#version").val(version);
    }

    if($("#val_list_size").val() != "") {
        var list_size = $("#val_list_size").val();
        $("#list_set").val(list_size);
    }

    if($("#val_order_state").val() != "") {
        var order_state = $("#val_order_state").val();
        $("#order_state").val(order_state);
    }

    if($("#val_dlvr_way").val() != "") {
        var dlvr_way = $("#val_dlvr_way").val();
        $("#dlvr_way").val(dlvr_way);
    }

    if($("#val_openseqno").val() != "") {
        var openseqno = $("#val_openseqno").val();
        showOrderDetail(openseqno);
    }

    var page = 1;
    if($("#val_page").val() != "") {
        page = $("#val_page").val();
    }

    var list_size = 30;
    if($("#val_list_size").val() != "") {
        list_size = $("#val_list_size").val();
    }

    if($("#val_is_auto").val() == "all") {
        $("#auto_all").css('background', 'cornflowerblue');
    }

    if($("#val_is_auto").val() == "memo") {
        $("#auto_memo").css('background', 'cornflowerblue');
    }
});

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
    var to   = $('#' + prefix + "_to").val().split('-');

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

        var lastDay = new Date(y, (m).toString(), "00").getDate();

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

        } else if (lastDay < temp) {
            var calc = this.month({
                'y' : y,
                'm' : m,
                'd' : d,
                "calc" : -1
            });

            y = calc.y;
            m = calc.m;
            // 바뀐 월의 마지막 날에서 일자 감소
            d = temp - lastDay;
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
        var toStr = toY + '-' + toM + '-' + toD;

        return {
            "from" : new Date(fromStr),
            "to"   : new Date(toStr)
        };
    }
};

var allocate_accept = function(kind) {
    var i = 0;
    var ordernums = '';
    $("input[name=chk_list]").each(function(index, item){
        if($(this).is(':checked')) {
            if(i != 0) ordernums += "|";
            ordernums += this.id;
            i++;
        }
    });

    if(i == 0) {
        alert("선택된 제품이 없습니다.");
        return;
    }

    var url = "/ajax/order/order_common_mng/allocate_accept.php";
    var data = {
        "ordernums"       : ordernums,
        "kind"  : kind
    };

    var callback = function(result) {
        if(result == "0") {
            alert("담당자가 존재합니다.");
        } else {
            //alert("상태가 변경되었습니다.");
        }
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
}


var select_extnl = function() {
    showMask();
    var url = "/ajax/order/order_common_mng/load_select_extnl.php";
    var data = {

    };

    var el_width = 400;
    var el_height = 1300;

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            //var tmp = result.split("♪");

            hideMask();
            openRegiPopup(result, el_width,el_height);
        },
        error: getAjaxError
    });
}

var extnl_work_list = function(kind) {
    var i = 0;
    var ordernums = '';
    $("input[name=chk_list]").each(function(index, item){
        if($(this).is(':checked')) {
            if(i != 0) ordernums += "|";
            ordernums += this.id;
            i++;
        }
    });

    if(i == 0) {
        alert("선택된 제품이 없습니다.");
        return;
    }

    var url = "/ajax/order/order_common_mng/make_extnl_work_list_excel_" + kind + ".php?";
    url += "ordernums=" + ordernums;
    $("#file_ifr").attr("src", url);
}

var extnl_income_list = function() {
    var i = 0;
    var ordernums = '';
    $("input[name=chk_list]").each(function(index, item){
        if($(this).is(':checked')) {
            if(i != 0) ordernums += "|";
            ordernums += this.id;
            i++;
        }
    });

    if(i == 0) {
        alert("선택된 제품이 없습니다.");
        return;
    }

    var url = "/ajax/order/order_common_mng/load_extnl_work_income_list.php?";
    url += "ordernums=" + ordernums;
    $("#file_ifr").attr("src", url);
}

var extnl_complete = function() {
    var i = 0;
    var ordernums = '';
    $("input[name=chk_list]").each(function(index, item){
        if($(this).is(':checked')) {
            if(i != 0) ordernums += "|";
            ordernums += this.id;
            i++;
        }
    });

    if(i == 0) {
        alert("선택된 제품이 없습니다.");
        return;
    }

    var url = "/ajax/order/order_common_mng/change_state.php";
    var data = {
        "ordernums"       : ordernums,
        "state"  : "2320"
    };

    var callback = function(result) {
        var jsonObj = JSON.parse(result);
        console.log(jsonObj);
        if(jsonObj.length == 0)
            alert("외주처리 완료하였습니다");
        else {
            alert("상태변경 실패 : " + jsonObj.join('-'))
        }
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
}

var downloadTemplate = function(seqno, dvs) {
    if (checkBlank(seqno)) {
        return false;
    }

    var url = "/dataproc_mng/template_file_down.php?";
    url += "&seqno=" + seqno;
    url += "&dvs=" + dvs;

    $("#file_ifr").attr("src", url);
};

var change_state = function() {
    var i = 0;
    var ordernums = '';
    $("input[name=chk_list]").each(function(index, item){
        if($(this).is(':checked')) {
            if(i != 0) ordernums += "|";
            ordernums += this.id;
            i++;
        }
    });

    if(i == 0) {
        alert("선택된 제품이 없습니다.");
        return;
    }
    var state = $("#change_order_state > option:selected").val();
    if(state == "") {
        alert("변경할 상태를 선택해주세요.");
        return;
    }

    var url = "/ajax/order/order_common_mng/change_state.php";
    var data = {
        "ordernums"       : ordernums,
        "state"  : $("#change_order_state > option:selected").val()
    };

    var callback = function(result) {
        var jsonObj = JSON.parse(result);
        console.log(jsonObj);
        if(jsonObj.length == 0)
            alert("상태가 변경되었습니다.");
        else {
            alert("상태변경 실패 : " + jsonObj.join('-'))
        }
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
}

var change_state_toerror = function(ordernum) {
    var url = "/ajax/order/order_common_mng/change_state.php";
    var data = {
        "ordernums"       : ordernum,
        "state"  : "1370"
    };

    var callback = function(result) {
        alert("상태가 변경되었습니다.");
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
}

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "searchFlag" : false,
    "exec"       : function(listSize, page) {
        this.searchFlag = true;
        // 리스트 사이즈 변경시 마지막 seqno 초기화

        var url = "/ajax/order/order_common_mng/load_order_list.php";
        var data = {
            "page"       : page,
            "list_size"  : listSize,
            "tab_dvs"    : this.tabDvs,
            "last_seqno" : $("#last_seqno").val()
        };

        data.sell_site     = $("#cpn_admin").val();
        data.sortcode = $("#cate_bot").val();
        if(data.sortcode == "")
            data.sortcode = $("#cate_mid").val();
        if(data.sortcode == "")
            data.sortcode = $("#cate_top").val();

        data.start_date     = $("#basic_from").val();
        data.end_date       = $("#basic_to").val();

        data.search_dvs = $("#search_dvs").val();
        data.search_keyword = $("#search_keyword").val();
        
        data.pay_way        = $("#pay_way").val();
        data.order_state        = $("#order_state").val();

        var callback = function(result) {
            var rs = result.split("♪");
            //$("#order_list").html(rs[0]);
            //$("#order_total").html(rs[1]);
            $("#paging").html(rs[2]);
        };

        showMask();

        ajaxCall(url, "html", data, callback);
    },
    "key" : function(event) {
        if (event.keyCode == 13) {
            cndSearch.exec(30, 1);
        }
    }
};

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {
    showPage = val;
    cndSearch.exec(val, 1);
}

/**
 * @brief 주문 상태 상세 조회팝업 출력
 *
 * @param seq = 주문일련번호
 */
var showOrderDetail = function(seqno) {
    $("#val_openseqno").val(seqno);
    if ($("#add_tr_" + seqno).hasClass("select_tr")) {
        $(".add_tr").removeClass('select_tr');
        $(".add_tr_view").hide();
    } else {
        $(".add_tr").removeClass('select_tr');
        $(".add_tr_view").hide();
        $("#add_tr_" + seqno).addClass('select_tr');
        $("#add_tr2_" + seqno).addClass('select_tr');
        $("#add_tr_view_" + seqno).show();

        var element = document.getElementById('add_tr_view_' + seqno);
        var offsetPosition = element.getBoundingClientRect().top;
        window.scrollTo({
            top: offsetPosition - 150
        });
    }
};

var submit_search = function(list_size, page) {
    $("#val_openseqno").val('');
    if(page == null) {
        $("#val_page").val(1);
        //$("#val_is_auto").val('');
    } else {
        $("#val_page").val(page);
    }

    if(list_size == null) {
        $("#val_list_size").val(30);
        //$("#val_is_auto").val('');
    } else {
        $("#val_list_size").val(list_size);
    }
    document.getElementById('search_input').submit();
}

var search_category = function(val1, val2, val3, state = "1320", val4) {
    if(state == "9x9"){
        state = "";
    }
    console.log("test-" + state);
    var date = new Date();
    date.setDate(date.getDate() - 3);

    var str_date = date.getFullYear() + "-" + ('0' + (date.getMonth()+1)).slice(-2) + "-" + ('0' + date.getDate()).slice(-2);
    $("#basic_from").val(str_date);
    $("#cate_top").val(val1);
    $("#val_paper").val(val4);
    cateSelect.exec('top', val1, null,() => {
        if(val2 == null) {
            $("#order_state").val(state);
            submit_search();
        } else {
            $("#cate_mid").val(val2);
            cateSelect.exec('mid', val2, null, () => {
                $("#cate_bot").val(val3);
                $("#order_state").val(state);
                submit_search();
            });
        }
    });
}

var search_auto = function(val) {
    if($("#val_is_auto").val() == val) {
        $("#val_is_auto").val('');
    } else {
        $("#val_is_auto").val(val);
    }
    submit_search();
}

var search_quick = function(val) {
    if($("#val_is_quick").val() == val) {
        $("#val_is_quick").val('');
    } else {
        $("#val_is_quick").val(val);
    }
    submit_search();
}

var search_quick_today = function(val) {
    if($("#val_is_quick_today").val() == val) {
        $("#val_is_quick_today").val('');
    } else {
        $("#val_is_quick_today").val(val);
    }
    submit_search();
}

var search_quick_tomorrow = function(val) {
    if($("#val_is_quick_tomorrow").val() == val) {
        $("#val_is_quick_tomorrow").val('');
    } else {
        $("#val_is_quick_tomorrow").val(val);
    }
    submit_search();
}

var submit_search_bykeyword = function() {
    if (window.event.keyCode == 13) {
        submit_search();
    }
}

var clickOrderDetail = function (seqno) {
    $("#val_openseqno").val(seqno);
    document.getElementById('search_input').submit();
}

/**
 * @brief 주문 작업메모등록
 *
 * @param seq = 주문일련번호
 */
var saveMemo = function(seqno) {
    var url = "/ajax/order/order_common_mng/update_workmemo.php";
    var data = {
        "seqno"       : seqno,
        "memo"  : $("#work_memo_" + seqno).val()
    };

    var callback = function(result) {
        alert("입력완료");
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
};

var changeReceiptSize = function(seqno) {
    var url = "/ajax/order/order_common_mng/update_acceptsize.php";
    var data = {
        "seqno"       : seqno,
        "wid"  : $("#receipt_size_wid_" + seqno).val(),
        "vert"  : $("#receipt_size_vert_" + seqno).val()
    };

    var callback = function(result) {
        alert("변경완료");
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
}


var changePrice = function(seqno) {
    var url = "/ajax/order/order_common_mng/update_price.php";
    var data = {
        "seqno"       : seqno,
        "price"  : $("#price").val(),
        "detail" : $("#change_cause_detail").val(),
    };

    var callback = function(result) {
        alert("변경완료");
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
}

var saveAcceptMemo = function(seqno) {
    var url = "/ajax/order/order_common_mng/update_acceptmemo.php";
    var data = {
        "seqno"       : seqno,
        "memo"  : $("#accept_memo_" + seqno).val()
    };

    var callback = function(result) {
        alert("입력완료");
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 주문 이력보기
 *
 * @param seq = 주문일련번호
 */
var showHistory = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 상세보기 인쇄
 *
 * @param seq = 주문일련번호
 */
var printDetail = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 택배첨부서류
 *
 * @param seq = 주문일련번호
 */
var attachDlvr = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 택배 청구서 인쇄
 *
 * @param seq = 주문일련번호
 */
var printDlvr = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 문자발송 
 *
 * @param seq = 주문일련번호
 */
var sendMsg = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 작업자간 메세지
 *
 * @param seq = 주문일련번호
 */
var sendWorkMsg = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 엑셀송장
 *
 * @param seq = 주문일련번호
 */
var excelDlvr = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 시안업로드 
 *
 * @param seq = 주문일련번호
 */
var uploadSample = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 금액수정
 *
 * @param seq = 주문일련번호
 */
var updatePrice = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 후가공업로드
 *
 * @param seq = 주문일련번호
 */
var uploadAfter = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 견적서업로드
 *
 * @param seq = 주문일련번호
 */
var uploadEsti = function(seqno) {
    alert("개발중");
};

/**
 * @brief 주문 수정
 *
 * @param seq = 주문일련번호
 */
var updateOrder = function(member_seqno, seqno) {
    var url = "/ajax/business/order_hand_regi/load_admin_hash.php";
    var callback = function(result) {
        window.open("http://new.gprinting.co.kr/common/login.php?seqno=" + member_seqno + "&flag=" + result.trim()+ "&isadmin=Y" + "&updateorder=" + seqno, "_blank");
        //window.open("http://devfront.goodprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
    };

    showMask();
    ajaxCall(url, "html", {}, callback);
};

/**
 * @brief 재작업/재주문 클릭
 *
 * @param seq = 주문일련번호
 */

var reworkOrder = function(seqno, amt, count, price) {

    showMask();
    var url = "/ajax/order/order_common_mng/load_rework_popup.php";
    var data = {
        "seqno"      : seqno,
        "amt"      : amt,
        "count"      : count,
        "price"      : price,
    };

    var el_width = 750;
    var el_height = 1050;

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            //var tmp = result.split("♪");

            hideMask();
            openRegiPopup(result, el_width,el_height);
        },
        error: getAjaxError
    });
}

var showHistory = function(seqno) {

    showMask();
    var url = "/ajax/order/order_common_mng/load_orderhistory_popup.php";
    var data = {
        "seqno"      : seqno
    };

    var el_width = 1250;
    var el_height = 1050;

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            //var tmp = result.split("♪");

            hideMask();
            openRegiPopup(result, el_width,el_height);
        },
        error: getAjaxError
    });
}

var showChangePrice = function(seqno) {

    showMask();
    var url = "/ajax/order/order_common_mng/load_changeprice_popup.php";
    var data = {
        "seqno"      : seqno
    };

    var el_width = 400;
    var el_height = 1300;

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            //var tmp = result.split("♪");

            hideMask();
            openRegiPopup(result, el_width,el_height);
        },
        error: getAjaxError
    });
}

var updateInvoice = function(seqno) {

    showMask();
    var url = "/ajax/order/order_common_mng/load_changedlvr_popup.php";
    var data = {
        "seqno"      : seqno
    };

    var el_width = 400;
    var el_height = 1300;

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            //var tmp = result.split("♪");

            hideMask();
            openRegiPopup(result, el_width,el_height);
        },
        error: getAjaxError
    });
}

var openBunDlvr = function(seqno) {
    var url = "/ajax/order/order_common_mng/load_bun_dlvr_html.php";
    var data = {
        "order_common_seqno" : seqno
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

var changeInvoNum = function(seqno) {
    var change_invo_num = $('#change_invo_num').val();
    var url = "/ajax/order/order_common_mng/change_invo_num.php";
    var data = {
        "seqno"         : seqno,
        "change_invo_num"   : change_invo_num,
        "invo_kind" : $("#invo_kind > option:selected").text(),
        "bun_group_dvs"   : $(':input[name=bun_group]:radio:checked').val()
    };

    $.ajax({
        type: "POST",
        data: data,
        url: url,
        success: function(result) {
            //var tmp = result.split("♪");
            alert("등록완료");
            hideMask();
            hideRegiPopup();
            //openRegiPopup(result, el_width,el_height);
        },
        error: getAjaxError
    });
}

var insertInfo = function(kind, seqno) {
    if(kind == "price") {
        changePrice(seqno);
    }
    else if(kind == "invo_num") {
        changeInvoNum(seqno);
    }
    else if(kind == "select_extnl") {
        kind = $("#extnl_kind").val();
        extnl_work_list(kind);
    }
    else if(kind == "rework") {
        var type_rework = $(':radio[name="type_rework"]:checked').val();
        if(type_rework == null) {
            alert("재작업 종류를 선택해주세요.");
            return;
        }

        var rework_price = $('#rework_price').val();
        if(rework_price == null) {
            alert("재작업 비용을 입력해주세요.");
            return;
        }

        var rework_amt = $('#rework_amt').val();
        if(rework_amt == null) {
            alert("재작업 매수를 입력해주세요.");
            return;
        }

        var rework_count = $('#rework_count').val();
        if(rework_count == null) {
            alert("재작업 건수를 입력해주세요.");
            return;
        }


        var rework_cause_detail = $('#rework_cause_detail').val();
        if(rework_cause_detail == null) {
            alert("재작업 사유를 입력해주세요.");
            return;
        }

        var rework_request_empl = $('#rework_request_empl').val();
        if(rework_request_empl == null) {
            alert("재작업 요청자를 입력해주세요.");
            return;
        }

        var rework_empl_id = $('#rework_empl_id').val();

        var url = "/ajax/order/order_common_mng/rework.php";
        var data = {
            "seqno"         : seqno,
            "type_rework"   : type_rework,
            "amt"           : rework_amt,
            "count"         : rework_count,
            "rework_cause_detail"    : rework_cause_detail,
            "rework_request_empl"    : rework_request_empl,
            "rework_empl_id"         : rework_empl_id,
            "rework_price"         : 0,
        };

        var el_width = 750;
        var el_height = 1050;

        $.ajax({
            type: "POST",
            data: data,
            url: url,
            success: function(result) {
                //var tmp = result.split("♪");
                alert("등록완료");
                hideMask();
                hideRegiPopup();
                //openRegiPopup(result, el_width,el_height);
            },
            error: getAjaxError
        });
    }
}

/**
 * @brief 주문 사용자화면 
 *
 * @param seq = 회원일련번호
 */
var goUserPage = function(seqno, channel) {
    var url = "/ajax/business/order_hand_regi/load_admin_hash.php";
    var callback = function(result) {
        if(channel == "GP") {
            window.open("http://www.gprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
        } else {
            window.open("http://www.dprinting.co.kr/common/login.php?seqno=" + seqno + "&flag=" + result.trim()+ "&isadmin=Y", "_blank");
        }
    };

    showMask();
    ajaxCall(url, "html", {}, callback);
};

/**
 * @brief 주문 사용자화면
 *
 * @param seq = 회원일련번호
 */
var deleteOrder = function(ordernum) {
    var url = "/ajax/order/order_common_mng/change_state.php";
    var data = {
        "ordernums"       : ordernum,
        "state"  : "1180"
    };

    var callback = function(result) {
        var jsonObj = JSON.parse(result);
        console.log(jsonObj);
        if(jsonObj.length == 0)
            alert("상태가 변경되었습니다.");
        else {
            alert("상태변경 실패 : " + jsonObj.join('-'))
        }
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
};

/**
 * @brief 주문 사용자화면
 *
 * @param seq = 회원일련번호
 */
var fileError = function(ordernum) {
    var url = "/ajax/order/order_common_mng/change_state.php";
    var data = {
        "ordernums"       : ordernum,
        "state"  : "1325"
    };

    var callback = function(result) {
        var jsonObj = JSON.parse(result);
        console.log(jsonObj);
        if(jsonObj.length == 0)
            alert("상태가 변경되었습니다.");
        else {
            alert("상태변경 실패 : " + jsonObj.join('-'))
        }
        location.reload();
    };

    showMask();

    ajaxCall(url, "html", data, callback);
};


/**
 * @brief  주문 상태 상세 조회팝업 숨김
 *
 * @param seq = 주문일련번호
 */
var hideOrderDetail = function(seqno) {
    $("#add_tr_" + seqno).removeClass('select_tr');
    $("#add_tr_view_" + seqno).hide();
    $("#add_tr2_" + seqno).removeClass('select_tr');
    $("#add_tr2_view_" + seqno).hide();
};
