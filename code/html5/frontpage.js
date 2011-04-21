function jss() {
	// TODO : réduire le nombre de fitIn ou fitFont, ou bien les précalculer.
	var w, h;
	w = $(window).width();
	h = $(window).height();
	
	var mch = h/8, mnh = h*0.075;
	
	$("body, html")
		.css({
			padding: 0,
			margin: 0,
			overflow: "hidden",
			textAlign: "left"
		});
	
	$("#screen")
		.wh(w, h)
		.north($("body").north()); // TODO : par rapport à la fenêtre entière.

	$("#title-block")
		.wh(w*0.5, h*0.2)
		.north($("#screen").north());
	$("#title")
		.fitIn("#title-block", 0.2);
	
	$(".text")
		.fitFont(w*0.25,(h-(72*4))*0.8*0.5,10);
	$(".button")
		.width(w*0.4);

	$(".button.game")
		.northEast({left:w*0.45,top:h*0.3});
	$(".button.about")
		.northWest({left:w*0.55,top:h*0.3});
	$(".button.connection")
		.southEast({left:w*0.45,top:h*0.9});
	$(".button.prefs")
		.southWest({left:w*0.55,top:h*0.9});
	
}

$(function() {
	$(window).resize(jss);
	jss();
	$(".button.game").click(function(){alert("Pour jouer, il faut payer !");});
});