(function($) {
	$(function() {
		if ($(".header-top .click-search").hasClass("current")) {
			$(".main-header .search-block").show()
		}
		$(".header-top .click-search").click(function() {
			if (!$(this).hasClass("current")) {
				$(this).addClass("current");
				$(".main-header .search-block").show()
			} else {
				$(this).removeClass("current");
				$(".main-header .search-block").hide()
			}
		});
		
		var myScroll = new IScroll('#main-nav',{ mouseWheel: true,scrollbars:true });
		var winheight=$(window).height();
		var menuheight=0;
		$(".header-top .click-nav").click(function() {
			winheight=$(window).height();
			menuheight=winheight-$(".header-top").height()-$(".close_main_div").height();
			$("#main-nav").css({"height":menuheight});
			if (!$(this).parents("li").hasClass("current")) {
				$(this).parents("li").addClass("current");
				$(".main-nav").animate({
					left: 0
				}, 300);
				$(".header-top").addClass("menu_relative");
				document.getElementById("wrapper").addEventListener('touchmove',touchMove, false);
				$(".wrapper").addClass("hide");
			} else {
				$(this).parents("li").removeClass("current");
				$(".main-nav").animate({
					left: '-100%'
				}, 300);
				$(".m-nav-inner li,.m-nav-inner a").removeClass("current");
				$(".m-nav-inner .next-ul,.m-nav-inner .last-ul").slideUp(300, function() {
					$(".header-top").removeClass("menu_relative")
				});
				$(".m-nav-inner h3").hide();
				$(".m-nav-inner h3").hide();
				$(".m-nav-inner h2,.m-nav-inner li,.m-nav-inner .fist-li").show();
				$(".m-nav-inner li,.m-nav-inner a").removeClass("current");
				$(".m-nav-inner .next-ul,.m-nav-inner .last-ul").slideUp(300);
				document.getElementById("wrapper").removeEventListener('touchmove',touchMove, false);
				$(".wrapper").removeClass("hide");
			}
			setTimeout(function(){myScroll.refresh();},300);
		});
		$(".close_main-nav").click(function() {
			document.getElementById("wrapper").removeEventListener('touchmove',touchMove, false);
			$(".header-top").removeClass("menu_relative");
			$(".wrapper").removeClass("hide");
			$(".header-top .click-nav").parents("li").removeClass("current");
			$(".main-nav").animate({
				left: '-100%'
			}, 300)
		});
		$(".m-nav-inner .fist-li").click(function() {
			$(".m-nav-inner h2").hide();
			$(".m-nav-inner h3").show();
			$(".m-nav-inner .fist-li").not(this).parents("li").hide();
			$(this).hide();
			if (!$(this).hasClass("current")) {
				$(this).addClass("current");
				$(this).parents("li").find(".next-ul").slideDown(300);
				$(".m-nav-inner .fist-li").not(this).parents("li").find(".next-ul").slideUp(300);
				$(".m-nav-inner .fist-li").not(this).removeClass("current")
			} else {
				$(this).removeClass("current");
				$(this).parents("li").find(".next-ul").slideUp(300);
				$(this).parents("li").find(".last-ul").slideUp(300);
				$(this).parents("li").find(".next-ul li").removeClass("current");
			}
			setTimeout(function(){myScroll.refresh();},300);
		});
		$(".m-nav-inner h3 a,.close_main-nav").click(function() {
			$(".m-nav-inner h3").hide();
			$(".m-nav-inner h2,.m-nav-inner li,.m-nav-inner .fist-li").show();
			$(".m-nav-inner li,.m-nav-inner a").removeClass("current");
			$(".m-nav-inner .next-ul,.m-nav-inner .last-ul").slideUp(300);
			setTimeout(function(){myScroll.refresh();},300);
		});
		$(".m-nav-inner .last-li").click(function() {
			if (!$(this).parent("li").hasClass("current")&&$(this).parent("li").find(".last-ul li").length>0) {
				$(this).parent("li").addClass("current");
				$(this).parent("li").find(".last-ul").slideDown(300);
				$(".m-nav-inner .last-li").not(this).parent("li").find(".last-ul").slideUp(300);
				$(".m-nav-inner .last-li").not(this).parent("li").removeClass("current")
			} else {
				$(this).parent("li").removeClass("current");
				$(this).parent("li").find(".last-ul").slideUp(300);
			}
			setTimeout(function(){myScroll.refresh();},300);
		});
		$(".page-Top i").click(function() {
			$("html, body").animate({
				scrollTop: 0
			}, 600)
		});
		$(window).scroll(function() {
			if ($(window).scrollTop() > 200) {
				$(".page-Top").show();
			} else {
				$(".page-Top").hide()
			}
		});
		function  touchMove(event){
			event.preventDefault();
		}
	})
})(jQuery)