// ==== URL persistante
function State(init) {
	try {
	$.extend(this, init || {});
	if (!this.screen) this.screen = 'splash';
	} catch(e) {alert("Error State");alert(e);}
};
var futureHashChange = null;
State.prototype.commit = function() {
	try {
		futureHashChange = "#"+encodeURI('"'+$.JSON.encode(this));
		location.hash = futureHashChange;
		return this;
	} catch(e) {alert("Error State.prototype.commit");alert(e);}
};
State.prototype.get = function(key) {
	try {
	return this[key];
	} catch(e) {alert("Error State.prototype.get");alert(e);}
};
State.prototype.set = function(key, value) {
	try {
	this[key] = value;
	return this;
	} catch(e) {alert("Error State.prototype.set");alert(e);}
};
State.prototype.validate = function () {
	try {
    state = this;
    UI().setScreen(this.screen);
    if (oldScreen != this.screen) {
	if (window[oldScreen] && window[oldScreen].leave) window[oldScreen].leave();
	oldScreen = this.screen;
    }
    if (window[this.screen] && window[this.screen].enter) window[this.screen].enter();
    return this;
	} catch(e) {alert("Error State.prototype.validate");alert(e);}
};

var runstate = {};
var state;
var oldScreen = '';
var ui = {};
function hashchange() {
	try {
		if (futureHashChange === location.hash) {
			futureHashChange = null;
		} else {
            var stateJSON = location.hash.substring(location.hash.indexOf("#") + 1);
            if (stateJSON.charAt(0) != '"') { stateJSON = decodeURI(stateJSON); }
            stateJSON = stateJSON.substring(1);
            state = new State($.parseJSON(stateJSON || '{}')).validate();
		}
	} catch(e) {alert("Error hashchange");alert(e);}
}

// ==== JavaScript Style général
function jss() {
	try {
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
		
		$("img.icon").each(function(i,e) {
			e=$(e);
			if (typeof(e.data('image')) != 'undefined')
				e.attr("src", "ressources/img/"+iconSize+"/"+e.data('image')+".png");
		});
	} catch(e) {alert("Error jss");alert(e);}
}

// ==== Interface Android
function UI () {
	try {
	if (typeof(PtiClicAndroid) != "undefined") {
		return PtiClicAndroid;
	} else {
		return {
			isAndroid: function() { return false; },
			setPreference: function() {},
			getPreference: function() {return "";},
			show: function(title, text) {},
			dismiss: function() {},
			exit: function() {},
			log: function(msg) { window.console && console.log(msg); },
			setScreen: function() {}
		};
	}
	} catch(e) {alert("Error UI");alert(e);}
}

// ==== Code métier général
$(function() {
	try {
	$(window).resize(jss);
	$(window).hashchange(hashchange);
	hashchange();
	} catch(e) {alert("Error main function");alert(e);}
});

function ajaxError(x) {
	try {
		UI().dismiss();
		var msg = "Erreur fatale. Merci de nous envoyer ce message : ";
		msg += x.status+" - "+x.statusText+"\n"+x.responseText.substring(0,20)+((x.responseText == '') ? '': '…');
		alert(msg);
		UI().exit();
	} catch(e) {alert("Error ajaxError");alert(e);}
}

// ==== Code métier pour le splash

splash = {};

splash.jss = function(w,h,iconSize) {
	try {
		var splashW = 320;
		var splashH = 480;
		var ratio = Math.min(w / splashW, h / splashH);
		$('#splash.screen img')
			.wh(splashW * ratio, splashH * ratio)
			.center($('#splash.screen').center());
	} catch(e) {alert("Error splash.jss");alert(e);}
}

splash.enter = function() {
	// Si l'application est déjà chargée, on zappe directement jusqu'à la frontpage.
	if (runstate.skipSplash) {
		splash.click.goFrontpage();
	} else {
		runstate.skipSplash = true;
		jss();
		$('#splash.screen').clickOnce(splash.click.goFrontpage);
	}
}

splash.click = {};
splash.click.goFrontpage = function() {
	try {
		UI().show("PtiClic", "Chargement…");
		state.set('screen', 'frontpage').validate();
	} catch(e) {alert("Error splash.click.goFrontpage");alert(e);}
};

// ==== Code métier pour la frontpage
frontpage = {};

frontpage.jss = function(w, h, iconSize) {
	try {
	var fp = $("#frontpage.screen");
	var $fp = function() {
		try {
			return fp.find.apply(fp,arguments);
		} catch(e) {alert("Error anonymous in frontpage.jss");alert(e);}
	};
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
	
	$fp(".game .icon").data('image', 'mode_normal');
	$fp(".prefs .icon").data('image', 'config');
	$fp(".connection .icon").data('image', 'config');
	$fp(".about .icon").data('image', 'aide');
	
	$fp(".frontpage-button")
		.css('text-align', 'center')
		.width(buttonWidth);
	
	$fp(".frontpage-button > div").css('display', 'block');
	
	$fp(".frontpage-button").each(function(i,e){
		try {
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
		} catch(e) {alert("Error anonymous in frontpage.jss");alert(e);}
	});
	} catch(e) {alert("Error frontpage.jss");alert(e);}
};

frontpage.enter = function () {
	try {
	if (location.hash != '') state.commit();
	$("#frontpage .frontpage-button.game").clickOnce(frontpage.click.game);
	jss();
	UI().dismiss();
	} catch(e) {alert("Error frontpage.enter");alert(e);}
};

frontpage.click = {};
frontpage.click.game = function(){
	try {
	state.set('screen', 'game').validate();
	} catch(e) {alert("Error frontpage.click.game");alert(e);}
};

// ==== Code métier pour le jeu
game = {};

game.jss = function(w, h, iconSize) {
	try {
	var g = $("#game.screen");
	var $g = function() {
		try {
		return g.find.apply(g,arguments);
		} catch(e) {alert("Error anonymous in game.jss");alert(e);}
	};
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
	} catch(e) {alert("Error game.jss");alert(e);}
};

game.enter = function () {
	try {
	if (!state.game) {
		var notAlreadyFetching = !runstate.gameFetched;
		runstate.gameFetched = function(data) {
			try {
			state.game = data;
			state.currentWordNb = 0;
			state.game.answers = [];
			state.commit();
			game.buildUi();
			} catch(e) {alert("Error anonymous 1 in game.enter");alert(e);}
		};
		if (notAlreadyFetching) {
			UI().show("PtiClic", "Récupération de la partie");
			$.getJSON("getGame.php?callback=?", {
				user:"foo",
				passwd:"bar",
				nonce:Math.random()
			}, function(data) {
				try {
				var fn = runstate.gameFetched;
				runstate.gameFetched = false;
				fn(data);
				} catch(e) {alert("Error anonymous 2 in game.enter");alert(e);}
			}).error(ajaxError);
		}
	} else {
		game.buildUi();
	}
	} catch(e) {alert("Error game.enter");alert(e);}
};

game.leave = function () {
	try {
	$("#game .relations").empty();
	$('#game #mn-caption').stop().clearQueue();
	if (runstate.gameFetched) runstate.gameFetched = function() {};
	} catch(e) {alert("Error game.leave");alert(e);}
};

game.buildUi = function () {
	try {
	$("#game .relations").empty();
	$.each(state.game.relations, function(i, relation) {
		try {
		$('#templates .relationBox')
			.clone()
			.data("rid", relation.id)
			.find(".text")
				.html(relation.name.replace(/%(m[cn])/g, '<span class="$1"/>'))
			.end()
			.find(".icon")
				.data("image",relation.id)
			.end()
			.click(function(e) {
				game.nextWord({left:e.pageX, top:e.pageY}, this);
			})
			.appendTo("#game .relations");
		} catch(e) {alert("Error anonymous in game.buildUi");alert(e);}
	});
	game.updateText();
	} catch(e) {alert("Error game.buildUi");alert(e);}
}

game.updateText = function() {
	try {
	$("#game .mn").text(state.game.cloud[state.currentWordNb].name);
	$("#game .mc").text(state.game.center.name);
	jss();
	UI().dismiss();
	} catch(e) {alert("Error game.updateText");alert(e);}
}

game.animateNext = function (click, button) {
	try {
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
		.queue(function() {
			try {
			$(this).remove();
			} catch(e) {alert("Error anonymous 1 in game.animateNext");alert(e);}
		});

	game.updateText();
	var fs = mn.css("fontSize");
	var mncbCenter = $("#game #mn-caption-block").center();
	
	(mn)
		.css("fontSize", 0)
		.animate({fontSize: fs}, {duration:duration, step:function(){
			try {
			mn.center(mncbCenter);
			} catch(e) {alert("Error anonymous 2 in game.animateNext");alert(e);}
		}});
	} catch(e) {alert("Error game.animateNext");alert(e);}
}

game.nextWord = function(click, button) {
	try {
	state.game.answers[state.currentWordNb++] = $(button).data("rid");
	if (state.currentWordNb < state.game.cloud.length) {
		game.animateNext(click, button);
		state.commit();
	} else {
		state.set('screen','score').validate();
	}
	} catch(e) {alert("Error game.nextWord");alert(e);}
}

// ==== Code métier pour les scores
score = {};

score.jss = function(w, h, iconSize) {
	try {
	$(".screen")
		.css('text-align', 'center');
	} catch(e) {alert("Error score.jss");alert(e);}
};

score.enter = function () {
	try {
	if (!state.hasScore) {
		var notAlreadyFetching = !runstate.scoreFetched;
		runstate.scoreFetched = function(data) {
			try {
			for (var i = 0; i < data.scores.length; ++i) {
				state.game.cloud[i].score = data.scores[i];
			}
			delete data.score;
			$.extend(state.game, data);
			state.hasScore = true;
			state.commit();
			score.ui();
			} catch(e) {alert("Error anonymous 1 in score.enter");alert(e);}
		};
		if (notAlreadyFetching) {
			UI().show("PtiClic", "Calcul de votre score");
			$.getJSON("server.php?callback=?", {
				user: "foo",
				passwd: "bar",
				action: 1,
				pgid: state.game.pgid,
				gid: state.game.gid,
				answers: state.game.answers,
				nonce: Math.random()
			}, function(data){
				try {
				var fn = runstate.scoreFetched;
				runstate.scoreFetched = false;
				fn(data);
				} catch(e) {alert("Error anonymous 2 in score.enter");alert(e);}
			}).error(ajaxError);
		}
	} else {
		score.ui();
	}
	jss();
	} catch(e) {alert("Error score.enter");alert(e);}
}

score.leave = function () {
	try {
	if (runstate.scoreFetched) runstate.scoreFetched = function() {};
	$("#score .scores").empty();
	$("#templates .scoreTotal").empty();
	} catch(e) {alert("Error score.leave");alert(e);}
};

score.ui = function () {
	try {
	$("#score .scores").empty();
	$("#score .scoreTotal")
		.text(state.game.scoreTotal)
		.goodBad(state.game.minScore*state.game.cloud.length,state.game.maxScore*state.game.cloud.length,{r:255,g:0,b:0},{r:0,g:192,b:0});
	$.each(state.game.cloud, function(i,e) {
		try {
		$("#templates .scoreLine")
			.clone()
			.find(".word")
				.text(e.name)
			.end()
			.find(".score")
				.text(e.score)
				.goodBad(state.game.minScore,state.game.maxScore,{r:255,g:0,b:0},{r:0,g:192,b:0})
			.end()
			.appendTo("#score .scores");
		} catch(e) {alert("Error anonymous 1 in score.ui");alert(e);}
	});
	$("#score #jaivu").clickOnce(score.click.jaivu);
	jss();
	UI().dismiss();
	} catch(e) {alert("Error score.ui");alert(e);}
}

score.click = {};
score.click.jaivu = function() {
	try {
		state = new State().validate();
	} catch(e) {alert("Error score.click.jaivu");alert(e);}
};