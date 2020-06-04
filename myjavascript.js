//Displays checkboxes and save choices
function toggleDownload(){
  var x = document.getElementsByClassName("hideDownload");
  for(i=0;i<x.length;i++){
	   if($(x[i]).hasClass("hideDownload")){
          $(x[i]).removeClass("hideDownload");
          $(x[i]).addClass("showDownload");
        }
    }
  var b = document.getElementsByClassName("myBox");
  for(i=0;i<b.length;i++){
    b[i].style.display="block";
  }
  hideStartSave();
  var s = document.getElementsByClassName("selectAll");
  for(i=0;i<s.length;i++){
    s[i].style.display="inline-block";
  }
}

function showStartSave(){
  var d = document.getElementById("startsave");
  d.style.display="block";
}
function hideStartSave(){
  var d = document.getElementById("startsave");
  d.style.display="none";
}

function toggleChecks(source){
  var checkboxes = document.getElementsByClassName("myBox");
  for(var i=0;i<checkboxes.length;i++){
    checkboxes[i].checked=source.checked;
  }
}

//Readers for csv, json, and xml result files
function csvReader(csv){
  var res="<ul id='resultsList'>";
  var data = [];
  var jObj = csv.split(/\r?\n|\r/);
  for(var i=0; i<jObj.length;i++){
	data = jObj[i].split(',');
	res += "<li class='result'><input type='checkbox' class='myBox'><p class='title'>" + data[0] +
    "</p><a href='"+data[1]+"'><p class='url'>" + data[1] +
    "</p></a><p class='description'>" + data[2] +
    "</p></li>";
  }
  res += "</ul>";
  document.getElementById("resultsDisplay").innerHTML = res;
}
function jsonReader(json){
  var myArr = JSON.parse(json);
  var res = "<ul id='resultsList'>";
  for(var i=0; i<myArr.Result.length;i++){
	res += "<li class='result'><input type='checkbox' class='myBox'><p class='title'>" + myArr.Result[i].title +
    "</p><a href='"+myArr.Result[i].url+"'><p class='url'>" + myArr.Result[i].url +
    "</p></a><p class='description'>" + myArr.Result[i].description +
    "</p></li>";
  }
  res += "</ul>";
  document.getElementById("resultsDisplay").innerHTML = res;
}
function xmlReader(xml) {
  var i;
  var parser = new DOMParser();
  var xmlDoc = parser.parseFromString(xml, "text/xml");
  var res="<ul id='resultsList'>";
  var x = xmlDoc.getElementsByTagName("result");
  for (i = 0; i <x.length; i++) {
    res += "<li class='result'><input type='checkbox' class='myBox'><p class='title'>" +
    x[i].getElementsByTagName("title")[0].childNodes[0].nodeValue +
    "</p><a href='"+x[i].getElementsByTagName("url")[0].childNodes[0].nodeValue+"'><p class='url'>" + x[i].getElementsByTagName("url")[0].childNodes[0].nodeValue +
    "</p></a><p class='description'>" + x[i].getElementsByTagName("description")[0].childNodes[0].nodeValue +
    "</p></li>";
  }
  res += "</ul>";
  document.getElementById("resultsDisplay").innerHTML = res;
}
//Begins saving results
function saveResults(){
    var dtype = $("#dtype option:selected").val();
    switch(dtype){
      case 'csv': csvWriter();
      break;
      case 'xml': xmlWriter();
      break;
      case 'json': jsonWriter();
      break;
      default: alert('Unavailable file type');
    }
}
//Parses data on page to write to download file for given type
function csvWriter(){
  var data = "";
  var boxes = document.getElementsByClassName("myBox");
	var checks =[];
	for(var i=0;i<boxes.length;i++){
		if(boxes[i].checked){
			checks.push(boxes[i]);
		}
	}
	for(var i=0;i<checks.length;i++){
		var nodes = checks[i].parentNode.parentNode.children;
		data+=nodes[1].innerHTML+","+nodes[3].innerHTML+","+nodes[4].innerText;
		if(i<checks.length-1){
			data+="\n";
		}
	}
  createDownload(data, "text/csv");
}
function xmlWriter(){
  var data = "<?xml version='1.0' encoding='UTF-8'?><results>";
  var boxes = document.getElementsByClassName("myBox");
	var checks =[];
	for(var i=0;i<boxes.length;i++){
		if(boxes[i].checked){
			checks.push(boxes[i]);
		}
	}
  for(var i=0;i<checks.length;i++){
    var nodes = checks[i].parentNode.parentNode.children;
    data+="<result><title>"+nodes[1].innerHTML+"</title><url>"+nodes[3].innerHTML+"</url><description>"+nodes[4].innerText+"</description></result>";
  }
  data+="</results>";
  createDownload(data, "text/xml");
}
function jsonWriter(){
  var data = '{"Result" : [';
  var boxes = document.getElementsByClassName("myBox");
  
	var checks =[];
	for(var j=0;i<boxes.length;i++){
		if(boxes[i].checked){
			checks.push(boxes[i]);
		}
	}
  for(var i=0;i<checks.length;i++){
    var nodes = checks[i].parentNode.parentNode.children;
    data+='{"title": "'+nodes[1].innerHTML+'","url":"'+nodes[3].innerHTML+'","description":"'+nodes[4].innerText+'"}';
    if(i<checks.length-1){
      data+=",";
    }
  }
  data+="]}";
  createDownload(data, "text/json");
}

//Creates and opens download file for chosen results
function createDownload(data, filetype){
  var today = new Date();
  var currentDate = today.toUTCString();
  var filename = "results"+currentDate;
  switch(filetype){
    case "text/csv": filename+=".csv";
    break;
    case "text/xml": filename+=".xml";
    break;
    case "text/json": filename+=".json";
    break;
  }
  var file = new Blob([data], {type: filetype});
  var downloadLink=document.createElement("a");
  downloadLink.download=filename;
  downloadLink.href=window.URL.createObjectURL(file);
  downloadLink.style.display="none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
}
function dbjsonReader(json){
  var myArr = json;
  var res = '<h3>Showing ' + myArr.Result.length + ' results for "'+$("#search_text").val()+'"</h3>';;
	res += '<ul id = "resultsList">';
 for(var i=0; i<myArr.Result.length;i++){
	res += 	"<li><div class='search-items-list'>"+
				"<label class = 'check-search'><input type='checkbox' class='myBox'>" +
				"<span class='checkmark'></span></label>"+
				"<a target = '_blank' href = '" + myArr.Result[i].url +"' class ='item-link'>"+
								myArr.Result[i].title + "</a><br>"+
				"<a target = '_blank' href= '"+myArr.Result[i].url+"' class='sub-link'> "+
								myArr.Result[i].url + "</a>"+
					"<p class='description'>" + myArr.Result[i].description +"</p></div></li>";
  }
 
 res += '</ul>';
  document.getElementById("resultsDisplay").innerHTML = res;
}

function pagination(){
	var maxItems = 10;
	var items = $('ul').find('li');
	var numItems = items.length;
	var numPages = Math.ceil(numItems/maxItems);
	if(numPages==1){
    $('#page-nav').hide();
		return;
	}
	items.hide();
	items.slice(0, maxItems).show();
	var prev = $('#prev');
	var next = $('#next');
	prev.addClass('disabled');

	prev.click(function() {
        var firstVisible = items.index(items.filter(':visible'));

        if (prev.hasClass('disabled')) {
            return false;
        }

        items.hide();
        if (firstVisible - maxItems - 1 > 0) {
            items.filter(':lt(' + firstVisible + '):gt(' + (firstVisible - maxItems - 1) + ')').show();
        } else {
            items.filter(':lt(' + firstVisible + ')').show();
        }

        if (firstVisible - maxItems <= 0) {
            prev.addClass('disabled');
        }

        next.removeClass('disabled');

        return false;
    });

    next.click(function() {
        var firstVisible = items.index(items.filter(':visible'));

        if (next.hasClass('disabled')) {
            return false;
        }

        items.hide();
        items.filter(':lt(' + (firstVisible +2 * maxItems) + '):gt(' + (firstVisible + maxItems - 1) + ')').show();

        if (firstVisible + 2 * maxItems >= items.length) {
            next.addClass('disabled');
        }

        prev.removeClass('disabled');

        return false;
    });
}
