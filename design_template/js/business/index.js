//페이징
// $(document).ready(function() {
//     $('#tableData, #tableData2, #tableData3, #tableData4').paging({limit: 5});
// });
//
//
// var _gaq = _gaq || [];
// _gaq.push(['_setAccount', 'UA-36251023-1']);
// _gaq.push(['_setDomainName', 'jqueryscript.net']);
// _gaq.push(['_trackPageview']);

//달력
$(document).ready(function() {
    $('.datepicker_input').datepicker({
        format: "yyyy/mm/dd",
        autoclose: true
    });
});

//테이블열기
function show_hide_row(row) {
    $("#" + row).toggle();
 $(".hidden_row").show();
}
function show_hide_row(row) {
    $("#" + row).toggle();
 $(".hidden_ground").hide();
}
function show_hide_row_1(none) {
    $("#" + none).toggle();
}

//라디오버튼 해제
var checkNum;
function check(num){
var obj = $('input:radio[name=""]');
if(checkNum==num){
obj.eq(num).attr('checked',false);
checkNum = null;
}else{
checkNum = num;
}
}
