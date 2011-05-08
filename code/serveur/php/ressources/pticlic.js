// ==== URL persistante
var nullFunction = function(){};

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
		
		$('#nojs').hide();

		$('#message:visible')
			.css({
				position: 'absolute',
				borderWidth: 'thin',
				borderStyle: 'solid',
				MozBorderRadius: 10,
				WebkitBorderRadius: 10,
				textAlign: 'center'
			})
			.fitFont(w/2, h*0.1)
			.css('max-width', w*0.6)
			.width(w*0.6)
			.center({left: w/2, top:h*0.1});
		
		$("#"+state.screen+".screen")
			.wh(w, h)
			.northWest({top:0,left:0});
		
		$("body, html")
			.css({
				padding: 0,
				margin: 0,
				overflow: "hidden",
				textAlign: "left"
			});
		
		$(".clearboth").css('clear', 'both');
		
		$(".screen").hide();
		$("#"+state.screen+".screen").show();
		
		if (window[state.screen] && window[state.screen].jss) window[state.screen].jss(w, h, iconSize);
		
		$("img.icon").each(function(i,e) {
			try {
			e=$(e);
			if (typeof(e.data('image')) != 'undefined')
				e.attr("src", "ressources/img/"+iconSize+"/"+e.data('image')+".png");
			} catch(e) {alert("Error anonymous in jss");alert(e);}
		});
		
		jssTheme(runstate.prefs.theme);
	} catch(e) {alert("Error jss");alert(e);}
}

function jssTheme(theme) {
	if (theme == "black") {
		var bg1 = "black";
		var fg1 = "white";
		var bg2 = "#222222";
		var fg2 = "#cccccc";
		var fg3 = "white";
		var hot = "#aaaaaa";
	} else {
		var bg1 = "#ffffe0";
		var fg1 = "black";
		var bg2 = "#f0f8d0";
		var fg2 = "#4a4";
		var fg3 = "#8b4";
		var hot = "yellow";
	}
	var splashbg = "black";
	var splashfg = "white";
	var screenbg = bg1;
	var screenfg = fg1;
	var messagebg = bg2;
	var messagefg = fg1;
	var messagebd = fg2;
	var centralfg = fg3;
	var cloudbg = bg2;
	var cloudfg = fg2;
	var cloudbd = fg2;
	var relationbg = bg2;
	var relationbd = fg2;
	var fphoverbg = bg2;
	var fphoverbd = fg2;
	var hotbg = hot;

	$('.screen').css({
		color: screenfg,
		backgroundColor: screenbg
	});

	$('html, body, #splash.screen').css({
		backgroundColor: splashbg,
		color: splashfg
	});

	$("#message").css({
		backgroundColor: messagebg,
		color: messagefg,
		borderColor: messagebd
	});

	$(".frontpage-button").hover(function() {
		$(this).css({
			backgroundColor: fphoverbg,
			outline: "medium solid "+fphoverbd
		});
	}, function() {
		$(this).css({
			outline: '',
			backgroundColor: "transparent"
		});
	});

	$('#mc-caption').css({color: centralfg});

	$('#mn-caption').css({color: cloudfg});

	$('#mn-caption-block').css({
		borderColor: cloudbd,
		backgroundColor: cloudbg
	});

	$('.relationBox').css({
		borderColor: relationbd,
		backgroundColor: relationbg
	});

	$('.relations .hot').css({backgroundColor: hotbg});

	$("a, a:visited").css({color: "#8888ff"});
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
			log: function(msg) {
				try {
					window.console && console.log(msg);
				} catch(e) {alert("Error UI().log");alert(e);}
			},
			info: function(title, msg) {
				try {
					alert(msg);
 				} catch(e) {alert("Error UI().info");alert(e);}
			},
			setScreen: function() {}
		};
	}
	} catch(e) {alert("Error UI");alert(e);}
}

function UIInfo(title, msg) {
	try {
		$('#message')
			.qCss('opacity',0)
			.qShow()
			.queue(function(next){
				try {
				$('#message')
					.text(msg);
				jss();
				next();
				} catch(e) {alert("Error anonymous in UIInfo");alert(e);}
			})
			.animate({opacity:0.9}, 700)
			.delay(5000)
			.animate({opacity:0}, 700);
	} catch(e) {alert("Error UI().info");alert(e);}
}

// ==== Code métier général
$(function() {
	try {
		$(window).resize(jss);
		$(window).hashchange(hashchange);
		hashchange();
		runstate.loaded = true;
	} catch(e) {alert("Error main function");alert(e);}
});

// ==== Asynchronous Javascript And Json.
ajaj = {};
ajaj.request = function(url, data, okFunction, smallErrorFunction, bigErrorFunction) {
	try {
		smallErrorFunction = smallErrorFunction || ajaj.smallError;
		bigErrorFunction = bigErrorFunction || ajaj.bigError;
		var user = UI().getPreference("user");
		var passwd = UI().getPreference("passwd");
		if (user != '' && passwd != '') {
			// TODO : on transfère le user/passwd à chaque fois ici… c'est pas très bon.
			data = $.extend({user:user, passwd:passwd}, data);
		}
		return $.getJSON(url, data, function(data) {
			try {
			if (data && data.isError) {
				smallErrorFunction(data);
			} else {
				okFunction(data);
			}
			} catch(e) {alert("Error anonymous in ajaj.request");alert(e);}
		}).error(bigErrorFunction);
	} catch(e) {alert("Error ajaj.request");alert(e);}
}
ajaj.smallError = function(x, ignoreConnect) {
	try {
		if (x.error == 10) {
			if (!ignoreConnect)
				state.set('screen', 'connection').commit().validate();
		} else {
			ajaj.error(
				"Erreur fatale. Merci de nous envoyer ce message : \n"
					+ "Erreur signalée par le serveur\n"
					+ "Code:"+x.error+"\n"
					+ "Message:"+x.msg+"\n"
			);
		}
	} catch(e) {alert("Error ajaj.smallError");alert(e);}
}
ajaj.bigError = function(x) {
	try {
		ajaj.error(
			"Erreur fatale. Merci de nous envoyer ce message : \n"
				+ "Erreur de transmission\n"
				+ "Code:"+x.status+"\n"
				+ "État:"+x.statusText+"\n"
				+ "Message:"+x.responseText.substring(0,20)+" ("+x.responseText.length+")"
		);
	} catch(e) {alert("Error ajaj.bigError");alert(e);}
}
ajaj.error = function(msg) {
	try {
		UI().dismiss();
		UI().info("Erreur !", msg);
	} catch(e) {alert("Error ajaj.error");alert(e);}
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
	try {
	// Si l'application est déjà chargée, on zappe directement jusqu'à la frontpage.
	if (runstate.loaded) {
		splash.click.goFrontpage();
	} else {
		jss();
		$('#splash.screen').clickOnce(splash.click.goFrontpage);
	}
	} catch(e) {alert("Error splash.enter");alert(e);}
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
		} catch(e) {alert("Error anonymous 1 in frontpage.jss");alert(e);}
	};
	var nbIcons = $fp(".icon").size();
	var nbRows = Math.ceil(nbIcons / 2)
	var ww = w - 2 * iconSize;
	var hh = h - nbRows * iconSize;
	var titleHeight = hh*0.4;
	var labelHeight = hh*0.4 / nbRows;
	var buttonPadding = hh*0.05/nbRows;
	var buttonHeight = labelHeight + iconSize + buttonPadding;
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
	$fp(".info .icon").data('image', 'aide');
	
	$fp(".frontpage-button")
		.css({
			textAlign: 'center',
			paddingTop: buttonPadding
		})
		.width(buttonWidth);
	
	$fp(".frontpage-button > div").css('display', 'block');
	
	var interIconSpace = (freeSpace - nbRows * buttonHeight) / (nbRows + 1);
	$fp(".frontpage-button").each(function(i,e){
		try {
		e=$(e);
		var currentRow = Math.floor(i/2);
		var currentColumn = i % 2;
		var iconOffset = titleHeight + ((currentRow+1) * interIconSpace) + (currentRow * buttonHeight);
		if (currentColumn == 0) {
			e.northEast({left:w/2-ww*0.05,top:iconOffset});
		} else {
			e.northWest({left:w/2+ww*0.05,top:iconOffset});
		}
		} catch(e) {alert("Error anonymous 2 in frontpage.jss");alert(e);}
	});
	} catch(e) {alert("Error frontpage.jss");alert(e);}
};

frontpage.enter = function () {
	try {
	if (location.hash != '') state.commit();
	$("#frontpage .frontpage-button.game").clickOnce(frontpage.click.goGame);
	$("#frontpage .frontpage-button.connection").clickOnce(frontpage.click.goConnection);
	$("#frontpage .frontpage-button.info").clickOnce(frontpage.click.goInfo);
	$("#frontpage .frontpage-button.prefs").clickOnce(frontpage.click.goPrefs);
	jss();
	UI().dismiss();
	} catch(e) {alert("Error frontpage.enter");alert(e);}
};

frontpage.click = {};
frontpage.click.goGame = function(){
	try {
	state.set('screen', 'game').validate();
	} catch(e) {alert("Error frontpage.click.goGame");alert(e);}
};

frontpage.click.goConnection = function() {
	try {
		UI().show("PtiClic", "Chargement…");
		state.set('screen', 'connection').commit().validate();
	} catch(e) {alert("Error frontpage.click.goConnection");alert(e);}
};

frontpage.click.goInfo = function() {
	try {
		UI().show("PtiClic", "Chargement…");
		state.set('screen', 'info').commit().validate();
	} catch(e) {alert("Error frontpage.click.goInfo");alert(e);}
};

frontpage.click.goPrefs = function() {
	try {
		UI().show("PtiClic", "Chargement…");
		state.set('screen', 'prefs').commit().validate();
	} catch(e) {alert("Error frontpage.click.goPrefs");alert(e);}
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
		.css({
			borderWidth: h/100,
			borderStyle: 'solid none'
		})
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
			WebkitBorderRadius: 10,
			borderWidth: 'thin',
			borderStyle: 'solid',
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
		var notAlreadyFetching = !runstate.gameFetched || runstate.gameFetched == nullFunction;
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
			ajaj.request("getGame.php?callback=?", {
				nonce:Math.random()
			}, function(data) {
				try {
				var fn = runstate.gameFetched;
				runstate.gameFetched = false;
				fn(data);
				} catch(e) {alert("Error anonymous 2 in game.enter");alert(e);}
			});
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
	if (runstate.gameFetched) runstate.gameFetched = nullFunction;
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
				try {
				game.nextWord({left:e.pageX, top:e.pageY}, this);
				} catch(e) {alert("Error anonymous 2 click in game.buildUi");alert(e);}
			})
			.appendTo("#game .relations");
		} catch(e) {alert("Error anonymous 1 in game.buildUi");alert(e);}
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
	$("#score.screen")
		.css('text-align', 'center');
	} catch(e) {alert("Error score.jss");alert(e);}
};

score.enter = function () {
	try {
	if (!state.hasScore) {
		var notAlreadyFetching = !runstate.scoreFetched || runstate.scoreFetched == nullFunction;
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
			ajaj.request("server.php?callback=?", {
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
			});
		}
	} else {
		score.ui();
	}
	jss();
	} catch(e) {alert("Error score.enter");alert(e);}
}

score.leave = function () {
	try {
	if (runstate.scoreFetched) runstate.scoreFetched = nullFunction;
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

// ==== Code métier pour la page d'info
info = {};

info.jss = function(w,h,iconSize) {
	try {
	$("#info-back-p").css('text-align', 'center');
	$("#info.screen .container input").css('font-size', 'inherit');
	$("#info.screen .container")
		.fitFont(w*0.9, h*0.9, null, null, true)
		.center($("#info.screen"));
	} catch(e) {alert("Error info.jss");alert(e);}
}

info.enter = function() {
	try {
		$("#info-back").clickOnce(info.click.goBack);
		jss();
		UI().dismiss();
	} catch(e) {alert("Error info.enter");alert(e);}
};

info.click = {};
info.click.goBack = function(){
	try {
		state.set('screen', 'frontpage').validate();
	} catch(e) {alert("Error anonymous in info.enter");alert(e);}
};

// ==== Code métier pour la connexion
connection = {};

connection.jss = function(w, h, iconSize) {
	try {
		var c = $("#connection.screen");
		var $c = function() {
			try {
				return c.find.apply(c,arguments);
			} catch(e) {alert("Error anonymous 1 in connection.jss");alert(e);}
		};
		
		$c("input, label")
			.fitFont(w*0.45, h*0.06, null, null, true, true);
		$c("#user-label").east({left:w*0.475,top:h*0.25});
		$c("#user").west({left:w*0.525,top:h*0.25});
		$c("#passwd-label").east({left:w*0.475,top:h*0.5});
		$c("#passwd").west({left:w*0.525,top:h*0.5});
		$c("#connect").center({left:w/2,top:h*0.75});
	} catch(e) {alert("Error connection.jss");alert(e);}
};

connection.enter = function() {
	try {
		$("#connect-form").unbind("submit", connection.connect).submit(connection.connect);
		jss();
		UI().dismiss();
	} catch(e) {alert("Error connection.enter");alert(e);}
};

connection.connect = function() {
	try {
		UI().setPreference("user", $("#user").val());
		UI().setPreference("passwd", $("#passwd").val());
		ajaj.request("server.php?callback=?", {
			action: 7,
			user: $("#user").val(),
			passwd: $("#passwd").val(),
		}, connection.connectFetched, connection.connectFetched);
		return false;
	} catch(e) {alert("Error connection.connect");alert(e);}
}

connection.connectFetched = function(data) {
	try {
		if (data && data.theme) {
			prefs.loadPrefs();
			UIInfo("Connexion", "Vous êtes connecté !");
		} else if (data && data.isError && data.error == 3) {
			prefs.loadPrefs();
			UIInfo("Connexion", data.msg);
		} else {
			prefs.loadPrefs();
			ajaj.smallError(data);
		}
		state.set('screen', 'frontpage').validate();
	} catch(e) {alert("Error connection.connectFetched");alert(e);}
}

// ==== Code métier pour la page de configuration
prefs = {};

prefs.jss = function(w,h,iconSize) {
	try {
		var p = $("#prefs.screen");
		var $p = function() {
			try {
				return p.find.apply(p,arguments);
			} catch(e) {alert("Error anonymous 1 in prefs.jss");alert(e);}
		};
		
		$p("input, label")
			.fitFont(w*0.45, h*0.06, null, null, true, true);
		$p("legend")
			.fitFont(w*0.3, h*0.05, null, null, true, true);
		$p("#theme").center({left:w*0.5,top:h*0.25});
		$p("#prefs-cancel").east({left:w*0.475,top:h*0.5});
		$p("#prefs-apply").west({left:w*0.525,top:h*0.5});
	} catch(e) {alert("Error prefs.jss");alert(e);}
}

prefs.enter = function() {
	try {
		$("#prefs-form").unbind('submit', prefs.apply).submit(prefs.apply);
		$("#prefs-cancel").clickOnce(prefs.cancel);
		$("#prefs-form input:radio[name=theme]").attr('checked', function(i,val) {
			return $(this).val() == runstate.prefs.theme;
		});
		jss();
		UI().dismiss();
	} catch(e) {alert("Error prefs.enter");alert(e);}
};

prefs.apply = function(){
	try {
		var newtheme = $("#prefs-form input:radio[name=theme]:checked").val();
		ajaj.request("server.php?callback=?", {
			action: 8,
			key: 'theme',
			value: newtheme
		}, function(data) {
			try {
			if (data.theme) {
				UIInfo("Préférences", "Les préférences ont été enregistrées.");
				prefs.loadPrefs(data);
			} else {
				UIInfo("Préférences", "Les préférences n'ont pas pu être enregistrées.");
			}
			} catch(e) {alert("Error anonymous in prefs.apply");alert(e);}
		});
		state.set('screen', 'frontpage').validate();
		return false;
	} catch(e) {alert("Error anonymous in prefs.apply");alert(e);}
};

prefs.cancel = function(){
	try {
		state.set('screen', 'frontpage').validate();
	} catch(e) {alert("Error anonymous in prefs.cancel");alert(e);}
};

prefs.loadPrefs = function(data) {
	try {
		console.log('loadPrefs');
		if (data && data.theme) {
			runstate.prefs = data;
		} else {
			runstate.prefs = {
				theme: "green"
			};
		}
		if (runstate.loaded) jss();
	} catch(e) {alert("Error anonymous in prefs.loadPrefs");alert(e);}
};