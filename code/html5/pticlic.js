function jss() {
	// TODO : réduire le nombre de fitIn ou fitFont, ou bien les précalculer.
	var w, h;
	w = $(window).width();
	h = $(window).height();
	
	var mch = h/8, mnh = h*0.075;
	
	$("body, html")
		.css({
			padding: 0,
			margin: 0,
			overflow: "hidden",
			textAlign: "left"
		});
	
	$("#screen")
		.wh(w, h)
		.north($("body").north()); // TODO : par rapport à la fenêtre entière.
	
	$("#mc-caption-block")
		.wh(w, mch)
		.north($("#screen").north());
	
	$("#mc-caption")
		.fitIn("#mc-caption-block", 0.1);
	
	$("#mn-caption-block")
		.css({borderWidth: h/100})
		.wh(w, mnh)
		.north($("#mc-caption-block").south());
	
	$("#mn-caption")
		.css({zIndex: 10})
		.fitIn("#mn-caption-block");

	$(".relationBox:visible")
		.css({
			margin: 10,
			padding: 10,
			MozBorderRadius: 10,
			WebkitBorderRadius: 10
		});
	
	$(".relationBox:visible .icon")
		.wh(72,72)
		.css({
			float: "left",
			marginRight: $(".relationBox").css("padding-left")
		});
	
	$(".relations")
		.width(w);

	$(".relation:visible").fitFont($(".relationBox:visible").width(), 72, 10);
	
	$(".relations")
		.south($("#screen").south());
}

$(function () {
	var url = "http://www.pticlic.fr/unstable/code/serveur/php/server.php?action=0&nb=1&user=foo&passwd=bar&mode=normal";
	$.getJSON(url, function(data) {
		var game = data[0];
		var currentWordNb = 0;
		var answers = [];
		
		var updateText = function() {
			$(".mn").text(game.cloud[currentWordNb].name);
			$(".mc").text(game.center.name);
			jss();
		}
		
		var nextWord = function(click, button) {
			answers[currentWordNb++] = $(button).data("rid");
			if (currentWordNb < game.cloud.length) {
				animateNext(click, button);
			} else {
				$(".relations").empty();
				alert("Partie terminée !");
			}
		}
		
		function animateNext(click, button) {
			var duration = 700;
			
			var mn = $("#mn-caption");
			
			$(button).addClass("hot").removeClass("hot", duration);
			
			(mn)
				.stop()       // Attention : stop() et clearQueue() ont aussi un effet
				.clearQueue() // sur la 2e utilisation de mn (ci-dessous).
				.clone()
				.removeClass("mn") // Pour que le texte animé ne soit pas modifié.
				.appendTo("body") // Append to body so we can animate the offset (instead of top/left).
				.offset(mn.offset())
				.animate({left:click.left, top:click.top, fontSize: 0}, duration)
				.queue(function() { $(this).remove(); });

			updateText();
			var fs = mn.css("fontSize");
			var mncbCenter = $("#mn-caption-block").center();
			
			(mn)
				.css("fontSize", 0)
				.animate({fontSize: fs}, {duration:duration, step:function(){mn.center(mncbCenter);}});
		}
		
		$.each(game.relations, function(i, relation) {
			$('#templates .relationBox')
				.clone()
				.data("rid", relation.id)
				.find(".text")
					.html(relation.name.replace(/%(m[cn])/g, '<span class="$1"/>'))
				.end()
				.find(".icon")
					.attr("src", "img/rel/"+relation.id+".png")
				.end()
				.click(function(e) {
					nextWord({left:e.pageX, top:e.pageY}, this);
				})
				.appendTo(".relations");
		});
		
		$(window).resize(jss);
		updateText();
	});
});
