$(document).ready(function() {
    $(".date").datepicker({
        format         : "yyyy-mm-dd",
        autoclose      : true,
        todayBtn       : "linked",
        todayHighlight : true,
        language       : "kr"
    }).datepicker("setDate", '0');
    loadDeparList();
    loadSalesList(1);
});
var page = "1";
var list_num = "30";

//팀 리스트 불러오기
var loadDeparList = function() {

    $.ajax({

        type: "POST",
        data: {
		    "sell_site" : $("#sell_site").val()
        },
        url: "/ajax/calcul_mng/sales_data/load_depar_list.php",
        success: function(result) {
	    
	    	$("#depar_dvs").html(result);
        }, 
        error: getAjaxError
    });
}

//매출 리스트 불러오기
var loadSalesList = function(pg) {

   showMask();

  var formData = new FormData($("#sales_form")[0]);
        formData.append("page", pg);
        formData.append("list_num", list_num);

    $.ajax({
		    
        type: "POST",
        data: formData,
	    processData : false,
	    contentType : false,
        url: "/ajax/calcul_mng/sales_data/load_sales_list.php",
        success: function(result) {
	    var list = result.split('♪♭@');
            if($.trim(list[0]) == "") {

                $("#sales_list").html("<tr><td colspan='6'>" +
                    "검색된 내용이 없습니다.</td></tr>"); 
                $("#member_cnt").html("0"); 
                $("#sales_sum").html("0원"); 
                $("#minus_sum").html("0원"); 
                $("#income_sum").html("0원"); 


	    } else {

                $("#sales_list").html(list[0]);
                $("#sales_page").html(list[1]); 
                $("#member_cnt").html(list[2]); 
                $("#sales_sum").html(list[3]); 
                $("#minus_sum").html(list[4]); 
                $("#income_sum").html(list[5]); 
                $('select[name=list_set]').val(list_num);

	    }
        hideMask();
        }, 
        error: getAjaxError

    });
}

//보여 주는 페이지 갯수 설정
var showPageSetting = function(val) {

    list_num = val;
    loadSalesList(1);
} 

//선택 조건으로 검색(페이징 클릭)
var searchResult = function(pg) {

    page = pg;
    loadSalesList(page);
}


