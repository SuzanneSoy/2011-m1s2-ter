if (typeof console == "undefined") { console = {}; }
if (typeof console.log == "undefined") { console.log = function() {}; }

$(function () {
	var url = "tmp.json"
	$.getJSON(url, function(data) {
		dbg = data;
		var game = data[0];
		var currentWordNb = 0;
		var answers = [];
		
		var refresh = function() {
			$(".currentWord").text(game.cloud[currentWordNb].name);
			
			$.each(game.cat, function(rel, cat) {
				$("#r"+rel).text(cat.name.replace("%mc", game.center.name).replace("%mn", game.cloud[currentWordNb].name));
			});
		}
		
		refresh();
		
		$(".centralWord").text(game.center.name);
		$.each(game.cat, function(rel, cat) {
			$("#r"+rel)
				.addClass("rid"+cat.id)
				.click(function() {
					answers[currentWordNb++] = cat.id;
					refresh();
				});
		});
	});
});
