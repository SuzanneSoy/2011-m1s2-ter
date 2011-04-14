$.fn.changeId = function(append) {
	this.find("[id]").each(function(i,e){
		$(e).attr("id", $(e).attr("id") + append);
	});
	this.find('[for="word-"]').text(append);
	this.find("[for]").each(function(i,e){
		$(e).attr("for", $(e).attr("for") + append);
	});
	return this;
};

$(function() {
	$.getJSON("server.php", {action:"5", user:"foo", passwd:"bar"}, function (data) {
		var numWord = 1;
		var user = "foo";
		var passwd = "bar";
		var relations = data;
		var nbWordMin = 10;
		var wordsOK = new Array();
		var centerOK = false;
		
		
		var displayNWordLines = function (nb) {
			
			for(var i=numWord; i<numWord+nb; i++){
				$("#templates .wordLine")
					.clone()
					.changeId(i)
//					.find("label").attr("for", "word"+i).text(i).end()
//					.find("input").attr("id", "word"+i).end()
					.appendTo("#wordLines");
				
				(function (i) {
					$("#word-"+i).focusout(checkWord);
					wordsOK[i] = false;
				})(i);
			}

			numWord += nb;
			
			displayRelations();
			// truc.children("option:nth-child(2)");
			// $(truc.children("option").get(2 /* ou 1 */))
		};
		
		var displayRelations = function() {
			$(".r1").text(relations[$("#relation1").val()]);
			$(".r2").text(relations[$("#relation2").val()]);
			$(".r3").text(relations[0]);
			$(".r4").text(relations[-1]);
		}
		
		var displayCentralWordAndRelations = function() {
			$("#centralWord").focusout(checkWord);
			
			$.each(relations, function(i, value) {
				if(i != 0 && i != -1)
					$('<option/>').val(i).text(value).appendTo("#relations select");
			});
			$("#relation1, #relation2").change(function() {
				if ($("#relation1").val() == $("#relation2").val())
					displayError("Les relations doivent être différentes");
				else
					displayError("");
					
				displayRelations();
			});
		};
		
		var displayButtons = function () {
			$("#button").html('<input type="button" id="addLine" name="addLine" value="Ajouter" />');
			$("#addLine").click(function(){ displayNWordLines(1); });
			
			$("#button").append('<input type="button" id="validate" name="validate" value="Valider" />');
			$("#validate").click(function(){ formOK(); });
		};
		
		var checkWord = function () {
			var input = $(this);
			var word = input.val();

			input.parent(".wordLine, #center").removeClass("valid invalid");

			if (word != "") {
				$.ajax({
					type: "GET",
					url: "server.php?",
   					data: "action=4&word="+word+"&user="+user+"&passwd="+passwd,
   					success: function(msg){
   						input.parent(".wordLine, #center").addClass((msg == false) ? "invalid" : "valid");
   						wordsOK[input.val()] = !(msg == false);
    				}});
    		}
		};
		
		var formOK = function() {
			displayError("");
			
			console.log(wordsOK);
		   	
   			if ($("#relation1").val() == $("#relation2").val())
   				displayError("Les deux relation doivent être différents");
   			else if ($("#centralWord").val() == "")
   				displayError("Le mot central doit être renseigné.");
				else if (badWord())
					displayError("Il existe des mots incorrects");   			
   			else if (nbWordOK() < nbWordMin)
   				displayError("Le nuage doit contenir au moins "+nbWordMin+" mots valides.");
		};
		
		var nbWordOK = function() {
			var count = 0;
		   	
   		for (word in wordsOK)
   			if (word == true)
   				count++;
   			
   		return count;
		};
		
		var badWord = function() {
			for (word in wordsOK)
   			if (word == false)
   				return true;
   			
   		return false;
   	}
   	
   	
   		

		var displayError = function(message) {
			if (message != "")
				$("#errorDiv").text(message).show();
			else
				$("#errorDiv").hide();
		};
		
		displayCentralWordAndRelations();	
		displayNWordLines(10);
		displayButtons();
	});
});
