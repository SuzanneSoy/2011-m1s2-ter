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
State.prototype.validate = function () {
	state = this;
	if (oldScreen != this.screen) {
		UI.show("PtiClic", "Chargement…");
		if (window[oldScreen] && window[oldScreen].leave) window[oldScreen].leave();
		oldScreen = this.screen;
	}
	if (window[this.screen] && window[this.screen].enter) window[this.screen].enter();
	return this;
};

var runstate = {};
var state;
var oldScreen = '';
var ui = {};
function hashchange() {
	var stateJSON = location.hash.substring(location.hash.indexOf("#") + 1);
	state = new State($.parseJSON(stateJSON)).validate();
}

// ==== JavaScript Style général
function jss() {
	var w = $(window).width();
	var h = $(window).height();
	var iconSize;
	if (h > 600) iconSize = 72;
	else if(h > 500) iconSize = 48;
	else iconSize = 36;
	
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
	
	if (window[state.screen] && window[state.screen].jss) window[state.screen].jss(w, h, iconSize);
}

// ==== Interface Android
var UI = {
	setPreference: function() {},
	getPreference: function() {return "";},
	show: function(title, text) {},// { if (typeof console != 'undefined') console.log(title, text);},
	dismiss: function() {},// {if (typeof console != 'undefined') console.log('dismiss');},
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
	alert("Erreur fatale. Merci de nous envoyer ce message : "+x.status+" - "+x.statusText+"\n"+x.responseText.substring(0,20)+((x.responseText == '') ? '': '…'));
}

// ==== Code métier pour la frontpage
frontpage = {};

frontpage.jss = function(w, h, iconSize) {
	var fp = $("#frontpage.screen");
	var $fp = function() { return fp.find.apply(fp,arguments); };
	var nbIcons = $fp(".icon").size();
	var nbRows = Math.ceil(nbIcons / 2)
	var ww = w - 2 * iconSize;
	var hh = h - nbRows * iconSize;
	var titleHeight = hh*0.4;
	var labelHeight = hh*0.4 / nbRows;
	var buttonHeight = labelHeight + iconSize;
	var buttonWidth = Math.max(w*0.25,iconSize);
	var freeSpace = h - titleHeight;
	$fp("#title-block")
		.wh(w*0.5, titleHeight)
		.north($("#frontpage.screen").north());
	$fp("#title")
		.fitIn("#title-block", 0.2);
	
	$fp(".text")
		.fitFont(buttonWidth, labelHeight, 10);
	$fp(".icon")
		.wh(iconSize);
	$fp(".frontpage-button")
		.css('text-align', 'center')
		.width(buttonWidth);
	
	$fp(".frontpage-button > div").css('display', 'block');
	
	$fp(".frontpage-button").each(function(i,e){
		e=$(e);
		var currentRow = Math.floor(i/2);
		var currentColumn = i % 2;
		var interIconSpace = (freeSpace - nbRows * buttonHeight) / (nbRows + 1);
		var iconOffset = titleHeight + ((currentRow+1) * interIconSpace) + (currentRow * buttonHeight);
		if (currentColumn == 0) {
			e.northEast({left:w/2-ww*0.05,top:iconOffset});
		} else {
			e.northWest({left:w/2+ww*0.05,top:iconOffset});
		}
	});
};

frontpage.enter = function () {
	if (location.hash != '') state.commit();
	$("#frontpage .frontpage-button.game").clickOnce(frontpage.click.game);
	jss();
	UI.dismiss();
};

frontpage.click = {};
frontpage.click.game = function(){
	state.set('screen', 'game').validate();
};

// ==== Code métier pour le jeu
game = {};

game.jss = function(w, h, iconSize) {
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

game.enter = function () {
	if (!state.game) {
		var notAlreadyFetching = !runstate.gameFetched;
		runstate.gameFetched = function(data) {
			state.game = data;
			state.currentWordNb = 0;
			state.game.answers = [];
			state.commit();
			game.buildUi();
		};
		if (notAlreadyFetching) {
			UI.show("PtiClic", "Récupération de la partie");
			$.getJSON("getGame.php?callback=?", {
				user:"foo",
				passwd:"bar",
				nonce:Math.random()
			}, function(data) {
				var fn = runstate.gameFetched;
				runstate.gameFetched = false;
				fn(data);
			}).error(ajaxError);
		}
	} else {
		game.buildUi();
	}
	jss();
};

game.leave = function () {
	$("#game .relations").empty();
	$('#game #mn-caption').stop().clearQueue();
	if (runstate.gameFetched) runstate.gameFetched = function() {};
};

game.buildUi = function () {
	$("#game .relations").empty();
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
				game.nextWord({left:e.pageX, top:e.pageY}, this);
			})
			.appendTo("#game .relations");
	});
	game.updateText();
}

game.updateText = function() {
	$("#game .mn").text(state.game.cloud[state.currentWordNb].name);
	$("#game .mc").text(state.game.center.name);
	jss();
	UI.dismiss();
}

game.animateNext = function (click, button) {
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
	
	game.updateText();
	var fs = mn.css("fontSize");
	var mncbCenter = $("#game #mn-caption-block").center();
	
	(mn)
		.css("fontSize", 0)
		.animate({fontSize: fs}, {duration:duration, step:function(){mn.center(mncbCenter);}});
}

game.nextWord = function(click, button) {
	state.game.answers[state.currentWordNb++] = $(button).data("rid");
	if (state.currentWordNb < state.game.cloud.length) {
		game.animateNext(click, button);
		state.commit();
	} else {
		state.set('screen','score').validate();
	}
}

// ==== Code métier pour les scores
score = {};

score.jss = function(w, h, iconSize) {
	$(".screen")
		.css('text-align', 'center');
};

score.enter = function () {
	if (!state.hasScore) {
		var notAlreadyFetching = !runstate.scoreFetched;
		runstate.scoreFetched = function(data) {
			for (var i = 0; i < data.scores.length; ++i) {
				state.game.cloud[i].score = data.scores[i];
			}
			delete data.score;
			$.extend(state.game, data);
			state.hasScore = true;
			state.commit();
			score.ui();
		};
		if (notAlreadyFetching) {
			UI.show("PtiClic", "Calcul de votre score");
			$.getJSON("server.php?callback=?", {
				user: "foo",
				passwd: "bar",
				action: 1,
				pgid: state.game.pgid,
				gid: state.game.gid,
				answers: state.game.answers,
				nonce: Math.random()
			}, function(data){
				var fn = runstate.scoreFetched;
				runstate.scoreFetched = false;
				fn(data);
			}).error(ajaxError);
		}
	} else {
		score.ui();
	}
	jss();
}

score.leave = function () {
	if (runstate.scoreFetched) runstate.scoreFetched = function() {};
};

score.ui = function () {
	$("#score .scores").empty();
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
	});
	$("#score #jaivu").clickOnce(function() {
		state = new State().validate();
	});
	jss();
	UI.dismiss();
}
