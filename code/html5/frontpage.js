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
	
	$("#game-icon-block")
		.wh(w*0.25, h*0.2)
		.northWest($("#title-block").southWest());
	$("#game-text-block")
		.wh(w*0.25, h*0.2)
		.north($('#game-icon-block').south());
	$("#connection-icon-block")
		.wh(w*0.25, h*0.2)
		.north($('#game-text-block').south());
	$("#connection-text-block")
		.wh(w*0.25, h*0.2)
		.north($('#connection-icon-block').south());

	$("#about-icon-block")
		.wh(w*0.25, h*0.2)
		.northEast($("#title-block").southEast());
	$("#about-text-block")
		.wh(w*0.25, h*0.2)
		.north($('#about-icon-block').south());
	$("#prefs-icon-block")
		.wh(w*0.25, h*0.2)
		.north($('#about-text-block').south());
	$("#prefs-text-block")
		.wh(w*0.25, h*0.2)
		.north($('#prefs-icon-block').south());

	$("#title")
		.fitIn("#title-block", 0.1);
	$(".icon")
		.wh(72,72);
	$(".game.icon")
		.center($("#game-icon-block").center());
	$(".about.icon")
		.center($("#about-icon-block").center());
	$(".prefs.icon")
		.center($("#prefs-icon-block").center());
	$(".connection.icon")
		.center($("#connection-icon-block").center());

	$(".text")
		.fitIn("#game-text-block", 0.2);
	$(".game.text")
		.center($("#game-text-block").center());
	$(".about.text")
		.center($("#about-text-block").center());
	$(".connection.text")
		.center($("#connection-text-block").center());
	$(".prefs.text")
		.center($("#prefs-text-block").center());
}

$(function() {
	$(window).resize(jss);
	jss();
});