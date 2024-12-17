function ConvertTime(ti)
{
	var hh = parseInt(ti / 3600);
	var mm = parseInt((ti / 60) % 60);
	var ss = parseInt(ti % 60);
	var ret = "";
	
	if(hh>=10)
		ret += hh;
	else
		ret += "0" + hh;
		
	ret += ":";
			
	if(mm>=10)
		ret += mm;
	else
		ret += "0" + mm;
		
	ret += ":";
	
	if(ss>=10)
		ret += ss;
	else
		ret += "0" + ss;
		
	return ret;				
	
}

function getCurrentTimeString()
{
   var curr = new Date();

   var result = curr.getFullYear()+"/";
   if((curr.getMonth()+1)>9)
     result += (curr.getMonth()+1);
   else
     result += "0"+(curr.getMonth()+1);
   result += "/";

	// Peter_Lee, getDay -> getDate
   if(curr.getDate()>9)
     result += curr.getDate();
   else
     result += "0"+curr.getDate();
   result += " ";
   if(curr.getHours()>9)
     result += curr.getHours();
   else
     result += "0"+curr.getHours();
   result += ":";
   if(curr.getMinutes()>9)
     result += curr.getMinutes();
   else
     result += "0"+curr.getMinutes();
   result += ":";
   if(curr.getSeconds()>9)
     result += curr.getSeconds();
   else
     result += "0"+curr.getSeconds();
   return result;
}

function Get_Cookie(name) {
    var start = document.cookie.indexOf(name+"=");
    var len = start+name.length+1;
    if ((!start) && (name != document.cookie.substring(0,name.length))) return null;
    if (start == -1) return null;
    var end = document.cookie.indexOf(";",len);
    if (end == -1) end = document.cookie.length;
    return unescape(document.cookie.substring(len,end));
}

function Set_Cookie(name,value,expires,path,domain,secure) {
    if(expires==null)
    {
       var today = new Date();
       var days = 365;
       var zero_date = new Date(0,0,0);
       today.setTime(today.getTime() - zero_date.getTime());

       var todays_date = new Date(today.getFullYear(),today.getMonth(),today.getDate(),0,0,0);
       expires = new Date(todays_date.getTime() + (days * 86400000));       
    }
    
    document.cookie = name + "=" +escape(value) +
        ( (expires) ? ";expires=" + expires.toGMTString() : "") +
        ( (path) ? ";path=" + path : "") + 
        ( (domain) ? ";domain=" + domain : "") +
        ( (secure) ? ";secure" : "");    
}

function Delete_Cookie(name,path,domain) {
    if (Get_Cookie(name)) document.cookie = name + "=" +
        ( (path) ? ";path=" + path : "") +
        ( (domain) ? ";domain=" + domain : "") +
        ";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}

function isCookieEnabled() {
   if (document.all) return navigator.cookieEnabled;
   Set_Cookie('testcookie',today.getTime());
   var tc = Get_Cookie('testcookie');
   Delete_Cookie('testcookie');
   return (tc == today.getTime());
}

function encodeURL(str)
{
  var re;
  var eStr;
  
  re = new RegExp("%", "ig");
  eStr = str.replace(re, "%25");

  re = new RegExp(" ", "ig");
  eStr = eStr.replace(re, "%20");

  re = new RegExp("#", "ig");
  eStr = eStr.replace(re, "%23");

  re = new RegExp("&", "ig");
  eStr = eStr.replace(re, "%26");

  re = new RegExp("\\+", "ig");
  eStr = eStr.replace(re, "%2B");

  re = new RegExp("/", "ig");
  eStr = eStr.replace(re, "%2F");

  re = new RegExp("\\?", "ig");
  eStr = eStr.replace(re, "%3F");
  
  return eStr;
}

function GetParameter(paraname)
{
	var url = ""+window.location;
	var idx = url.indexOf("?");
	if(idx>0)
		url = url.substring(idx+1);
	
	var arr = splitString(url,"&");	
	for(var i=0; arr!=null && i<arr.length; i++)
	{
		if(arr[i]!=null && arr[i].indexOf(paraname+"=")==0){
			idx = arr[i].indexOf("=");
			return arr[i].substring(idx+1);
		}	
	}
	return "";
}

function GetByteCount(str)
{
 	str = encodeURIComponent(str);
	return str.replace(/%[A-F\d]{2}/g, 'U').length;	
}

function GetElement(eid)
{
	return document.getElementById(eid);
}

function IsTimeField(field)
{
	var idx = field.lastIndexOf("_to");
	var idx1 = field.lastIndexOf("_mx");
	var idx2 = field.lastIndexOf("_av");
	
	if(idx==(field.length-3) || idx1==(field.length-3) || idx2==(field.length-3))
		return true;
	
	return false;
}

function splitString(str, seperator)
{
   var str1 = str;
   var idx;
   var count = 0;
   
   while(str1!=null && str1.length>0)
   {
      idx = str1.indexOf(seperator);
      if(idx>=0)
      {
         count++;
         str1 = str1.substring(idx+seperator.length); 
         if(str1.length<1)
           count++;
      }
      else
      {
         count++; 
         str1 = "";
      }
   }
   
   var result = Array(count);
   for(i=0; i<count; i++)
   {
      idx = str.indexOf(seperator);
      if(idx>=0)
      {
         result[i] = str.substring(0,idx);
         str = str.substring(idx+seperator.length);
      }
      else
      {
        result[i] = str;
        str = ""; 
      }
   }
   
   return result;
}

function replaceString1(str,repstr,repwith)
{
  var idx = 0-repstr.length;
  var idx1 = 0;
  var result = "";

  if(str != null)
  {
      while(true)
      {
         idx = str.indexOf(repstr,idx+repstr.length);

         if(idx>=0)
         {
            result += str.substring(idx1,idx);
            result += repwith;
            idx1 = idx + repstr.length;
         }
         else
         {
            if(idx1<str.length)
              result += str.substring(idx1);

            break;
         }
      }
  }
  return result;	
}