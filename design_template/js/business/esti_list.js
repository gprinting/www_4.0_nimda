/*
 *
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/23 이청산 생성
 *=============================================================================
 *
 */

// 작업 파일 업로더 객체
var dvsArr = ["cover", "inner1", "inner2", "inner3"];
var dvsLength = dvsArr.length;

$(document).ready(function() {
    // 데이트피커
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0').attr('readonly', 'readonly');
        
    // 팀 별 검색에서 팀구분 값 로드
    cndSearch.exec(30, 1);
});

//보여줄 페이지 수
var showPage = "";

var paperIdx = 1;
var outputIdx = 1;
var printIdx = 1;
var afterIdx = 1;
var etcIdx = 1;

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "data": null,
    "exec": function() {
        var url = "/json/business/esti_list/load_esti_list.php";
        var data = {
    	    "search_dvs"     : $("#search_dvs").val(),
    	    "search_keyword" : $("#search_keyword").val(),
       	    "basic_from"     : $("#basic_from").val(),
    	    "basic_to"       : $("#basic_to").val(),
    	    "depar"          : $("#depar").val(),
    	    "empl"           : $("#empl").val(),
    	    "member_typ"     : $("#member_typ").val()
	    };
        var callback = function(result) {
            hideLoadingMask();
            $("#esti_list_table_tbody").html(result.list);
            pagingCommon("esti_list_table_page",
                         "changePageEstiList",
                         5,
                         result.result_cnt,
                         5,
                         "init");
        };

        this.data = data;
        showLoadingMask();
        ajaxCall(url, "json", data, callback);
    }
};

/**
 * @brief 검색리스트 페이지 변경시 호출
 *
 * @param page = 선택한 페이지
 */
var changePageEstiList = function(page) {
    //if ($("#esti_list_table_page_" + page).hasClass("page_accent")) {
    //    return false;
    //}
    if (isNaN(page)) {
        return false;
    }
    
    $(".esti_list_table_page").removeClass("page_accent");
    $("#esti_list_table_page_" + page).addClass("page_accent");
    
    var url = "/json/business/esti_list/load_esti_list.php";
    var data = cndSearch.data;
    data.page = page;
    data.page_dvs = '1';
    
    var callback = function(result) {
        hideLoadingMask();
        $("#esti_list_table_tbody").html(result.list);
        
        //선택 영역 기억
        $("#" + saveSelectedBarArea.str_esti).addClass("active_tr");
    };

    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 판매채널 변경시 팀/담당자 변경
 *
 * @param val = 판매채널 일련번호
 */
var changeCpnAdmin = function(seqno) {
    var url = "/json/business/order_mng/load_depar_info.php";
    var data = {
        "seqno" : seqno
    };
    var callback = function(result) {
        $("#depar").html(result.depar);
        $("#empl").html(result.empl);
        $("#crm_info_depar").html(result.depar);
        $("#crm_info_empl").html(result.empl);
    };

    ajaxCall(url, "json", data, callback);
};

/**
 * @brief 리스트 중 하나 클릭시
 */
var loadEstiBaseInfo = {
    "data"  : null,
    "exec"  : function(seqno, flattypYn) {
        $(".esti_top").removeClass("active_tr");
        $("#esti_top_" + seqno).addClass("active_tr");
        $("#esti_detail").html('');

        $("#esti_origin_price").val('0');
        $("#esti_sale_rate").val('0');
        $("#esti_sale_price").val('0');
        $("#esti_sum").val('0');
        $("#supply_price").val('0');
        $("#esti_tax").val('0');
        $("#order_price").val('0');

        saveSelectedBarArea.exec();

        var url = "/json/business/esti_list/load_esti_base_info.php";
        var data = {
    	    "esti_seqno" : seqno
    	    ,"flattyp_yn" : flattypYn
	    };
        var callback = function(result) {
            hideLoadingMask();
            if (result.success < 0) {
                return alertReturnFalse("견적정보 불러오기에 실패했습니다.");
            }

            $("#table_esti_service_detail").html(result.base_info);
            $("#esti_file_name").html(result.file_name);
            $("#esti_file_down").attr("seqno", result.file_seqno);
            $("#esti_calc").attr("cs", result.sortcode);

            if (result.state === "7120") {
                $("#esti_calc").show();
                $("#esti_cancel").hide();
                $("#esti_mng_name").html('');
            } else if (result.state === "7130") {
                $("#esti_calc").hide();
                $("#esti_cancel").show();

                $("#esti_mng_name").html(result.esti_mng + "님이 견적중입니다.");
            } else if (result.state === "7190") {
                $("#esti_calc").hide();
                $("#esti_cancel").show();
                $("#esti_mng_name").html('');
            }
        };
        showLoadingMask();

        this.data = data;

        ajaxCall(url, "json", data, callback);
    }
};

var estiFileDown = function() {
    var seqno = $("#esti_file_down").attr("seqno");
    if (checkBlank(seqno)) {
        return false;
    }

    $("#file_ifr").attr("src", "/common/esti_file_down.php?seqno=" + seqno);
};

// 견적계산 시작
var calcEsti = function() {
    if (checkBlank(loadEstiBaseInfo.data)) {
        return false;
    }

    var url = "/json/business/esti_list/load_esti_detail.php";
    var data = loadEstiBaseInfo.data;
    data.cs = $("#esti_calc").attr("cs");
    var callback = function(result) {
        hideLoadingMask();

        $("#esti_calc").hide();
        $("#esti_cancel").show();

        $("#esti_detail").html(result.html);

        loadUnitPrice("all", '');
        loadAfterUnitPrice();
    };

    showLoadingMask();
    ajaxCall(url, "json", data, callback);
};

var cancelEsti = function() {
    var url = "/proc/business/esti_list/update_esti_state.php";
    var callback = function(result) {
        if (parseInt(result) < 0) {
            return alertReturnFalse("견적취소에 실패했습니다.");
        }

        $("#esti_calc").show();
        $("#esti_cancel").hide();
        $("#esti_mng_name").html('');

        return alertReturnFalse("견적을 취소했습니다.");
    };

    ajaxCall(url, "text", loadEstiBaseInfo.data, callback);
};

/**
 * @brief 리스트 선택영역 기억 함수
 */
var saveSelectedBarArea = { 
    "str_esti"      : null,
    "exec"          : function() {
        var str_esti = $(".esti_top.active_tr").attr("id");
        saveSelectedBarArea.str_esti = str_esti;
    }
};

/**
 * @brief 종이정보 load
 */
var loadPaperInfo = function(typ, dvs) {
    var prefix = getPrefix(typ) + "paper_";

    var url = "/ajax/business/esti_list/load_paper_info.php";
    var data = {
        "sort" : $(prefix + "sort").val()
        ,"name" : $(prefix + "name").val()
        ,"dvs" : dvs
    };
    var callback = function(result) {
        $(prefix + dvs).html(result);

        if (dvs === "name") {
            loadPaperInfo(typ, "info");
        }
    };

    ajaxCall(url, "html", data, callback);
};

var calcPrice = function(typ, dvs) {
    // row별 계산
    var prefix = getPrefix(typ) + dvs + '_';

    var unitPrice = parseFloat($(prefix + "unit_price").val());
    var tmpt      = parseFloat($(prefix + "tmpt").val());
    var machCount = parseFloat($(prefix + "mach_count").val());
    var rCount    = parseFloat($(prefix + "r_count").val());

    unitPrice = isNaN(unitPrice) ? 0 : unitPrice;
    tmpt      = isNaN(tmpt) ? 0 : tmpt;
    machCount = isNaN(machCount) ? 0 : machCount; 
    rCount    = isNaN(rCount) ? 0 : rCount;

    var price = unitPrice * tmpt * machCount * rCount;

    $(prefix + "price").val(price);

    calcEstiPrice();
};

var calcEstiPrice = function() {
    // 하단부 전체계산
    var originPrice = 0;
    $(".input_esti_list_detail_05").each(function() {
        var p = parseFloat($(this).val());
        p = isNaN(p) ? 0 : p;

        originPrice += p;
    });
    // 견적금액 DC
    var saleRate = parseFloat($("#esti_sale_rate").val());
    saleRate = isNaN(saleRate) ? 0.0 : saleRate;
    var salePrice = originPrice * (saleRate / 100.0);
    // 최종견적가(공급가 = 최종견적가)
    var estiSum = originPrice - salePrice;
    // 부가세
    var tax = estiSum * 0.1;
    // 총계
    var orderPrice = estiSum + tax;
    
    $("#esti_origin_price").val(originPrice.format());
    $("#esti_sale_rate").val(saleRate.format());
    $("#esti_sale_price").val(salePrice.format());
    $("#esti_sum").val(estiSum.format());
    $("#supply_price").val(estiSum.format());
    $("#esti_tax").val(tax.format());
    $("#order_price").val(orderPrice.format());
};

// 견적가 업데이트
var updateEstiPrice = function() {
    if (checkBlank(loadEstiBaseInfo.data)) {
        return false;
    }

    var data = {};
    data.esti_seqno = loadEstiBaseInfo.data.esti_seqno;
    data.flattyp_yn = loadEstiBaseInfo.data.flattyp_yn;
    data.memo       = $("#memo").val();
    for (var i = 0; i < dvsLength; i++) {
        var prefix = getPrefix(dvsArr[i]);

        if ($(prefix + "paper_price").length > 0) {
            var tmp = {
                 "paper_price"      : $(prefix + "paper_price").val()
                ,"paper_mpcode"     : $(prefix + "paper_info").val()
                ,"paper_unitprice"  : $(prefix + "paper_unit_price").val()
                ,"paper_tmpt"       : $(prefix + "paper_tmpt").val()
                ,"paper_mach_count" : $(prefix + "paper_mach_count").val()
                ,"paper_r_count"    : $(prefix + "paper_r_count").val()
                ,"paper_note"       : $(prefix + "paper_note").val()

                ,"output_price"      : $(prefix + "output_price").val()
                ,"output_mpcode"     : $(prefix + "output_mpcode").val()
                ,"output_unitprice"  : $(prefix + "output_unit_price").val()
                ,"output_tmpt"       : $(prefix + "output_tmpt").val()
                ,"output_mach_count" : $(prefix + "output_mach_count").val()
                ,"output_r_count"    : $(prefix + "output_r_count").val()
                ,"output_note"       : $(prefix + "output_note").val()

                ,"print_price"      : $(prefix + "print_price").val()
                ,"print_mpcode"     : $(prefix + "print_mpcode").val()
                ,"print_unitprice"  : $(prefix + "print_unit_price").val()
                ,"print_tmpt"       : $(prefix + "print_tmpt").val()
                ,"print_mach_count" : $(prefix + "print_mach_count").val()
                ,"print_r_count"    : $(prefix + "print_r_count").val()
                ,"print_note"       : $(prefix + "print_note").val()
            };

            if ($('.' + dvsArr[i] + "_after").length > 0) {
                var aft = {};
                $('.' + dvsArr[i] + "_after").each(function() {
                    var aftEn = $(this).attr("aft_en");
                    var seqno = $(this).attr("seqno");

                    aft[aftEn] = {
                         "seqno"      : seqno
                        ,"price"      : $(prefix + aftEn + "_price").val()
                        ,"unitprice"  : $(prefix + aftEn + "_unit_price").val()
                        ,"tmpt"       : $(prefix + aftEn + "_tmpt").val()
                        ,"mach_count" : $(prefix + aftEn + "_mach_count").val()
                        ,"r_count"    : $(prefix + aftEn + "_r_count").val()
                        ,"note"       : $(prefix + aftEn + "_note").val()
                    };
                });

                tmp["after"] = aft;
            }
            
            data[dvsArr[i]] = tmp;
        }
    }
    data.origin_price = $("#esti_origin_price").val();
    data.sale_rate    = $("#esti_sale_rate").val();
    data.sale_price   = $("#esti_sale_price").val();
    data.esti_price   = $("#supply_price").val();
    data.vat          = $("#esti_tax").val();
    data.order_price  = $("#order_price").val();

    var url = "/proc/business/esti_list/update_esti_price.php";
    var callback = function(result) {
        if (parseInt(result) < 0) {
            return alertReturnFalse("견적등록에 실패했습니다.");
        }

        alert("견적을 등록했습니다.");

        var page = $(".esti_list_table_page.page_accent").text();
        changePageEstiList(page);
    };

    ajaxCall(url, "text", data, callback);
};

var loadUnitPrice = function(typ, dvs) {
    var data = {};
    data.typ = typ;
    data.dvs = dvs;

    if (typ === "all") {
        for (var i = 0; i < dvsLength; i++) {
            var typ = dvsArr[i];
            var prefix = getPrefix(typ);

            if ($(prefix + "paper_info").length > 0) {
                data[typ] = {
                     "paper"  : $(prefix + "paper_info").val()
                    ,"output" : $(prefix + "output_mpcode").val()
                    ,"print"  : $(prefix + "print_mpcode").val()
                    ,"print_r_count" : $(prefix + "print_r_count").val()
                };
            }
        }
    } else {
        var temp = null;
        var prefix = getPrefix(typ);
        if (dvs === "paper") {
            temp = {"paper" : $(prefix + "paper_info").val()};
        }
        if (dvs === "output") {
            temp = {"output" : $(prefix + "output_mpcode").val()};
        }
        if (dvs === "print") {
            temp = {
                 "print"  : $(prefix + "print_mpcode").val()
                ,"print_r_count" : $(prefix + "print_r_count").val()
            };
        }

        data[typ] = temp;
    }

    var url = "/json/business/esti_list/load_unitprice.php";
    var callback = function(result) {
        for (var i = 0; i < dvsLength; i++) {
            typ = dvsArr[i];
            var prefix = getPrefix(typ);
            var priceArr = result[typ];

            if (checkBlank(priceArr)) {
                continue;
            }

            var paperPrice  = ceilVal(priceArr.paper);
            var outputPrice = ceilVal(priceArr.output);
            var printPrice  = ceilVal(priceArr.print);

            if (!checkBlank(paperPrice)) {
                $(prefix + "paper_unit_price").val(paperPrice);
                calcPrice(typ, 'paper');
            }
            if (!checkBlank(outputPrice)) {
                $(prefix + "output_unit_price").val(outputPrice);
                calcPrice(typ, 'output');
            }
            if (!checkBlank(printPrice)) {
                $(prefix + "print_unit_price").val(printPrice);
                calcPrice(typ, 'print');
            }
        }
    };

    ajaxCall(url, "json", data, callback);
};

var loadAfterUnitPrice = function() {
    var data = [];

    for (var i = 0; i < dvsLength; i++) {
        var typ = dvsArr[i];
        var $obj = $('.' + typ + "_after");

        if ($obj.length > 0) {
            var temp = [];
            var dupChk = '';
            $obj.each(function() {
                var mpcode = $(this).attr("mpcode");

                if (dupChk.indexOf(mpcode) > -1) {
                    return true
                }

                data.push(mpcode);
            });
        }
    }

    var url = "/json/business/esti_list/load_after_unitprice.php";
    var callback = function(result) {
        for (var i = 0; i < dvsLength; i++) {
            var typ = dvsArr[i];
            var prefix = getPrefix(typ);

            $('.' + typ + "_after").each(function() {
                var mpcode = $(this).attr("mpcode");

                if (!checkBlank(result[mpcode])) {
                    var aftEn = $(this).attr("aft_en");
                    $(prefix + aftEn + "_unit_price").val(result[mpcode]);
                    calcPrice(typ, aftEn);
                }
            });
        }
    };

    ajaxCall(url, "json", {"mpcode_arr" : data}, callback);
};

var loadAfterDepth = function(typ, dvs) {
    var prefix = getPrefix(typ) + "after_";

    var url = "/ajax/business/esti_list/load_after_depth.php";
    var data = {
         "name"   : $(prefix + "name").val()
        ,"depth1" : $(prefix + "depth1").val()
        ,"depth2" : $(prefix + "depth2").val()
        ,"cs"  : $("#esti_calc").attr("cs")
        ,"dvs" : dvs
    };
    var callback = function(result) {
        if (dvs !== "depth3") {
            result = "<option value=\"\">" + dvs + "</option>" + result;
        }
        $(prefix + dvs).html(result);
    };

    ajaxCall(url, "html", data, callback);
};

var deleteAfterHistory = function(seqno) {
    if(!confirm("해당 후가공을 삭제하시겠습니까?")) {
        return false;
    }

    var url = "/proc/business/esti_list/delete_after_history.php";
    var data = {
        "seqno" : seqno
    };
    var callback = function(result) {
        if (parseInt(result) < 0) {
            return alertReturnFalse("후가공 내역 삭제에 실패했습니다.");
        }

        $("#aft_" + seqno).remove();
    };

    ajaxCall(url, "text", data, callback);
};

var addAfterHistory = function(dvs, dvs_num) {
    var prefix = getPrefix(dvs);
    var mpcode = $(prefix + "after_depth3").val();

    if (checkBlank(mpcode)) {
        return false;
    }

    var name = $(prefix + "after_name").val();
    var depth1 = $(prefix + "after_depth1").val();
    var depth2 = $(prefix + "after_depth2").val();
    var depth3 = $(prefix + "after_depth3 > option:selected").text();

    var url = "/proc/business/esti_list/insert_after_history.php";
    var data = {
         "name" : name
        ,"depth1" : depth1
        ,"depth2" : depth2
        ,"depth3" : depth3
        ,"mpcode" : mpcode
        ,"detail_dvs_num" : dvs_num
        ,"esti_seqno" : $(".esti_top.active_tr").attr("seqno")
    };
    var callback = function(result) {
        if (parseInt(result) < 0) {
            return alertReturnFalse("후가공 내역 추가에 실패했습니다.");
        } else if (parseInt(result) === 2) {
            return alertReturnFalse("이미 존재하는 후가공 입니다.");
        }

        calcEsti();
    };

    ajaxCall(url, "text", data, callback);
};
