$(function () {
	var numWord = 1;
	
	var displayNWordLines = function (nb) {
		var wLines = "";

		for(var i=numWord; i<numWord+nb; i++){
			wLines += '<div><label for="word'+i+'">'+i+' </label><input id="word'+i+'" /></div>';
		}
		
		$("#wordLines").html(wLines);
		
		for(var i=numWord; i<numWord+nb; i++){
			function f(id) {
				$("#word"+id).focusout(function () {
				var input = "word"+id;
				
				checkWord(input)});
			};
			
			f(i);
		}
		
		numWord += nb;
	}
	
	var displayCentralWord = function () {
		$("#center").html('<label for="centralWord"> Le mot central : </label><input type="text" id="centralWord" name="centralWord" />');
		$("#centralWord").focusout(function () {
									var input = "centralWord";
				
									checkWord(input)
									}
								);
	}
	
	var checkWord = function (inputId) {
		var input = "#"+inputId;
		var word = $(input).val();

		if(word == "")
			$(input).css("background-color", "white");
		else {
			$.ajax({type: "GET",   url: "server.php?",
   					data: "action=4&word="+word+"&user=foo&passwd=bar",
   					success: function(msg){
   						if(msg == "false")
   							$(input).css("background-color", "orange");
   						else
   							$(input).css("background-color", "#55FF55");
    					}});
    	}
   }

	displayCentralWord();	
	displayNWordLines(10);
});
