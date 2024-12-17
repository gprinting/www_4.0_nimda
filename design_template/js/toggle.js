function display_toggle(id)
{
  var obj = document.getElementById(id);
  var body = document.getElementById("body");
  obj.style.display = (obj.style.display == 'none') ? 'block' : 'none';
  body.display = "none";
  body.display = "inline-block";
}
function overstyle_onset(obj, parentid)
{
	var tag = obj.tagName;
	var items = document.getElementById(parentid).getElementsByTagName(tag);


	for(var i = 0 ; i < items.length; i++)
	{
		var area = document.getElementById(items[i].id+'_area');
		if(items[i] == obj)
		{
			if(items[i].className.split('_')[items[i].className.split('_').length-1] == 'on')	return;
			items[i].className += '_on';
			if(area) area.style.display = 'block';
		}
		else
		{
			if(items[i].className.split('_')[items[i].className.split('_').length-1] != 'on')	continue;
			items[i].className = str_replace('_on', '', items[i].className);
			if(area) area.style.display = 'none';
		}
	}
}
