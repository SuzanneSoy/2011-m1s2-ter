$(function () {
	var url = "tmp.json"
	$.getJSON(url, function(data) {
		console.log(data);
		var game = data[0];
		var currentWordNb = 0;
		$(".centralWord").text(game.center.name);
		$(".currentWord").text(game.cloud[currentWordNb].name);
	})
});