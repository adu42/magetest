var scripts = document.getElementsByTagName("script"),
src = scripts[scripts.length-1].src;
x = src.split("/_js/");
cc_domainname = x[0]+"/";

function rateover(id,value,size,outof,cat_id,separator)
{
//exit;
if(size == "small"){sizevar = 1;}
else if(size == "medium"){sizevar = 2;}
else if(size == "large"){sizevar = 3;}
s2 = value*2;
str = "hello";
k=0;
for(i=1;i<=10;i++)
{
j=i/2;
starid = "rate"+id+""+j;
// console.log(starid);
k=k+0.5;
docstar = document.getElementById(starid);
// for 5 stars
if(cat_id == "5"){
if(k <= value){
if(i%2 == 1){
  docstar.style.backgroundPosition="-8px -"+(36*sizevar)+"px";}
else {docstar.style.backgroundPosition="-"+(9*sizevar)+"px -"+(36*sizevar)+"px";}
} else {
if(i%2 == 1){docstar.style.backgroundPosition="-8px -1px";}
else{docstar.style.backgroundPosition="-"+(9*sizevar)+"px -1px";}
}
}

// for 10 stars
if(cat_id == "10"){
if(k <= value){
if(i%2 == 1){docstar.style.backgroundPosition="-8px -"+(36*sizevar)+"px";}
else {docstar.style.backgroundPosition="-"+(0*sizevar)+"px -"+(36*sizevar)+"px";}
} else {
if(i%2 == 1){docstar.style.backgroundPosition="-8px -1px";}
else{docstar.style.backgroundPosition="-"+(0*sizevar)+"px -1px";}
}
}

}
if(outof == 5){ value = value;}
else if(outof == 10){ value = value*2;}
else if(outof == 100){ value = value*20;}
//document.getElementById("rate_numbers_"+id).innerHTML = value+""+separator+""+outof;
}

function rateout(id,value,blograte,size,outof,cat_id)
{
//exit;
if(size == "small"){sizevar = 1;}
else if(size == "medium"){sizevar = 2;}
else if(size == "large"){sizevar = 3;}

s2 = blograte*2;
str = "hello"+blograte;
k=0;
for(i=1;i<=10;i++)
{
j=i/2;
starid = "rate"+id+""+j;
k=k+0.5;
docstar = document.getElementById(starid);
// out of 5 stars
if(cat_id == "5"){
if(k <= blograte){
if(i%2 == 1){docstar.style.backgroundPosition = "-8px -"+(18*sizevar)+"px";} 
else{docstar.style.backgroundPosition = "-"+(9*sizevar)+"px -"+(18*sizevar)+"px";}
}
else {
if(i%2 == 1){docstar.style.backgroundPosition = "-8px -1px";} 
else{docstar.style.backgroundPosition = "-"+(9*sizevar)+"px -1px";}
}
}

// out of 10 stars
if(cat_id == "10"){
if(k <= blograte){
if(i%2 == 1){docstar.style.backgroundPosition = "-8px -"+(18*sizevar)+"px";}
else{docstar.style.backgroundPosition = "-"+(0*sizevar)+"px -"+(18*sizevar)+"px";}
}
else {
if(i%2 == 1){docstar.style.backgroundPosition = "-8px -1px";} 
else{docstar.style.backgroundPosition = "-"+(0*sizevar)+"px -1px";}
}
}

}
//document.getElementById("rate_numbers_"+id).innerHTML = "";
}

function ratethis(id,value,type,size,color,outof,rateonhover,score,totalcount,shape,cat_id,ratingon,lang_rating,domainname)
{
//alert(type+size+color+outof+rateonhover+score+totalcount);alert(id+""+value);
var linkbox = 'rateboxtable_'+ id;
var countId = 'countrating' ;
var countbox = '._countRating';
var totalbox = '._countTotal';


if(size == "small"){a = 18; b=2; c=21+2;} 
else if(size == "medium"){a = 36; b=3; c=25+3;} 
else if(size == "large"){a = 54; b=5; c=29+5;}
h1 = a; h2 = c; w1 = a*cat_id+(b * (cat_id - 1));

var xmlhttp;
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {

    // show on page loading
  if (xmlhttp.readyState==1){
  document.getElementById(linkbox).innerHTML = "Loading...";
  }
  
  // show on successful page return
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      // icon after adding review
      document.getElementById(linkbox).innerHTML=xmlhttp.responseText;

      // change rating counts
      var countRating = document.getElementById(countId).value;
      jQuery(countbox).html(countRating);
    
      // change total votes
      var  countTotal = jQuery(totalbox).html();

      countTotal = Number(countTotal) + 1 ;
      jQuery(totalbox).html(countTotal);
 
    }
  }

// send page request
xmlhttp.open("GET",domainname+"?id="+id+"&value="+value+"&type="+type+"&size="+size+"&color="+color+"&outof="+outof+"&rateonhover="+rateonhover+"&score="+score+"&totalcount="+totalcount+"&shape="+shape+"&cat_id="+cat_id+"&ratingon="+ratingon,true);

xmlhttp.send();

}

function ext_ratethis(id,value,type,size,color,outof,rateonhover,score,totalcount,shape,cat_id,ratingon,lang_rating,domainname)
{
linkbox = 'rateboxtable_'+id;
if(size == "small"){a = 18; b=2; c=21+2;}
else if(size == "medium"){a = 36; b=3; c=25+3;} 
else if(size == "large"){a = 54; b=5; c=29+5;}
h1 = a; h2 = c; w1 = a*cat_id+(b * (cat_id - 1));
//alert("test");
var request = extrate("get", ""+domainname+"cc_rating_ratethis.php?id="+id+"&value="+value+"&type="+type+"&size="+size+"&color="+color+"&outof="+outof+"&rateonhover="+rateonhover+"&score="+score+"&totalcount="+totalcount+"&shape="+shape+"&cat_id="+cat_id+"&ratingon="+ratingon);
if (request){
    request.onload = function(){
    document.getElementById(linkbox).innerHTML = request.responseText;
			};
    request.send();
}
}


function extrate(method, url)
{
    var xhr = new XMLHttpRequest();
    if ("withCredentials" in xhr){
        xhr.open(method, url, true);
    } else if (typeof XDomainRequest != "undefined"){
        xhr = new XDomainRequest();
        xhr.open(method, url);
    } else {
        xhr = null;
    }
    return xhr;
}



// edit url to var
function rating_module(id)
{
//alert(cc_domainname);
var request = extrate("get", ""+cc_domainname+"cc_rating_external.php?id="+id);
if (request){
    request.onload = function(){	//do something with request.responseText
    document.getElementById("extrate_"+id).innerHTML = request.responseText;
gapi.plusone.go();
FB.XFBML.parse();
	};
    request.send();
}
}


function ext_rateover(id,value,size,outof,cat_id,separator)
{
rateover(id,value,size,outof,cat_id,separator);
}

function ext_rateout(id,value,blograte,size,outof,cat_id)
{
rateout(id,value,blograte,size,outof,cat_id);
}
//FB.XFBML.parse(); 