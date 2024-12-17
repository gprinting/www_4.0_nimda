/**
 * Created by USER on 2017-12-06.
 */
$(document).ready(function() {
    $(".datepicker_input").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", "0").attr('readonly', 'readonly');

    //searchProcess(30,1);
});

var showPage = 30;

var searchProcess = function(showPage, page) {

    if($("#state").val() == "3120") {
        $("#btn_complete").hide();
    } else {
        $("#btn_complete").show();
    }

    var url = "/ajax/manufacture/output_list/load_imposition_list.php";
    var blank = "<tr><td colspan=\"9\">검색 된 내용이 없습니다.</td></tr>";
    var data = {
        "category" : $("#category").val(),
        "typset_num"  : $("#typset_num").val(),
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

var click_impose_num = function(typset_num) {
    window.open("/order/order_common_mng.html?search_dvs=typset_num&search_keyword=" + typset_num, '_blank')
}

var make_label = function(sheet_typset_seqno) {
    var url = "/ajax/manufacture/output_list/load_imposition_by_count.php";
    var data = {
        "sheet_typset_seqno" : sheet_typset_seqno
    };
    var callback = function(result) {
        downloadURI('/attach/gp/by_label_file/aligned.pdf',"낱딱판.pdf")
        ///attach/gp/by_label_file/aligned.pdf

    };

    showMask();
    ajaxCall(url, "html", data, callback);
}

var printSelectedLabel = function() {
    var selectedValue = "";
    $("input[name=chk]:checked").each(function() {
        selectedValue += "|"+ $(this).val();
    });

    if(selectedValue.length > 1) {
        selectedValue = selectedValue.substring(1);
    }

    make_label(selectedValue);
}

function downloadURI(uri, name) {
    var link = document.createElement("a");
    link.download = name;
    link.href = uri;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    delete link;
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

var downloadURL = function(url) {
    window.open(url, 'Download');
}