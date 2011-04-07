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
	
	$(".relations > div")
		.css({
			margin: 10,
			height: 72,
			padding: 10,
		});

	// TODO : fitFont pour ".relations div"
	$(".relations > div:nth-child(odd)")
		.css({
			backgroundPosition: "2% center", // TODO : virer le pourcentage, et séparer l'icône dans un nouvel élément.
			textAlign: "right",
			paddingLeft: 76
		});
	
	$(".relations > div:nth-child(even)")
		.css({
			backgroundPosition: "98% center", // TODO : virer le pourcentage, et séparer l'icône dans un nouvel élément.
			textAlign: "left",
			paddingRight: 76
		});
	
	$(".relations")
		.south($("#screen").south());
}

function animateNext(e, button) {
	console.log(e, e.clientX, e.clientY);
	$(button).clearQueue().qAddClass("hot").delay(100).qRemoveClass("hot");
	$("#mn-caption").clearQueue().animate({left:e.clientX, top:e.clientY},1500);
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
			$('<div/>')
				.html(cat.name.replace(/%(m[cn])/g, '<span class="$1"/>'))
				.addClass("rid"+cat.id)
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
