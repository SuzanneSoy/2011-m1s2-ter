Number.prototype.clip = function(min, max, floor) {
	try {
	return Math.min(Math.max(floor ? Math.floor(this) : this, min), max);
	} catch(e) {alert("Error Number.prototype.clip");alert(e);}
};

Number.prototype.mapInterval = function(a,b,x,y) {
	return x + ((this-a) / (b-a) * (y-x));
}
	
dichotomyStop = false;
function dichotomy(start, isBigger, debug) {
	try {
	var i = 0, min = 0, max, half;
	for (max = start || 1; ++i < 10 && !isBigger(max); max *= 2);
	for (half = start; Math.abs(min-max) > 0.1; half = (min + max) / 2) {
		if (!isBigger(half)) min = half;
		else                 max = half;
	}
	while (half > 1 && isBigger(half)) { --half; }
	return half;
	} catch(e) {alert("Error dichotomy");alert(e);throw(e);}
}

$.fn.fold = function(acc, fn) {
	try {
	this.each(function(i,e) { acc = fn(acc, i, e); });
	return acc;
	} catch(e) {alert("Error $.fn.fold");alert(e);}
};

$.fn.maxWidth = function() { return this.fold(0, function(acc,i,e){ return Math.max(acc, $(e).width()); }); };
$.fn.maxHeight = function() { return this.fold(0, function(acc,i,e){ return Math.max(acc, $(e).height()); }); };
$.fn.sumHeight = function() { return this.fold(0, function(acc,i,e){ return acc + $(e).height(); }); };
$.fn.sumOuterHeight = function() { return this.fold(0, function(acc,i,e){ return acc + $(e).outerHeight(); }); };

$.fn.hasScroll = function() {
	var e = this.get(0);
	return (e.clientHeight != e.scrollHeight) || (e.clientWidth != e.scrollWidth);
}

$.fn.fitFont = function() {
	try {
	var that = this;
	var setFont = this.find('.setFont').andSelf();
	this.find('.center').css({top:0, left:0}); // Petit hack pour que ça ne déborde pas à cause de l'offset mis par .center().
	var size = dichotomy(parseInt(this.css("font-size"), 10), function(x) {
		setFont.css("fontSize", x);
		return that.$ormap(function(i,e) { return e.hasScroll(); });
	}, this);
	this.css("font-size", Math.max(0, size));
	return this;
	} catch(e) {alert("Error $.fn.fitFont");alert(e);throw(e);}
};

$.fn.swapCSS = function(k,v) {
	var old = this.css(k);
	this.css(k, v);
	return old;
};

$.fn.$each = function(fn) {
	this.each(function(i,e) { return fn(i, $(e)); });
};

$.fn.$ormap = function(fn) {
	var ret = false;
	this.each(function(i,e) { if (fn(i, $(e))) { ret = true; return false; } });
	return ret;
};

function queueize(method) {
	try {
	return function() {
		var $this = this;
		var args = arguments;
		return this.queue(function(next) {
			$this[method].apply($this,args);
			next();
		});
	};
	} catch(e) {alert("Error queueize");alert(e);}
}

$.fn.qAddClass = queueize("addClass");
$.fn.qRemoveClass = queueize("removeClass");
$.fn.qRemove = queueize("remove");
$.fn.qShow = queueize("show");
$.fn.qHide = queueize("hide");
$.fn.qCss = queueize("css");
$.fn.qText = queueize("text");

$.fn.wh = function(w, h) {
	try {
		return this.width(w).height(isNaN(+h) ? w : h);
	} catch(e) {alert("Error $.fn.wh");alert(e);}
};

$.fn.relativePos = function(xAnchor, yAnchor, to, justCss) {
	try {
	if (to) this.css("position", "absolute");
	var deltaX = this.outerWidth()  * xAnchor;
	var deltaY = this.outerHeight() * yAnchor;

	if (to) {
		var css = {
			left: to.left - deltaX,
			top:  to.top  - deltaY
		};
		return (justCss ? css : this.offset(css));
	} else {
		var pos = this.offset();
		pos.left += deltaX;
		pos.top  += deltaY;
		return pos;
	}
	} catch(e) {alert("Error $.fn.relativePos");alert(e);}
};

$.each({
	center:    {x:0.5, y:0.5},
	north:     {x:0.5, y:0},
	northEast: {x:1,   y:0},
	east:      {x:1,   y:0.5},
	southEast: {x:1,   y:1},
	south:     {x:0.5, y:1},
	southWest: {x:0,   y:1},
	west:      {x:0,   y:0.5},
	northWest: {x:0,   y:0},
}, function(i,e) {
	try {
	var x = e.x;
	var y = e.y;
	$.fn[i] = function(to, justCss) {
		try {
		return this.relativePos(x, y, to, justCss);
		} catch(e) {alert("Error auto-generated $.fn." + i);alert(e);}
	};
	} catch(e) {alert("Error top-level anonymous in my-extensions");alert(e);}
});

$.fn.clickOnce = function(fn) {
	try {
	this.unbind("click",fn).click(fn);
	} catch(e) {alert("Error $.fn.clickOnce");alert(e);}
};

/**
* startcolor et endcolor sont de la forme {r:0,g:255,b:127}
*/
$.fn.goodBad = function(min, max, startcolor, endcolor) {
	var val = parseInt(this.text(), 10);
	if (isNaN(val)) return this;
	this.css("color","rgb("
						  +(val.mapInterval(min,max,startcolor.r,endcolor.r).clip(0, 255, true))+","
						  +(val.mapInterval(min,max,startcolor.g,endcolor.g).clip(0, 255, true))+","
						  +(val.mapInterval(min,max,startcolor.b,endcolor.b).clip(0, 255, true))+")");
	return this;
};

var PtiClic = $({});
PtiClic.queueJSON = function(url, data, ok, error) {
};

function decodeHash(hash) {
	/* hash.match(/^#([a-z]+(\/[0-9]+(\/-?[0-9]+(,-?[0-9]+)*)?)?)?$/) */
	hash = hash.substring(1).split('/');
	return {
		screen:hash[0] || 'splash',
		pgid:hash[1] || 0,
		answers:(hash[2] ? hash[2].split(',') : [])
	};
}

function appendAnswer(data, answer) {
	return $.extend({}, data, { answers: data.answers.concat([answer]) });
}

function encodeHash(data) {
	var hash = "#";
	if (data.screen == '') return hash;
	hash += data.screen
	if (data.pgid == 0) return hash;
	hash += '/'+data.pgid;
	if (data.answers.length == 0) return hash;
	hash += '/'+data.answers.join(',');
	return hash;
}

function Cache(resolver) {
	var cache = [];
	var self = this;
	this.get = function(k) {
		return cache[k] = cache[k] || $.Deferred(function(dfd) { resolver(k, dfd, self); });
	};
	this.alias = function(alias, k) {
		cache[alias] = cache[alias] || $.Deferred();
		cache[k].done(function(data) { cache[alias].resolve(data); });
	};
}

/* Enchaînement des écrans

*** Utiliser un objet Deferred pour les fonctions qu'on ne veut apeller qu'une fois.

***

- Cache des parties récupérées & scores (key = pgid pour les deux, mais params supplémentaires pour scores)
new Cache(queryFn(k, dfd, cache) { cache.set(k,v); dfd.resolve(data); });
Cache.get(k) returns Promise; // Peut déclencher $.extend(Cache, queryFn(k)).

- Récupérer une partie aléatoire, et la stocker dans le cache à son arrivée
- Afficher $(#game) (et $(#score)) une fois la partie (score) récupéré(e) et le(la) consommer
- Sauf si l'action a été annulée.
$.when(getGame, goGame)
if (runstate.nextScreen == 'game') …
- Lorsqu'une requête échoue, on demande le login, on retente la requête avec ce login/mdp. Si ça marche avec ce login/mdp, on .resolve(), sinon on .fail().

***

Aller sur un écran donné (parfois sans changer l'URL, par ex. pour splash→frontpage, et lorsqu'on force le login).
Recevoir des données avant d'entrer dans un écran.
Envoyer des données avant de quiter un écran.
Vérouiller l'écran courant pendant qu'on attend un transfert ou bien des écrans d'«attente».
Lorsqu'un transfert a échoué car non logué, on va sur l'écran de connexion et on retente le transfert ensuite.
Stocker uniquement les données importantes dans l'url (état, numéro de partie, réponses).
Pouvoir basculer sur un écran et exécuter quelque chose une fois qu'il est chargé (exécuter le commit pour l'url).
*/