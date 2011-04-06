$($.getJSON("server.php",
					{action:"5", user:"foo", passwd:"bar"},
					function (data) {
	var numWord = 1;
	var user = "foo";
	var passwd = "bar";
	var relations = data;
	
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
	
	var displayCentralWordAndRelations = function () {
		$("#centralWord").focusout(function () {
			var input = "centralWord";
			checkWord(input);
		});
		$.each(relations,function(i,value){
			console.log(value);
			$('<option/>').val(i).text(value).appendTo("#relations select")
		});
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

	displayCentralWordAndRelations();	
	displayNWordLines(10);
	displayButtons();
}));
