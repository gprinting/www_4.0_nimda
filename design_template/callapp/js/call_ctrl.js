var mIsDebug = true;

$(document).ready(function(){

    //서버연결 버튼 클릭
    $("#btnConnect").click(function(){
	    var svr = $("#txtWsURL").val();
	    var port = $("#txtWsPort").val();

        ConnectServer(svr, port);
    });

    $("#BTN_REG").click(function(){
        var group = $("#txtGroup").val();
        var phoneIP = $("#txtPhoneIP").val();
        var phoneID = $("#txtPhoneID").val();
        var phonePWD = $("#txtPhonePWD").val();
        var phoneExt = $("#txtPhoneExt").val();

        //추가된 사항
        var accountID = $("#txtUserID").val();
        var accountPWD = $("#txtUserPWD").val();

        if(accountPWD==undefined || accountPWD==""){
            accountPWD = "ob" + phoneID + "0000@!";
        }
        if(accountID==undefined || accountID==""){
            accountPWD = phoneID;
        }

        SessionRegister(group,phoneIP,phoneID,phonePWD,phoneExt,accountPWD,accountID);
    });

    $("#BTN_UNREG").click(function(){
        g_callInfo.destroy();
    });



    $("#btnSetReady").click(function(){
        AgentStateChange(3);
    });
    $("#btnSetShift").click(function(){
        AgentStateChange(2);
    });
    $("#btnSetRest").click(function(){
        AgentStateChange(100);
    });


    $("#BTN_MK").click(function(){
        //전화걸기
        var telno = $("#TXDNIS").val();
        g_callInfo.makeCall(telno);
    });

    $("#BTN_AK").click(function(){
        //전화받기
        g_callInfo.setCallState(0);
    });

    $("#BTN_HG").click(function(){
        //전화거절
        g_callInfo.setCallState(2);
    });

    $("#BTN_HD").click(function(){
        //HOLD
        g_callInfo.setCallState(1);
    });

    $("#BTN_MT").click(function(){
        //뮤트
        g_callInfo.setCallState(4);
    });

    $("#btn2ndCall").click(function(){
        //2nd콜 생성
        var telno = $("#TXTRANS").val();
        g_callInfo.makeATrans(telno);
    });

    $("#btnTrans").click(function(){
        //호전환
        g_callInfo.merge2ndCall(1);
    });

    $("#btnRetrieve").click(function(){
        //전환 취소
        g_callInfo.merge2ndCall(4);
    });



    $('#controlFrame').load(function () {
        var controlFrame = $('#controlFrame');
        var src = controlFrame.prop("Other");
        if (!src) return;
        if (src.length > 0) {
            var i;
            var arrKeyLogic = src.split("|");
            if (DataValidation()) {
                var URL = getURLMakeKeyCommand(arrKeyLogic[0]);
                arrKeyLogic.splice(0, 1);
                var other = "";
                for (i = 0; i < arrKeyLogic.length; i++) {
                    if (i != 0) {
                        other += "|";
                    }
                    other += arrKeyLogic[i];
                }
                controlFrame.prop("Other", other);
                controlFrame.prop("src", URL);
            }
        }
    });


});



function ConnectServer(svr, port){
    g_callInfo.ACDSvr = svr;
    g_callInfo.ACDPort = port;
    g_callInfo.init();
}

function SessionRegister(group,phoneIP,phoneID,phonePWD,phoneExt,accountPWD,accountID){
    g_callInfo.loginInit(group,phoneIP,phoneID,phonePWD,phoneExt,accountPWD,accountID);

}

function MakeCall_Phone(telno) {
	g_callInfo.makeCall(telno);
}

function HangUp_Phone() {
	g_callInfo.setCallState(2);
}

function MuteToggle_Phone() {
	g_callInfo.setCallState(4);
}

function AgentStateChange(value) {
	/*
		2: not ready
	*/
	g_callInfo.setAgentState(value);
}

/* Event */
function EventError(reason){
    DebugMessage("EventError");
    /*
    reason 인자값
    0 - 미 로그인 상태에서 명령을 내린 경우
    1 - 소켓 미접속 상태에서 loginInit 를 날린 경우
     */
}

function EventCallIncome(){
    //g_callInfo.RemoteNumber - 고객 전화 번호
    DebugMessage("EventCallIncome");
    ToggleButton(99);
}

function EventServerConnected(){
    DebugMessage("EventServerConnected");
    //서버연결 완료
/*
    var group = $("#txtGroup").val();
    var phoneIP = $("#txtPhoneIP").val();
    var phoneID = $("#txtPhoneID").val();
    var phonePWD = $("#txtPhonePWD").val();
    var phoneExt = $("#txtPhoneExt").val();

    var accountID = $("#txtUserID").val();
    var accountPWD = $("#txtUserPWD").val();

    SessionRegister(group,phoneIP,phoneID,phonePWD,phoneExt,accountID,accountPWD);
*/

	if (typeof parent.eventServerConnected == 'function') {
		parent.eventServerConnected();
	}

}

function EventServerDisconnected(){
    DebugMessage("EventServerDisconnected");
    $("#BTN_REG").show();
    $("#BTN_UNREG").hide();
    //서버연결 끊김
    if (typeof parent.eventServerDisconnected == 'function') {
		parent.eventServerDisconnected();
	}
}

function EventOtherAgentStatusChange() {
    if (typeof parent.eventOtherAgentStatusChange == 'function') {
        parent.eventOtherAgentStatusChange(g_callInfo.AgentListArr);
    }
}

function EventAgentStatusChange(){
    //g_callInfo.agentState - 상담사 상태



    var state = g_callInfo.agentState;
    ToggleButton(state);
    var item = GetStateName(state);
    $("#SPAN_AGENTSTATE").text(item['stateName']);

    if (typeof parent.eventAgentStatusChange == 'function') {
    	parent.eventAgentStatusChange(item);
    }
}

function EventNotice(data){
    //data["sTime"],data["from"],data["msg"],data["senderID"],data["senderName"]
}

function EventMSG(data){
    //data["sTime"],data["from"],data["msg"]
}

function EventCallMute() {
	if (typeof parent.eventCallMute == 'function') {
    	parent.eventCallMute();
    }
}

function EventCallUnMute() {
	if (typeof parent.eventCallUnMute == 'function') {
    	parent.eventCallUnMute();
    }
}

function EventLoginDuplicated(){
    //alert("중복 로그인으로 인해 접속이 종료 되었습니다.");
    DebugMessage("중복 로그인으로 인해 접속이 종료 되었습니다.");
    $("#BTN_REG").show();
    $("#BTN_UNREG").hide();


    if (typeof parent.eventLoginDuplicated == 'function') {
    	parent.eventLoginDuplicated();
    }
}

function EventLoginFailed(){
    //alert("로그인에 실패하였습니다. 다시 로그인해주세요.");
    DebugMessage("로그인에 실패하였습니다. 다시 로그인해주세요.");
    $("#BTN_REG").show();
    $("#BTN_UNREG").hide();


    if (typeof parent.eventLoginFailed == 'function') {
    	parent.eventLoginFailed();
    }
}
function EventLoginSuccess(){
    //로그인성공
    $("#BTN_REG").hide();
    $("#BTN_UNREG").show();

    if (typeof parent.eventLoginSuccess == 'function') {
    	parent.eventLoginSuccess();
    }
}



function ToggleButton(state){
    console.log('ToggleButton',state);
    /*
    * state code define
    * 2 - 이석
    * 4 - 통화중
    * 5 - 후처리
     */
    switch(parseInt(state,10)){
        case 4:
            $("#BTN_MK").hide();
            $("#BTN_HG").show();
            $("#BTN_AK").hide();
            $("#BTN_MT").show();
            $("#BTN_HD").show();
            $("#DIV_TRANS").show();

            break;
        case 99:
            $("#BTN_MK").hide();
            $("#BTN_HG").show();
            $("#BTN_AK").show();
            $("#BTN_MT").hide();
            $("#BTN_HD").hide();
            $("#DIV_TRANS").hide();
            break;
        default:
            $("#BTN_MK").show();
            $("#BTN_HG").hide();
            $("#BTN_AK").hide();
            $("#BTN_MT").hide();
            $("#BTN_HD").hide();
            $("#DIV_TRANS").hide();
            break;
    }
}

function SocketEvent(json){
    var result=false;
    var method = json["method"];

    switch (method) {
        case "Login":
            //로그인 성공/실패 리턴
            result = json["result"];
            if(result == true){
                //로그인 성공

            }else{
                //로그인 실패
            }
            break;
        default:
            break;
    }

}


var stringConstructor = "test".constructor;
var arrayConstructor = [].constructor;
var objectConstructor = {}.constructor;

function whatIsIt(object) {
    if (object === null) {
        return "null";
    }
    else if (object === undefined) {
        return "undefined";
    }
    else if (object.constructor === stringConstructor) {
        return "String";
    }
    else if (object.constructor === arrayConstructor) {
        return "Array";
    }
    else if (object.constructor === objectConstructor) {
        return "Object";
    }
    else {
        return "don't know";
    }
}


//디버그메세지 출력
function DebugMessage(str) {
    if(mIsDebug==true){
        var type = whatIsIt(str);
        if (console) {
            console.log(str);
        }
        if(type == "Object"){
            str = JSON.stringify(str);
        }
        str += "\n";

        var obj = $("#TALOG");
        obj.val(obj.val() + str);
        obj.scrollTop(999999999);
    }
}


//상태코드별 이름
function GetStateName(state){
    var item = {stateName:"",stateColor:"",stateCode:state};
    switch(state){
        case 3:	//ready
            item['stateName'] = "대기중";
            item['stateColor'] = "#22b14c";
            break;
        case 2:	//not ready
            item['stateName'] = "이석중";
            item['stateColor'] = "#99d9ea";
            break;
        case 300:	//PDS Ready
            item['stateName'] = "PDS 대기";
            item['stateColor'] = "#22b14c";
            break;
        case 200:	//aux
            item['stateName'] = "기타이석";
            item['stateColor'] = "#c3c3c3";
            break;
        case 201:	//aux
            item['stateName'] = "교육중";
            item['stateColor'] = "#c3c3c3";
            break;
        case 202:	//aux
            item['stateName'] = "아웃바운드";
            item['stateColor'] = "#c3c3c3";
            break;
        case 203:	//aux
            item['stateName'] = "회의";
            item['stateColor'] = "#c3c3c3";
            break;
        case 204:	//aux
            item['stateName'] = "타업무";
            item['stateColor'] = "#c3c3c3";
            break;
        case 100:	//rest
            item['stateName'] = "휴식중";
            item['stateColor'] = "#c3c3c3";
            break;
        case 101:	//Meal
            item['stateName'] = "식사중";
            item['stateColor'] = "#c3c3c3";
            break;
        case 4:		//busy
            item['stateName'] = "통화중";
            item['stateColor'] = "#ed1c24";
            break;
        case 5:		//ACW
            item['stateName'] = "후처리중";
            item['stateColor'] = "#fff200";
            break;
        case 1:		//Logout
            item['stateName'] = "비로그인";
            item['stateColor'] = "#000000";
            break;
        default:
            item['stateName'] = "로그인";
            item['stateColor'] = "#99d9ea";
            break;
    }
    return item;
}
