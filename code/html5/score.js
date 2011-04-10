$(function () {
	var url = "score.json";
	$.getJSON(url, function(data) {
		console.log(data);
		$.each(data, function(i,e) {
			$("#templates .scoreLine")
				.clone()
				.find(".word").text(e.name).end()
				.find(".score").text(e.score).end()
				.appendTo(".scores")
		});
	})
});