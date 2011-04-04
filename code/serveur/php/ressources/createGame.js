$(function () {
	var numWord = 1;
	var user = "foo";
	var passwd = "bar";
	
	var displayNWordLines = function (nb) {

		for(var i=numWord; i<numWord+nb; i++){
			$("#wordLines").append('<div><label for="word'+i+'">'+i+' </label><input id="word'+i+'" /></div>');
			
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
	
	var displayButtons = function () {
		$("#button").html('<input type="button" id="addLine" name="addLine" value="Ajouter" />');
		$("#addLine").click(function(){displayNWordLines(1)});
		
		$("#button").append('<input type="button" id="validate" name="validate" value="Valider" />');
		$("#validate").click(function(){});
	}
	
	var checkWord = function (inputId) {
		var input = "#"+inputId;
		var word = $(input).val();

		if(word == "")
			$(input).css("background-color", "white");
		else {
			$.ajax({type: "GET",   url: "server.php?",
   					data: "action=4&word="+word+"&user="+user+"&passwd="+passwd,
   					success: function(msg){
   						if(msg == "false")
   							$(input).css("background-color", "orange");
   						else
   							$(input).css("background-color", "#55FF55");
    					}});
    	}
   }

	var getRelationsList = function () {
		$.getJSON("server.php?action=5&user=foo&passwd=ba",function (data) {
														$.debug(data);
													});
	}
	getRelationsList();
	displayCentralWord();	
	displayNWordLines(10);
	displayButtons();
});
