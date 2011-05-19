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
	$.getJSON("server.php", {action:"5"}, function (data) {
		var numWord = 1;
		var user = "foo";
		var passwd = "bar";
		var relations = data;
		var nbWordMin = 5;
		var wordsOK = new Array();
		var centerOK = false;
		
		
		var displayNWordLines = function (nb) {
			
			for(var i=numWord; i<numWord+nb; i++){
				$("#templates .wordLine")
					.clone()
					.changeId(i)
					.addClass(i%2==0 ? "lightLine" : "")
					.appendTo(".wordLinesTable tbody");
				
				(function (i) {
					$("#word-"+i).focusout(checkWord);
					wordsOK["word-"+i] = false;
				})(i);
			}

			numWord += nb;
			
			displayRelations();
		};
		
		var updateRelationLabels = function() {
			$('#relations option').each(function(i,e) {
				$(e).text(applyFormat($(e).data("format"), $('#centralWord').val() || 'mot central', '…'));
			});
			
			$('.relationLabel').each(function(i,e) {
				$(e).text(applyFormat(
					$(e).data("format"),
					$('#centralWord').val() || 'mot central',
					$(e).closest('.wordLine').find('.word').val() || '…'));
			});
		}
		
		var displayRelations = function() {
			$(".r1").data("format", relations[$("#relation1").val()]);
			$(".r2").data("format", relations[$("#relation2").val()]);
			$(".r3").data("format", relations[0]);
			$(".r4").data("format", relations[-1]);
			updateRelationLabels();
		}
		
		var applyFormat = function(str, mc, mn) {
			return str.replace(/%mc/g, mc).replace(/%mn/g, mn);
		};
		
		var displayCentralWordAndRelations = function() {
			$("#centralWord").focusout(checkWord);
			
			$.each(relations, function(i, value) {
				if(i != 0 && i != -1)
					$('<option/>')
						.val(i)
						.data("format", value)
						.appendTo("#relations select");
			});
			$("#relation1, #relation2").change(function() {
				if ($("#relation1").val() == $("#relation2").val())
					displayError("Les relations doivent être différentes");
				else
					displayError("");
					
				displayRelations();
			});
			$("select#relation1").val(5);
			$("select#relation2").val(7);
			displayRelations();
		};
		
		var displayButtons = function () {
			$("#button").html('<input type="button" id="addLine" name="addLine" value="Ajouter" />');
			$("#addLine").click(function(){ displayNWordLines(1); });
			
			$("#button").append('<input type="button" id="validate" name="validate" value="Valider" />');
			$("#validate").click(function(){ formOK(); });
		};
		
		var checkWord = function () {
			updateRelationLabels();
			var input = $(this);
			var word = input.val();

			input.closest(".wordLine, #center").removeClass("valid invalid");

			if (word != "") {
				$.ajax({
					type: "GET",
					url: "server.php?",
   					data: "action=4&word="+word, //+"&user="+user+"&passwd="+passwd,
   					success: function(msg){
   						input.closest(".wordLine, #center").addClass(msg == false ? "invalid" : "valid");
   						wordsOK[input.attr("id")] = !(msg == false);
    				}});
    		}
		};
		
		var formOK = function() {
			displayError("");
		   	
			if ($("#relation1").val() == $("#relation2").val())
				displayError("Les deux relation doivent être différents");
			else if ($("#centralWord").val() == "")
				displayError("Le mot central doit être renseigné.");
			else if (badWord())
				displayError("Il existe des mots incorrects");   			
			else if (nbWordOK() < nbWordMin)
				displayError("Le nuage doit contenir au moins "+nbWordMin+" mots valides.");
			else if (!relationsOK())
				displayError("Tout les mots ne sont pas liés à une relation");
			else
				sendGame();
   				
			return false;
		};
		
		var nbWordOK = function() {
			var count = 0;
		   	
   		for (word in wordsOK)
   			if (wordsOK[word] == true)
   				count++;
   			
   		return count;
		};
		
		var badWord = function() {
			for (word in wordsOK)
   			if ($("#"+word).val() != "" && wordsOK[word] == false)
   				return true;
   			
   		return false;
		}
   	
		var relationsOK = function() {
			for(i = 1; i < numWord; i++) {
				if(wordsOK["word-"+i]) {
					if(!$("#r1-"+i).is(":checked") && !$("#r2-"+i).is(":checked") && !$("#r3-"+i).is(":checked") && !$("#r4-"+i).is(":checked"))
						return false;
				}
			}	
						
			return true;
		}
   	
		var sendGame = function() {
			var exit;
			var cloud = "";
			
			exit = {center:$("#centralWord").val(),
					  relations:[$("#relation1").val(),$("#relation2").val(),0,-1],
					  cloud:[]};
					  
			for(i=1;i<numWord;i++) {
				exit.cloud.push({
					name:$("#word-"+i).val(),
					relations:[
						$("#r1-"+i).is(":checked") ? "1":"0",
						$("#r2-"+i).is(":checked") ? "1":"0",
						$("#r3-"+i).is(":checked") ? "1":"0",
						$("#r4-"+i).is(":checked") ? "1":"0"
						]
					});
			}
			
			$.get("server.php",{action:"6",game:exit},function (data) {
				//$(".word").closest(".wordLine, #center").removeClass("valid invalid");
					if(data == true) {
						displaySuccess("La partie à bien été enregistrée");
						$('#newCreationLink').show();
						$('#center').hide();
						$('#relations').hide();
						$('#wordLines').hide();
						$('#button').hide();
					}
					else if (data == false) {
						$('input').removeAttr('disabled');
						displayError("Le nuage doit contenir au moins "+nbWordMin+" mots valides.");
					}
					else if (data != true) {
						$('input').removeAttr('disabled');
						var that = $(this);
						console.log("mot incorrect");
						$.each(data,function(i,e) {
							$('.word')
								.filter(function() { return that.val() == e; })
								.closest(".wordLine, #center")
								.removeClass("valid invalid")
								.addClass("invalid");
						});
					}
			});
			
			$('input').attr('disabled', 'disabled');
		}

		var displayError = function(message) {
			if (message != "")
				$("#errorDiv").text(message).show();
			else
				$("#errorDiv").hide();
		};
		
		var displaySuccess = function(message) {
			if (message != "")
				$("#successDiv").text(message).show();
			else
				$("#successDiv").hide();
		};
		
		displayCentralWordAndRelations();	
		displayNWordLines(nbWordMin+5);
		displayButtons();
	});
});
