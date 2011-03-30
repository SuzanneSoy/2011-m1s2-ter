$(function () {
/*	window.setTimeout(function() {
	var w=480;
	var h=800;
	var a=[]; $("#screen").find("*").add("#screen").each(function(i,e){ a.push({
		e:$(e),
		w:$(e).width()*w/480,
		h:$(e).height()*h/800});
	});
	$.each(a,function(i,a){ a.e.width(a.w); a.e.height(a.h); });
}, 1000); */
	
	var url = "tmp.json"
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
