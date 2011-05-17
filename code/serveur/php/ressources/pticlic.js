// ==== URL persistante
var nullFunction = function(){};
var futureHashChange = null;
var runstate = {
	screen: 'none',
};
var state = decodeHash("");
var oldstate = decodeHash("");

$.screen = function (name) {
	return $(document.getElementById(name)).filter('.screen');
}

function hashchange() {
	oldstate = state;
	state = decodeHash(location.hash);
	$.screen(state.screen).trigger(state.screen != runstate.screen ? "goto" : "update");
}

function init(fn) {
	$(window).queue('init', function(next) {fn(); next();});
}

// ==== Code métier général
$(function() {
	$(window).dequeue('init');
	$(window).resize(jss);
	$(window).hashchange(hashchange);
	hashchange();
	jss();
	runstate.loaded = true;
});

// ==== Nouveau jss
function jss() {
	try {
		if ($("#splash img").is(':visible')) {
			var ratio = Math.min($('#splash').width() / 320, $('#splash').height() / 480);
			$('#splash.screen img')
				.wh(320 * ratio, 480 * ratio);
		}
		if ($('#game.screen').is(':visible')) {
			var iconSize = 72;
			var rel = $('#game.screen .relations');
			var rb = rel.find('.relationBox');
			rb.css({
				borderWidth: ({72:3,48:2,36:1})[iconSize],
				padding: 10/72*iconSize,
				borderRadius: 20/72*iconSize,
			}).height(iconSize);
			rb.css({ marginTop: (rel.height() - rb.sumOuterHeight()) / (rb.size() + 1) });
			rb.find('.icon').css({paddingRight: 10/72*iconSize});
		}
		$('.iconFitParent').wh(0,0).each(function(i,e) {
			e=$(e);
			var p = e.parent();
			var size = Math.min(p.width(), p.height());
			if (size >= 72) { e.wh(72); }
			else if (size >= 48) e.wh(48);
			else if (size >= 36) e.wh(36);
			else e.wh(0);
		});
		$('.fitFont:visible').$each(function(i,e) { e.fitFont(); });
		$('.fitFontGroup:visible').each(function(i,e) { $(e).find('.subFitFont').fitFont(); });
		$('.center:visible').$each(function(i,e) { e.center(e.parent().center()); });
	} catch(e) {alert("Error jss");alert(e);}
}

// ==== Passage d'un écran à l'autre

init(function() {
	$('.screen').live('goto', function() {
		var screen = this.id;
		if (screen == '') return;
		// Afficher "Chargement…"
		/* location.hash = "#" + screen; */
		$.screen(runstate.screen).trigger('leave').hide();
		runstate.screen = screen;
		$(this).trigger('pre-enter');
	});
	
	$('.screen').live('pre-enter', function() {
		$(this).trigger('enter');
	});
	
	$('.screen').live('enter', function() {
		$(this).show();
		jss();
	});
	
	$('.screen').live('leave', function() {
		$(this).hide();
	});
});

// ==== Bulle pour les messages
init(function() {
	$('#message').hide();
});

function message(title, msg) {
	try {
		$('#message')
			.qCss('opacity',0).qShow()
			.queue(function(next){ $('#message .text').text(msg); jss(); next(); })
			.fadeTo(700, 0.9).delay(5000).fadeOut(700);
	} catch(e) {alert("Error UI().info");alert(e);}
}

// ==== Écran splash
init(function() {
	$('#splash.screen').click(function(){ $('#frontpage').trigger('goto'); });
	$('#splash.screen').bind('goto', function(e){
		if (runstate.loaded) {
			$('#frontpage').trigger('goto');
			return false;
		}
	});
	
});

// ==== Écran game
runstate.gameCache = new Cache(function(k, dfd, cache) {
	$.getJSON("getGame.php?callback=?", {pgid:k}, function(data) {
		if (data.error == 10) {
			$('#connection.screen').trigger('goto');
		} else if (data.isError) {
			location.hash = "#frontpage";
			message("Erreur", "Une erreur est survenue, veuillez nous en excuser.");
		} else {
			cache.alias(data.pgid, k);
			dfd.resolve(data);
		}
	});
});

init(function() {
	var game = $('#game.screen');
	$('a[href="#game"]').click(function() {
		location.hash = '#game/-' + $.now();
		return false;
	});

	game.bind('pre-enter', function() {
		runstate.gameCache.get(state.pgid).done(function(data) {
			runstate.game = data
			if (runstate.screen == 'game') { game.trigger('enter'); }
		});
		return false;
	});

	game.bind('enter', function() {
		$("#game .relations").empty();
		var game = runstate.game;
		$.each(game.relations, function(i, relation) {
			$('#templates .relationBox')
				.clone()
				.find(".text").html(relation.name.replace(/%(m[cn])/g, '<span class="$1"/>')).end()
				.find(".icon").data("image",relation.id).end()
				.click(function(e) {
					state.pgid = game.pgid;
					state.answers.push(relation.id);
					location.hash = encodeHash(state);
					$(this).addClass("hot").removeClass("hot", 1000);
/*					try {
						game.nextWord({left:e.pageX, top:e.pageY}, this);
					} catch(e) {alert("Error anonymous 2 click in game.buildUi");alert(e);}*/
				})
				.appendTo("#game .relations");
		});
		$("#game .mn").text(game.cloud[state.answers.length].name);
		$("#game .mc").text(game.center.name);
	});

	game.bind('update', function(e) {
		$("#game .mn").text(runstate.game.cloud[state.answers.length].name);
		jss();
		console.log('update');
	});
});


game = {};
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


/*function State(init) {
	try {
	$.extend(this, init || {});
	if (!this.screen) this.screen = 'splash';
	} catch(e) {alert("Error State");alert(e);}
};
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
};*/

function _hashchange() {
	try {
		if (futureHashChange !== location.hash) {
            state = decodeHash(location.hash);
			// Appliquer le changement de screen etc.
		}
	} catch(e) {alert("Error hashchange");alert(e);}
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

// ==== Code métier pour les scores
score = {};

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
			message("Connexion", "Vous êtes connecté !");
		} else if (data && data.isError && data.error == 3) {
			prefs.loadPrefs();
			message("Connexion", data.msg);
		} else {
			prefs.loadPrefs();
			ajaj.smallError(data);
		}
		state.set('screen', 'frontpage').validate();
	} catch(e) {alert("Error connection.connectFetched");alert(e);}
}

// ==== Code métier pour la page de configuration
prefs = {};

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
				message("Préférences", "Les préférences ont été enregistrées.");
				prefs.loadPrefs(data);
			} else {
				message("Préférences", "Les préférences n'ont pas pu être enregistrées.");
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