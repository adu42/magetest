	window.onload=function(){
		var divs=document.getElementById("additional");
		var h2s=document.getElementsByTagName("h2")[0];
		var uls=document.getElementsByTagName("ul")[0];
		h2s.onclick=function(){
			if(uls.style.display=="block"){
				uls.style.display="none";
			}else{
				uls.style.display="block";
			}
		}
	}