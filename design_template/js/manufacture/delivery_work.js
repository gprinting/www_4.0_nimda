/*
 *
 * Copyright (c) 2015 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/03/21 임종건 생성
 *=============================================================================
 *
 */

$(document).ready(function() {
    $("#order_no").focus();

    alertOpened = false;
    //기존의 tab가 눌렸을때 하는 작동을 방지하고 새로운 명령을 등록
    $(document).keydown(function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==9) {
            if(alertOpened == false) {
                findButton("Y");
                e.preventDefault();
            } else {
                alertHideMask();
                e.preventDefault();
            }
        }
    });

    $("#bootstrap_alert").hide();
});

/**
 * @brief 선택조건으로 검색 클릭시
 */
var cndSearch = {
    "exec"       : function(change) {
        var url = "/ajax/produce/process_result/load_dlvr_waitin_op_process_result_list.php";
        var blank = "<tr><td colspan=\"20\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "order_detail_num" : $("#order_no").val(),
            "changestate_yn"             : change
        };
        var callback = function(result) {
            resetToTable();

            if(result == "") {
                return;
            }

            var obj = eval("("+result+")");
            var length = obj["product"].length;
            var is_bun = "";

            if(length == 1) {
                is_bun = "없음";
            } else {
                is_bun = "있음";
            }

            $("#amt").val(length);
            $("#is_bun").val(is_bun);

            for(i = 1 ; i <= length; i++) {
                a_obj = obj["product"][i-1];
                var order_state = a_obj.order_state;

                var order_state_first = order_state.substring(0, 1);

                var alertMessage = "존재하지 않는 상품입니다.";
                if (order_state_first == '1') {
                    order_state = "주문중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                }
                else if (order_state_first == '2') {
                    order_state = "입금확인중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                }
                else if (order_state_first == '3') {
                    order_state = "접수중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                } else if (order_state_first == '4') {
                    order_state = "조판중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                } else if (order_state_first == '6') {
                    order_state = "출력중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                } else if (order_state_first == '7') {
                    order_state = "인쇄중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                } else if (order_state_first == '8') {
                    order_state = "후공정중";
                    $("#alert_message").show();
                    $("#btn_stockin").show();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                } else if (order_state == '910') {
                    order_state = "입고대기";
                    $("#alert_message").hide();
                    $("#btn_stockin").hide();
                    alertMessage = "현재 " + order_state + "인 상품입니다.";
                } else if (order_state == '950') {
                    order_state = "입고완료";
                    $("#alert_message").hide();
                    $("#btn_stockin").hide();
                } else if (order_state == '960') {
                    order_state = "출고대기";
                    $("#alert_message").hide();
                    $("#btn_stockin").hide();
                } else if (order_state == '010') {
                    order_state = "배송대기";
                    $("#alert_message").hide();
                    $("#btn_stockin").hide();
                } else if (order_state == '011') {
                    order_state = "배송완료";
                    $("#alert_message").hide();
                    $("#btn_stockin").hide();
                } else {
                    $("#alert_message").show();
                }

                $("#alert_message").html(alertMessage);
                $("#alert_message").hide();
                var dlvr_way = a_obj.dlvr_way;

                if (dlvr_way == "01") {
                    dlvr_way = "택배";
                } else if (dlvr_way == "02") {
                    dlvr_way = "직배";
                } else if (dlvr_way == "03") {
                    dlvr_way = "화물";
                } else if (dlvr_way == "04") {
                    dlvr_way = "퀵(오토바이)";
                } else if (dlvr_way == "05") {
                    dlvr_way = "퀵(지하철)";
                }

                if(a_obj.after == null) {
                    a_obj.after = "";
                }

                if (a_obj.order_num != null) {
                    $("#order_num_"+ i).text(a_obj.order_num);
                    $("#title_"+ i).text(a_obj.title);
                    $("#dlvr_addr_"+ i).text(a_obj.addr);
                    $("#order_detail_"+ i).text(a_obj.order_detail);//디테일
                    $("#amt_"+ i).text(a_obj.amt + "(" + a_obj.count + ")");
                    $("#dlvr_way_"+ i).text(dlvr_way);
                    $("#order_state_"+ i).text(order_state);
                    $("#after_"+ i).text(a_obj.after);
                    $("#option_"+ i).text(a_obj.option);
                    $("#is_bun_"+ i).text(is_bun);

                    // 강제입고
                    if(parseInt(a_obj.order_state) < 3120) {
                        force = true;
                        $("#stock_force_" + i).attr({"class" : "btn_dw_force btn",
                            "disabled" : false,
                            "onclick" : "btn_stock_force('" + a_obj.order_detail_num + "')"});

                        $("#stock_cancel_" + i).attr({"class" : "btn_dw_nothing btn" ,
                            "disabled" : true});
                    }
                    //
                    else if(parseInt(a_obj.order_state) == 3120) {
                        $("#stock_force_" + i).attr({"class" : "btn_dw_nothing btn",
                            "disabled":true});

                        $("#stock_cancel_" + i).attr({"class" : "btn_dw_nothing btn" ,
                            "disabled" : true});
                    } else
                    {
                        $("#stock_force_" + i).attr({"class" : "btn_dw_nothing btn",
                            "disabled":true});

                        $("#stock_cancel_" + i).attr({"class" : "btn_dw_cancel btn" ,
                            "disabled" : false,
                            "onclick" : "btn_stock_cancel('" + a_obj.order_detail_num + "')"});
                    }
                } else {
                    alert("출고대기중인 상품이 아닙니다.");
                    $("#order_no").select();
                }

                if(data.order_detail_num == a_obj.order_detail_num) {
                    $("#recorded_barcode").attr("state", a_obj.order_state);
                    $("#product_form_"+ i).find(".table-body").addClass("searched");
                    if(force == true) {
                        msg_alert = "해당 상품 구성 중 생산 완료처리가 되지 않은 상품이 있습니다.\n"
                            + "주문내역과 상이한지 확인 후 진행하시기 바랍니다.";
                        tempAlert();
                        force = false;
                    }
                } else {
                    $("#product_form_"+ i).find(".table-body").removeClass("searched");
                }

                if(i != length) {
                    copyToTable(i + 1);
                }
            }

            if(change == "Y") {
                $("#recorded_barcode").val("");
            }
            $("#order_no").select();
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};


//강제입고처리
var processStock = {
    "exec"       : function() {
        var url = "/ajax/produce/process_result/load_dlvr_stockin_process.php";
        var blank = "<tr><td colspan=\"20\">검색 된 내용이 없습니다.</td></tr>";
        var data = {
            "order_detail_num" : $("#order_detail_num").val()
        };
        var callback = function(result) {
            if(result == '1') {
                cndSearch.exec('Y');
            } else {
                alert('강제입고 실패');
            }
        };

        showMask();
        ajaxCall(url, "html", data, callback);
    }
};

//검색
var stockButton = function() {
    processStock.exec();
}


//검색
var findButton = function(change) {
    order_no = $("#order_no").val();
    if(order_no == $("#recorded_barcode").val() && Number($("#recorded_barcode").attr("state")) <= Number("3120")) {
        change = "Y";
    } else {
        change = "N";
    }
    $("#recorded_barcode").val(order_no);
    cndSearch.exec(change);
}


var printInvoice = function() {
    order_num = $("#order_num_1").html();

    if(order_num == "") {
        alert("검색된 상품이 없습니다.");
    }

    var url = "/ajax/produce/process_mng/invoice_print_again.php";
    var data = {
        "order_num"       : order_num
    };
    var callback = function(result) {
        if(result == "1") {
            alert("송장 재출력 완료");
        } else {
            alert("송장 재출력 실패");
        }
    };

    showMask();
    ajaxCall(url, "html", data, callback);
};

/**
 *
 *  Base64 encode / decode
 *  http://www.webtoolkit.info/
 *
 **/

var Base64 = {

    // private property
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
                this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}


function copyToTable(idx) {
    var $clone = $("#product_form_1").clone();
    var deliverySection = $("#section_product");

    $clone.attr({"id" : "product_form_" + idx});
    $clone.find("#order_num_1").attr({"id" : "order_num_" + idx});
    $clone.find("#title_1").attr({"id" : "title_" + idx});
    $clone.find("#amt_1").attr({"id" : "amt_" + idx});
    $clone.find("#dlvr_way_1").attr({"id" : "dlvr_way_" + idx});
    $clone.find("#order_state_1").attr({"id" : "order_state_" + idx});
    $clone.find("#is_bun_1").attr({"id" : "is_bun_" + idx});
    $clone.find("#after_1").attr({"id" : "after_" + idx});
    $clone.find("#bun_product_1").attr({"id" : "bun_product_" + idx});
    $clone.find("#option_1").attr({"id" : "option_" + idx});
    $clone.find("#dlvr_addr_1").attr({"id" : "dlvr_addr_" + idx});
    $clone.find("#stock_force_1").attr({"id" : "stock_force_" + idx});
    $clone.find("#stock_cancel_1").attr({"id" : "stock_cancel_" + idx});

    deliverySection.append($clone);
}

function resetToTable() {
    $('.product_form').each(function (i) {
        if (i == 0) {
            $("#order_num_1").text("");
            $("#title_1").text("");
            $("#amt_1").text("");
            $("#dlvr_way_1").text("");
            $("#order_state_1").text("");
            $("#is_bun_1").text("");
            $("#after_1").text("");
            $("#bun_product_1").text("");
            $("#option_1").text("");
            $("#dlvr_addr_1").text("");
            $("#product_form_1").find(".table-body").removeClass("searched");
        } else {
            $(this).remove();
        }
    });
}


function btn_stock_force(order_num) {
    $("#order_num").val(order_num);
    findButton("Y");
    /*
     var url = "/ajax/produce/process_mng/delivery_waitin_stock_force.php";
     var data = {
     "order_detail_num"       : order_num
     };
     var callback = function(result) {
     //var rs = result.split("♪");
     if(result == "1") {
     findButton("N");
     $("#recorded_barcode").val("");
     } else {
     alert("강제입고 실패");
     }
     };

     ajaxCall(url, "html", data, callback);
     */
}


function btn_stock_cancel(order_num) {

    if(!confirm("해당 상품 구성의 입고를 취소 하시겠습니까?"))
    {
        return;
    }

    var url = "/ajax/produce/process_mng/delivery_waitin_stock_cancel.php";
    var data = {
        "order_detail_num"       : order_num
    };
    var callback = function(result) {
        if(result == "1") {
            findButton("N");
            $("#recorded_barcode").val("");
        } else {
            alert("입고취소 실패");
        }
    };

    ajaxCall(url, "html", data, callback);
}


function tempAlert() {
    alertOpened = true;
    alertShowMask();
}

function alertShowMask() {
    showBgMask();
    var html = "<div class='alert alert-danger' role='alert' style='margin-bottom:1px;'>";
    html += "<button type='button' class='close' data-dismiss='alert' aria-label='Close' onclick='alertHideMask()'><span aria-hidden='true'>x</span></button>";
    html += "해당 상품 구성 중 생산 완료처리가 되지 않은 상품이 있습니다.\n 주문내역과 상이한지 확인 후 진행하시기 바랍니다.</div>";
    openRegiPopup(html, 500);
}

function alertHideMask() {
    alertOpened = false;
    hideBgMask();
    $("#regi_popup").html("");
    $("#regi_popup").hide();
    $("#order_no").focus();
    $("#order_no").select();
}
