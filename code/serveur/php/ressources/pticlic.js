// ==== Code métier général
function init(fn) {
	$(window).queue('init', function(next) {fn(); next();});
}

$.ajaj = function(url, data, dfd, retryCheck, callback) {
	var user = '' + UI().getPreference("user");
	var passwd = '' + UI().getPreference("passwd");
	user = runstate.user || user;
	passwd = runstate.passwd || passwd;
	if (user && passwd) {
		if (!data.user) data.user = user;
		if (!data.passwd) data.passwd = passwd;
	}
	var fromScreen = state.screen;
	if (callback) return $.getJSON(url, data, callback);
	return $.getJSON(url, data, function(data) {
		if (data.isError) {
			isConnected(false);
			dfd.reject(data);
			message("Erreur", data.msg);
			if ((data.error == 10 || data.error == 3) && state.screen == fromScreen && (!retryCheck || retryCheck())) {
				$.screen('connection').trigger('goto');
			} else if (runstate.screen == fromScreen) {
				$.screen('frontpage').trigger('goto');
			}
		} else {
			isConnected(true);
			dfd.resolve(data);
		}
	}).fail(function(data) {
		isConnected(false);
		dfd.reject(data);
		$("#frontpage").trigger('goto');
		message("Erreur", "Une erreur est survenue, veuillez nous en excuser.");
	});
};

$(function() {
	var lastWinSize = $(window).wh();
	$(window).dequeue('init');
	$(window).resize($.debounce(function resizeJSS() {
		if (lastWinSize.width != $(window).width() || lastWinSize.height != $(window).height()) {
			lastWinSize = $(window).wh();
			hashchange();
		}
	}));
	$(window).hashchange(hashchange);
	hashchange();
	runstate.loaded = true;
});

// ==== URL persistantes et passage d'un écran à l'autre
var runstate = { screen: 'none' };
var state = decodeHash("");
var oldstate = decodeHash("");

init(function() {
	$('.screen').live('goto', function() {
		var screen = this.id;
		if (screen == '') return;
		// Afficher "Chargement…"
		/* location.hash = "#" + screen; */
		$.screen(runstate.screen).trigger('leave').hide();
		runstate.screen = screen;
		UI().setScreen(screen);
		$(this).trigger('pre-enter');
	});
	
	$('.screen').live('pre-enter', function() {
		$(this).trigger('enter');
	});
	
	$('.screen').live('enter', function() {
		$(this).show();
		$(this).trigger('update');
	});

	$('.screen').live('update', function() {
		jss();
	});
	
	$('.screen').live('leave', function() {
		$(this).hide();
	});
});

$.screen = function (name) {
	return $(document.getElementById(name)).filter('.screen');
}

function hashchange() {
	oldstate = state;
	state = decodeHash(location.hash);
	$.screen(state.screen).trigger(state.screen != runstate.screen ? "goto" : "update");
}

// ==== Interface Android™
function UI () {
	if (typeof(PtiClicAndroid) != "undefined") {
		return PtiClicAndroid;
	} else {
		return {
			isAndroid: function() { return false; },
			setPreference: function() {},
			getPreference: function() {return "";},
			setScreen: function() {}
		};
	}
}

// ==== Nouveau jss
function jss() {
	var iconSize = 0;
	if (jss.running) return;
	jss.running = true;
	$('body').removeClass().addClass(runstate.prefs.theme);
	if ($("#splash img").is(':visible')) {
		var ratio = Math.min($('#splash').width() / 320, $('#splash').height() / 480);
		$('#splash.screen img')
			.wh(320 * ratio, 480 * ratio);
	}
	if ($('#game.screen').is(':visible')) {
		var rb = $('#game.screen .relationBox');
		var screenHeight = $('#game.screen').height();
		var nbRb = rb.size();
		
		var calcFreeSpace = function (iconSize) {
			var rbHeight = (iconSize || 20) + 2*({72:3,48:2,36:1,0:1})[iconSize] + Math.ceil(2*10/72*iconSize); // = iconSize + border + padding
			return screenHeight - rbHeight*nbRb;
		};
		$.each([72,48,36,0], function(i,is) {
			iconSize = is;
			return (calcFreeSpace(is) - (nbRb+1)*5 < Math.max(/*Hauteur min du header :*/70, screenHeight*0.2));
		});
		var freeSpace = calcFreeSpace(iconSize);
		var headerHeight = Math.min(screenHeight * 0.35, freeSpace - (nbRb+1)*5);
		var interRbSpace = (freeSpace - headerHeight) / (nbRb+1);

		$('#game .header').height(headerHeight);
		$('#game .relations').height(screenHeight - headerHeight).css('top', headerHeight);
		(rb)
			.css({
				borderWidth: ({72:3,48:2,36:1,0:1})[iconSize],
				padding: 10/72*iconSize,
				borderRadius: 20/72*iconSize,
				marginTop: interRbSpace
			})
			.height(iconSize)
			.find('img.icon').wh(iconSize).css({paddingRight: 10/72*iconSize});
	}
	$('#frontpage:visible a.fpButton').$each(function(i,e) {
		var img = e.find('img');
		var size = Math.min($('#frontpage').width() * 0.3, $('#frontpage').height() * 0.32 * 0.5);
		if (size >= 36) iconSize = 36;
		if (size >= 48) iconSize = 48;
		if (size >= 72) iconSize = 72;
		e.find('.icon-label').height($('#frontpage').height() * 0.32 * 0.3);
		img.css('padding-top', $('#frontpage').height() * 0.32 * 0.06);
	});
	$('.fitFont:visible').$each(function(i,e) { e.fitFont(); });
	$('.fitFontGroup:visible').each(function(i,e) { $(e).find('.subFitFont').fitFont(); });
	$('.center:visible').$each(function(i,e) { e.center(e.parent().center()); });
	$('img.icon:visible').$each(function(i,e){ if(e.data('image') && iconSize) e.attr('src', 'ressources/img/'+iconSize+'/'+e.data('image')+'.png'); e.wh(iconSize); });
	if ($('#game.screen').is(':visible')) {
		$('#game.screen').trigger('update');
	}
	jss.running = false;
}

// ==== Bulle pour les messages
init(function() {
	$('#message').hide();
});

function message(title, msg) {
	$('#message')
		.qCss('opacity',0).qShow()
		.queue(function(next){ $('#message .text').text(msg || 'Une erreur est survenue, veuillez nous en excuser.'); jss(); next(); })
		.fadeTo(700, 0.9).delay(5000).fadeOut(700);
}

// ==== Écran splash
init(function() {
	$('#splash.screen').click(function(){ $('#frontpage').trigger('goto'); return false; });
	window.setTimeout(function() {
		if (runstate.screen == 'splash') $('#frontpage').trigger('goto');
	}, 5000);
	$('#splash.screen').bind('goto', function(e){
		if (runstate.loaded) {
			$('#frontpage').trigger('goto');
			return false;
		}
	});
	
});

// ==== Écran d'accueil
init(function() {
	$.screen('frontpage').bind('enter', function() {
		window.document.title = "PtiClic pre-alpha 0.2";
		if (runstate.pendingSetPrefs) runstate.pendingSetPrefs();
	});
	$.screen('frontpage').bind('update', function() {
		$('.dis-connect').text(isConnected() ? "Déconnexion" : "Connexion");
	});
	if (UI().isAndroid()) $('#back2site').hide();
	$('#frontpage a.fpButton').$each(function(i,e) {
		e.find('img.icon').data('image', e.attr('href').substring(1));
	});
});

// ==== Écran connexion
function isConnected(arg) {
	if (typeof arg == 'undefined') {
		return !!runstate.connected;
	} else {
		runstate.connected = !!arg;
		if (runstate.screen == 'frontpage') $.screen('frontpage').trigger('update');
	}
}

init(function() {
	$('#connection.screen form').submit(function() {
		runstate.user = $('#user').val();
		runstate.passwd = $('#passwd').val();
		UI().setPreference('user', runstate.user);
		UI().setPreference('passwd', runstate.passwd);
		if (!runstate.pendingSetPrefs) {
			runstate.pendingGetPrefs();
		}
		if (state.screen == 'game') {
			$('#game').trigger('goto');
		} else if (state.screen == 'score') {
			$('#score').trigger('goto');
		} else if (state.screen == "frontpage") {
			$.screen('frontpage').trigger('goto'); // Ne devrait jamais être appellé.
		} else {
			location.hash = "#frontpage";
		}
		return false;
	});

	$('#connection.screen .back').click(function() {
		if (runstate.pendingSetPrefs)
			runstate.cancelPendingSetPrefs();
		if (state.screen == 'frontpage') $.screen('frontpage').trigger('goto');
	});
	
	$('a[href="#connection"]').click(function() {
		if (isConnected()) {
			UI().setPreference("user", '');
			UI().setPreference("passwd", '');
			runstate.user = '';
			runstate.passwd = '';
			$.ajaj("server.php?callback=?", { action: 9 }, null, null, function(data) {
				isConnected(false);
				message("Succès", "Vous êtes déconnecté.");
				loadPrefs({theme:"green"});
			});
			return false;
		}
	});
});

// ==== Écran game
runstate.gameCache = new Cache(function getGame(k, dfd) {
	$.ajaj("getGame.php?callback=?", {pgid:k}, dfd, function() { return state.pgid == k; });
});

init(function() {
	var game = $('#game.screen');
	$('a[href="#game"]').click(function() {
		location.hash = '#game/' + $.now();
		return false;
	});

	game.bind('pre-enter', function() {
		runstate.gameCache.get(state.pgid).done(function(data) {
			runstate.game = data;
			game.trigger('enter');
		});
		return false;
	});

	game.bind('enter', function() {
		$("#game .relations").empty();
		var game = runstate.game;
		runstate.relationBox = [];
		$.each(game.relations, function(i, relation) {
			runstate.relationBox[relation.id] = $('#templates .relationBox')
				.clone()
				.find(".text").html(relation.name.replace(/%(m[cn])/g, '<span class="$1"/>')).end()
				.find(".icon").data("image",relation.id).end()
				.click(function(e) {
					var h = appendAnswer(state, relation.id);
					if (state.answers.length + 1 >= runstate.game.cloud.length) {
						location.hash = encodeHash($.extend(h, {screen:'score'}));
					} else {
						location.hash = encodeHash(h);
						$(this).addClass("hot");
					}
					return false;
				})
				.appendTo("#game .relations");
		});
		$("#game .mc").text(game.center.name);
	});

	var updating = false;
	game.bind('update', function() {
		if (updating) return false;
		updating = true;
		if (!runstate.game || state.pgid != runstate.game.pgid) {
			updating = false;
			$('#game').trigger('goto');
			return;
		}

		window.document.title = "PtiClic "+(state.answers.length + 1)+' / '+runstate.game.cloud.length;
		$('.mn').text(runstate.game.cloud[state.answers.length].name);
		jss();
		
		var isForward = (state.answers.length - oldstate.answers.length) >= 0;
		var rb = runstate.relationBox[(isForward ? state : oldstate).answers[(isForward ? state : oldstate).answers.length - 1]];

		if (runstate.currentMNCaption && oldstate.screen != 'game')
			runstate.currentMNCaption.remove();
		if (!runstate.currentMNCaption)
			runstate.currentMNCaption = $('<span class="mn-caption"/>').attr('id', Math.random());
		var tmp = runstate.game.cloud[oldstate.answers.length];
		var a = runstate.currentMNCaption.text(tmp ? tmp.name : '…');
		var b = $('<span class="mn-caption"/>').attr('id', Math.random()).text(runstate.game.cloud[state.answers.length].name);
		if (!rb || (isForward && (oldstate.screen != 'game' || state.answers.length == oldstate.answers.length))) {
			isForward = true;
			a.remove();
			a = $();
		}
		if (!isForward) { var c = b; b = a; a = c; }
		runstate.currentMNCaption = isForward ? b : a;
		
		a.zoom(rb, '#mn-caption-box', isForward); // De ou vers la relationBox
		b.zoom('#mn-caption-box', '#mn-caption-box', !isForward); // De ou vers le #mn-caption
		
		window.setTimeout(function() {
			$('.relationBox.hot').addClass('transition-bg').removeClass('hot').delay(700).qRemoveClass('transition-bg');
		}, 0);
		
		updating = false;
		return false;
	});
	
	game.bind('leave', function() {
		if (runstate.currentMNCaption) runstate.currentMNCaption.remove();
	});
});

// ==== Écran score
runstate.scoreCache = new Cache(function getScore(k, dfd, arg) {
	$.ajaj("server.php?callback=?", {
		action: 1,
		pgid: k,
		answers: arg,
	}, dfd, function() { return state.pgid == k; });
});

init(function() {
	var score = $.screen('score');
	score.bind('pre-enter', function() {
		runstate.scoreCache.get(state.pgid, state.answers).done(function(data) {
			runstate.score = data;
			score.trigger('enter');
		});
		return false;
	});
	score.bind('enter', function() {
		var s = runstate.score;
		$("#score .scores").empty();
		$("#score .scoreTotal")
			.text(s.scoreTotal)
			.goodBad(s.minScore*s.scores.length, s.maxScore*s.scores.length, {r:255,g:0,b:0}, {r:0,g:192,b:0});
		$.each(s.scores, function(i,e) {
			$("#templates .scoreLine")
				.clone()
				.find(".word").text(e.name).end()
				.find(".score").text(e.score).goodBad(s.minScore, s.maxScore, {r:255,g:0,b:0}, {r:0,g:192,b:0}).end()
				.appendTo("#score .scores");
		});
	});
});

// ==== Écran Préférences
runstate.prefs = { theme: "green" };
runstate.serverPrefs = $.extend({}, runstate.prefs);

function loadPrefs(prefs) {
	var previousTheme = runstate.prefs ? runstate.prefs.theme : 'green';
	if (prefs && prefs.theme) {
		isConnected(prefs.connected);
		runstate.prefs = prefs;
		runstate.serverPrefs = $.extend({}, runstate.prefs);
		if (runstate.loaded && previousTheme != runstate.prefs.theme) jss();
	}
}

function setPrefs(prefs, callback) {
	var dfd = $.Deferred();
	$.ajaj("server.php?callback=?", {
		action: 8,
		key: 'theme',
		value: prefs.theme
	}, dfd);
	return dfd;
}

runstate.pendingGetPrefs = function() {
	$.ajaj("server.php?callback=?", { action: 7 }, null, null, function(data) {
		if (data.theme) { isConnected(true); message("Succès", "Vous êtes connecté."); loadPrefs(data); }
		if (data.isError) { isConnected(false); message("Erreur", data.msg); }
	});
};

runstate.cancelPendingSetPrefs = function() {
	runstate.pendingSetPrefs = false;
	message("Préférences", "Les préférences n'ont pas pu être enregistrées.");
};

init(function() {
	$("#prefs").bind('enter', function() {
		$("#prefs-form input:radio[name=theme]").attr('checked', function(i,val) {
			return $(this).val() == runstate.prefs.theme;
		});
	});

	var readPrefs = function() {
		var newtheme = $("#prefs form input:radio[name=theme]:checked").eq(0).val();
		if (runstate.prefs.theme != newtheme) {
			runstate.prefs.theme = newtheme;
			jss();
		}
	};
	
	$("#prefs form").submit(function() {
		readPrefs();
		var p = $.extend({}, runstate.prefs);
		runstate.pendingSetPrefs = function() {
			setPrefs(p)
				.fail(function(data) {
					if (!data || (data.error != 10 && data.error != 3)) {
						message("Erreur", data.msg);
						runstate.cancelPendingSetPrefs();
					}
				})
				.done(function(data) {
					runstate.pendingSetPrefs = false;
					loadPrefs(data);
					message("Préférences", "Les préférences ont été enregistrées.");
				});
		};
		location.href = "#frontpage";
		return false;
	});
	$("#prefs form").bind('reset', function() {
		runstate.prefs = $.extend({}, runstate.serverPrefs);
		location.hash = "#frontpage";
	});
	$("#prefs form input:radio[name=theme]").bind('change click', readPrefs);
});
