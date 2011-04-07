function jss() {
	var w=480, h=800;
	var mch = h/8, mnh = h*0.075;
	
	$("body, html")
		.css({
			padding: 0,
			margin: 0,
			textAlign: "left"
		});
	
	$("#screen")
		.wh(w, h)
		.north($("body").north()); // TODO : par rapport à la fenêtre entière.0
	
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
	
	$(".relation > *")
		.css({
			display: "inline-block",
			position: "absolute",
			textAlign: "right"
		});

	$(".relation .icon")
		.wh(72,72);
		
	$(".relation")
		.wh(w,76);

	$(".relation").each(function (i,e) {
		e = $(e);
		e.find(".icon")
			.west(e.west());
		e.find(".text")
			.east(e.east());
	});
	
	$(".relations")
		.south($("#screen").south());
}

$(function () {
	var url = "tmp.json";
	$.getJSON(url, function(data) {
		var game = data[0];
		var currentWordNb = 0;
		var answers = [];
		
		var refresh = function() {
			if (currentWordNb < game.cloud.length) {
				$(".mn").text(game.cloud[currentWordNb].name);
				$(".mc").text(game.center.name);
			} else {
				$(".relations").empty();
				alert("Partie terminée !");
			}
			jss();
		}
		
		function animateNext(e, button) {
			var duration = 700;
			
			var mn = $("#mn-caption");
			
			$(button).addClass("hot").removeClass("hot", duration);
			
			(mn)
				.clone()
				.removeClass("mn") // Pour que le texte animé ne soit pas modifié.
				.appendTo("body") // Append to body so we can animate the offset (instead of top/left).
				.offset(mn.offset())
				.clearQueue()
				.animate({left:e.pageX, top:e.pageY, fontSize: 0}, duration)
				.queue(function() { $(this).remove(); });

			refresh();
			var fs = mn.css("fontSize");
			var mncbCenter = $("#mn-caption-block").center();
			
			(mn)
				.css("fontSize", 0)
				.animate({fontSize: fs}, {duration:duration, step:function(){mn.center(mncbCenter);}});
		}
		
		$.each(game.cat, function(i, cat) {
			$('#templates .relation')
				.clone()
				.find(".text")
					.html(cat.name.replace(/%(m[cn])/g, '<span class="$1"/>'))
				.end()
				.find(".icon")
					.attr("src", "img/rel/"+cat.id+".png")
				.end()
				.click(function(e) {
					answers[currentWordNb++] = cat.id;
					animateNext(e, this);
				})
				.appendTo(".relations");
		});
		
		$(window).resize(jss);
		refresh();
		refresh(); // TODO : fix the bug with the margin on ".relation > div"
	});
});
