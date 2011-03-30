$(function () {
	var numWord = 1;
	
	var displayNWordLines = function (nb) {
		var wLines = "";

		for(var i=0; i<nb;i++){
			wLines += '<div><label for="word'+numWord+'">'+numWord+' </label><input id="word'+numWord+'" /></div>';
			numWord++;
		}

		$("#wordLines").html(wLines);
	}

	displayNWordLines(10);
});
