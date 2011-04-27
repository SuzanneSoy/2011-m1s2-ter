// ==== URL persistante
function State(init) {
	$.extend(this, init || {});
	if (!this.screen) this.screen = 'frontpage';
};
State.prototype.commit = function() {
	location.hash="#"+$.JSON.encode(this);
	return this;
};
State.prototype.get = function(key) {
	return this[key];
};
State.prototype.set = function(key, value) {
	this[key] = value;
	return this;
};
var state;
var oldScreen = '';
var enter = {};
var leave = {};
var ui = {};
function hashchange() {
	var stateJSON = location.hash.substring(location.hash.indexOf("#") + 1);
	state = new State($.parseJSON(stateJSON));
	if (oldScreen != state.screen) {
		if (leave[oldScreen]) leave[oldScreen]();
		oldScreen = state.screen;
		if (enter[state.screen]) enter[state.screen]();
	}
}

// ==== JavaScript Style général
function jss() {
	var w = $(window).width();
	var h = $(window).height();
	var iconSize = 72;
	
	$(".screen")
		.wh(w, h)
		.northWest({top:0,left:0});
	
	$("body, html")
		.css({
			padding: 0,
			margin: 0,
			overflow: "hidden",
			textAlign: "left"
		});
	
	$(".screen").hide();
	$("#"+state.screen+".screen").show();
	
	jss[state.screen](w, h, iconSize);
}

// ==== JavaScript Style pour la frontpage
jss.frontpage = function(w, h, iconSize) {
	var fp = $("#frontpage.screen");
	var $fp = function() { return fp.find.apply(fp,arguments); };
	$fp("#title-block")
		.wh(w*0.5, h*0.2)
		.north($("#frontpage.screen").north());
	$fp("#title")
		.fitIn("#title-block", 0.2);
	
	$fp(".text")
		.fitFont(w*0.25,(h-(iconSize*4))*0.8*0.5,10);
	$fp(".frontpage-button")
		.width(w*0.4);

	$fp(".frontpage-button.game")
		.northEast({left:w*0.45,top:h*0.3});
	$fp(".frontpage-button.about")
		.northWest({left:w*0.55,top:h*0.3});
	$fp(".frontpage-button.connection")
		.southEast({left:w*0.45,top:h*0.8});
	$fp(".frontpage-button.prefs")
		.southWest({left:w*0.55,top:h*0.8});	
};

// ==== JavaScript Style pour le jeu
jss.game = function(w, h, iconSize) {
	var g = $("#game.screen");
	var $g = function() { return g.find.apply(g,arguments); };
	var mch = h/8, mnh = h*0.075;
	
	$g("#mc-caption-block")
		.wh(w, mch)
		.north(g.north());
	
	$g("#mc-caption")
		.fitIn("#mc-caption-block", 0.1);
	
	$g("#mn-caption-block")
		.css({borderWidth: h/100})
		.wh(w, mnh)
		.north($g("#mc-caption-block").south());
	
	$g("#mn-caption")
		.css({zIndex: 10})
		.fitIn("#mn-caption-block");

	$g(".relationBox:visible")
		.css({
			margin: 10,
			padding: 10,
			MozBorderRadius: 10,
			WebkitBorderRadius: 10
		});
	
	$g(".relationBox:visible .icon")
		.wh(iconSize,iconSize)
		.css({
			float: "left",
			marginRight: $g(".relationBox").css("padding-left")
		});
	
	$g(".relations")
		.width(w);

	$g(".relation:visible").fitFont($g(".relationBox:visible").width(), iconSize, 10);
	
	$g(".relations")
		.south(g.south());
};

jss.score = function(w, h, iconSize) {
	$(".screen")
		.css('text-align', 'center');
};

// ==== Interface Android
var UI = {
	setPreference: function() {},
	getPreference: function() {return "";},
	show: function() {},
	dismiss: function() {},
	exit: function() {}
};

if (typeof(PtiClicAndroid) != "undefined") {
	UI = PtiClicAndroid;
}

// ==== Code métier général
$(function() {
	$(window).resize(jss);
	$(window).hashchange(hashchange);
	hashchange();
});

function ajaxError(x) {
	UI.dismiss();
	alert("Erreur fatale. Merci de nous envoyer ce message : "+x.status+" - "+x.statusText+"\n"+x.responseText.substring(0,20)+((x.responseText == '') ? '': 'âò  Š'));
}

// ==== Code métier pour la frontpage
enter.frontpage = function () {
	$("#frontpage .frontpage-button.game").click(function(){
		state.set('screen', 'game').commit();
	});
	jss();
	UI.dismiss();
};

// ==== Code métier pour le jeu
enter.game = function () {
	UI.show("PtiClic", "Récupération de la partie");
	$.getJSON("getGame.php?callback=?", {
		user:"foo",
		passwd:"bar",
		nonce:Math.random()
	}, function(data) {
		state.game = data;
		ui.game();
	}).error(ajaxError);
	jss();
};

leave.game = function () {
	$("#game .relations").empty();
	$('#game #mn-caption').stop().clearQueue();
};

ui.game = function () {
	var currentWordNb = 0;
	state.game.answers = [];
	
	var updateText = function() {
		$("#game .mn").text(state.game.cloud[currentWordNb].name);
		$("#game .mc").text(state.game.center.name);
		jss();
	}
	
	var nextWord = function(click, button) {
		state.game.answers[currentWordNb++] = $(button).data("rid");
		if (currentWordNb < state.game.cloud.length) {
			animateNext(click, button);
		} else {
			state.set('screen','score').commit();
		}
	}
	
	function animateNext(click, button) {
		var duration = 700;
		
		var mn = $("#game #mn-caption");
		
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
		var mncbCenter = $("#game #mn-caption-block").center();
		
		(mn)
			.css("fontSize", 0)
			.animate({fontSize: fs}, {duration:duration, step:function(){mn.center(mncbCenter);}});
	}
	
	$.each(state.game.relations, function(i, relation) {
		$('#templates .relationBox')
			.clone()
			.data("rid", relation.id)
			.find(".text")
				.html(relation.name.replace(/%(m[cn])/g, '<span class="$1"/>'))
			.end()
			.find(".icon")
				.attr("src", "ressources/img/rel/"+relation.id+".png")
			.end()
			.click(function(e) {
				nextWord({left:e.pageX, top:e.pageY}, this);
			})
			.appendTo("#game .relations");
	});
	
	updateText();
	UI.dismiss();
}

// ==== Code métier pour les scores
enter.score = function () {
	UI.show("PtiClic", "Calcul de votre score");
	$.getJSON("server.php?callback=?", {
		user: "foo",
		passwd: "bar",
		action: 1,
		pgid: state.game.pgid,
		gid: state.game.gid,
		answers: state.game.answers,
		nonce: Math.random()
	}, function(data) {
		for (var i = 0; i < data.scores.length; i++) {
			state.game.cloud[i].score = data.scores[i];
		}
		delete data.score;
		$.extend(state.game, data);
		ui.score();
	}).error(ajaxError);
	jss();
}

ui.score = function () {
	$.each(state.game.cloud, function(i,e) {
		var percentScore = (e.score - state.game.minScore) / (state.game.maxScore - state.game.minScore);
		u = $("#templates .scoreLine");
		ee = e;
		$("#templates .scoreLine")
			.clone()
			.find(".word")
				.text(e.name)
			.end()
			.find(".score")
				.text(e.score)
				.css("color","rgb("+(255 - 255*percentScore).clip(0,255)+","+(191*percentScore).clip(0,255,true)+",0)")
			.end()
			.appendTo("#score .scores");
		$("#score #jaivu").click(function() {
			state = new State().commit();
		});
		jss();
	});
	UI.dismiss();
}
