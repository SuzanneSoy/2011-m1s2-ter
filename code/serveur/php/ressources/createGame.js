$($.getJSON("server.php",
					{action:"5", user:"foo", passwd:"bar"},
					function (data) {
	var numWord = 1;
	var user = "foo";
	var passwd = "bar";
	var relations = data;
	
	var displayNWordLines = function (nb) {

		for(var i=numWord; i<numWord+nb; i++){
			$("#templateWordLine")
				.clone()
				.find("label").attr("for", "word"+i).text(i).end()
				.find("input").attr("id", "word"+i).end()
				.appendTo("#wordLines");
			
			(function (i) {
				$("#word"+i).focusout(checkWord);
			})(i);
		}

		numWord += nb;
	}
	
	var displayCentralWordAndRelations = function () {
		$("#centralWord").focusout(checkWord);
		
		$.each(relations,function(i,value){
			$('<option/>').val(i).text(value).appendTo("#relations select")
		});
		$("#relation1, #relation2").change(function(){
			if($("#relation1").val() == $("#relation2").val()) {
				$("#errorDiv").text("Les relations doivent être différentes");
				$("#errorDiv").css("display","block");			
			}
			else {
				$("#errorDiv").text("");
				$("#errorDiv").css("display","none");
			}
		});
	}
	
	var displayButtons = function () {
		$("#button").html('<input type="button" id="addLine" name="addLine" value="Ajouter" />');
		$("#addLine").click(function(){displayNWordLines(1)});
		
		$("#button").append('<input type="button" id="validate" name="validate" value="Valider" />');
		$("#validate").click(function(){});
	}
	
	var checkWord = function () {
		var input = $(this);
		var word = input.val();

		input.parent(".wordLine, #center").removeClass("valid invalid");

		if (word != "") {
			$.ajax({type: "GET",   url: "server.php?",
   					data: "action=4&word="+word+"&user="+user+"&passwd="+passwd,
   					success: function(msg){
   						input.parent(".wordLine, #center").addClass((msg == false) ? "invalid" : "valid");
    					}});
    	}
   };

	displayCentralWordAndRelations();	
	displayNWordLines(10);
	displayButtons();
}));
