$(function () {
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
