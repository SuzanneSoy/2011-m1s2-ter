function jss() {
	var w=480, h=800;
	var mch = h/8, mnh = h*0.075;
	
	$("body")
		.css({
			padding: 0,
			margin: 0,
			textAlign: "left"
		});
	
	$("#screen")
		.width(w)
		.height(h)
		.position({my:"center center", at:"center center", of:"body", collision:"none"});
	
	$("#mc-caption-block")
		.css({
			position: "absolute"
		})
		.width(w)
		.height(mch)
		.position({my:"center top", at:"center top", of:"#screen", collision:"none"});
	
	$("#mc-caption")
		.css({
			maxWidth: w*0.9,
			textAlign: "center",
			position: "absolute"
		})
		.fitFont(w*0.9, mch*0.9, 20)
		.position({my:"center center", at:"center center", of:"#mc-caption-block", collision:"none"});
	
	$("#mn-caption-block")
		.css({
			borderWidth: h/100,
			position: "absolute"
		})
		.width(w)
		.height(mnh)
		.position({my:"center top", at:"center bottom", of:"#mc-caption-block", collision:"none"});
	
	$("#mn-caption")
		.css({
			maxWidth: w*0.9,
			textAlign: "center",
			position: "absolute"
		})
		.fitFont(w*0.9, mnh*0.9, 20)
		.position({my:"center center", at:"center center", of:"#mn-caption-block", collision:"none"});
	
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
		.position({my:"center bottom", at:"center bottom", of:"#screen", offset:"0 -10", collision:"none"});
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
				.click(function() {
					answers[currentWordNb++] = cat.id;
					$(this).addClass("hot")//.delay(500).removeClass("hot"); // TODO: just blink.
					refresh();
				})
				.appendTo(".relations");
		});
		
		$(window).resize(jss);
		refresh();
		refresh(); // TODO : fix the bug with the margin on ".relation > div"
	});
});
