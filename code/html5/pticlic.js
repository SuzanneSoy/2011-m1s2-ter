function jss() {
	var w=480, h=800;
	var mch = h/8, mnh = h*0.075;
	$("#screen")
		.width(w)
		.height(h)
	
	$("#mc-caption-block")
		.width(w)
		.height(mch)
		.position({my:"center top", at:"center top", of:"#screen", collision:"none"});
	$("#mc-caption")
		.fitFont(w*0.9, mch*0.9, 20)
		.css("max-width", w*0.9)
		.position({my:"center center", at:"center center", of:"#mc-caption-block", collision:"none"});
	
	$("#mn-caption-block")
		.width(w)
		.height(mnh)
		.css("border-width", h/100)
		.position({my:"center top", at:"center bottom", of:"#mc-caption-block", collision:"none"});
	$("#mn-caption")
		.fitFont(w*0.9, mnh*0.9, 20)
		.css("max-width", w*0.9)
		.position({my:"center center", at:"center center", of:"#mn-caption-block", collision:"none"});

	$("#screen")
		.position({my:"center center", at:"center center", of:"body", collision:"none"});
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
					refresh();
				})
				.appendTo(".relations");
		});
		
		refresh();
	});
});
