function jss() {
	var w=480, h=800;
	var mch = h/8, mnh = h*0.075;
	
	$("body, html")
		.css({
			padding: 0,
			margin: 0,
			textAlign: "left"
		});
	
	$("#screen")
		.wh(w, h)
		.north($("body").north()); // TODO : par rapport à la fenêtre entière.0
	
	$("#mc-caption-block")
		.wh(w, mch)
		.north($("#screen").north());
	
	$("#mc-caption")
		.fitIn("#mc-caption-block", 0.1);
	
	$("#mn-caption-block")
		.css({borderWidth: h/100})
		.wh(w, mnh)
		.north($("#mc-caption-block").south());
	
	$("#mn-caption")
		.css({zIndex: 10})
		.fitIn("#mn-caption-block");
	
	$(".relation > *")
		.css({
			display: "inline-block",
			position: "absolute",
			textAlign: "right"
		});

	$(".relation .icon")
		.wh(72,72);
		
	$(".relation")
		.wh(w,76);

	$(".relation").each(function (i,e) {
		e = $(e);
		e.find(".icon")
			.west(e.west());
		e.find(".text")
			.east(e.east());
	});
	
	$(".relations")
		.south($("#screen").south());
}

function animateNext(e, button) {
	console.log(e, e.clientX, e.clientY);
	$(button).clearQueue().qAddClass("hot").delay(100).qRemoveClass("hot");
	var el = $("#mn-caption")
		.clone()
		.removeClass("mn")
		.appendTo("#screen")
		.clearQueue();
	var oldOff = el.offset();
	el.offset({left:e.pageX, top:e.pageY});
	var pos = el.position();
	el.offset(oldOff);
	pos.fontSize = 0;
	el.animate(pos,500).queue(function() {
		el.remove();
	});
}


$(function () {
	var url = "tmp.json";
	$.getJSON(url, function(data) {
		var game = data[0];
		var currentWordNb = 0;
		var answers = [];
		
		var refresh = function() {
			if (currentWordNb < game.cloud.length) {
				$(".mn").text(game.cloud[currentWordNb].name);
				$(".mc").text(game.center.name);
			} else {
				$(".relations").empty();
				alert("Partie terminée !");
			}
			jss();
		}
		
		$.each(game.cat, function(i, cat) {
			$('#templates .relation')
				.clone()
				.find(".text")
					.html(cat.name.replace(/%(m[cn])/g, '<span class="$1"/>'))
				.end()
				.find(".icon")
					.attr("src", "img/rel/"+cat.id+".png")
				.end()
				.click(function(e) {
					answers[currentWordNb++] = cat.id;
					animateNext(e, this);
					refresh();
				})
				.appendTo(".relations");
		});
		
		$(window).resize(jss);
		refresh();
		refresh(); // TODO : fix the bug with the margin on ".relation > div"
	});
});
