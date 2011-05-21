Number.prototype.clip = function(min, max, floor) {
	try {
	return Math.min(Math.max(floor ? Math.floor(this) : this, min), max);
	} catch(e) {alert("Error Number.prototype.clip");alert(e);}
};

Number.prototype.mapInterval = function(a,b,x,y) {
	return x + ((this-a) / (b-a) * (y-x));
}

Array.prototype.equalp = function(a) {
	if (this.length != a.length) return false;
	for (var i = 0; i < this.length; i++) {
		if (this[i] !== a[i]) return false;
	}
	return true;
};

function dichotomy(start, isBigger) {
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
	this.css({overflow: 'auto'});
	var setFont = this.find('.setFont').andSelf();
	this.find('.center').css({top:0, left:0}); // Petit hack pour que ça ne déborde pas à cause de l'offset mis par .center().
	var size = dichotomy(parseInt(this.css("font-size"), 10), function(x) {
		setFont.css("fontSize", x);
		return that.$ormap(function(i,e) { return e.hasScroll(); });
	});
	this.css({overflow: 'hidden'});
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
		var args = Array.prototype.slice.call(arguments); // cast Arguments → Array
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
		if (isNaN(+w) && w && typeof w.width != 'undefined') { h = w.height; w = w.width; }
		if (typeof w == 'undefined') return {width: this.width(), height:this.height()};
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

$.debounce = function debounce(fn, interval) {
	var wait = false;
	var delayedFn = false;
	interval = interval || 200;
	return function() {
		var that = this;
		var args = Array.prototype.slice.call(arguments); // cast Arguments → Array
		delayedFn = function() { delayedFn = false; fn.apply(that, args); }
		if (!wait) {
			wait = true;
			delayedFn();
			var loop = function() {
				if (delayedFn) {
					delayedFn();
					window.setTimeout(loop, interval);
				} else {
					wait = false;
				}
			}
			window.setTimeout(loop, interval);
		}
	};
};

/** Zoom from small center of startElem to big center of endElem, or do the reverse animation (from big end to small start). */
$.fn.zoom = function(startElem, endElem, reverse) {
	var that = this;
	startElem = $(startElem);
	endElem = $(endElem);
	return this.queue(function(next) {
		if (that.size() == 0) return next();
		that.removeClass('transition'); // disable animations
		window.setTimeout(function() {
			// Calcul de la taille de police pour end().
			var endbox = $('<div style="text-align:center;"/>').appendTo('body').wh(endElem.wh());
			that.appendTo(endbox);
			that.css({position:'', top:'', left:''});
			endbox.fitFont();
			var BFontSize = endbox.css('fontSize');
			var APos = endbox.css('fontSize', 0).center(startElem.center()).offset();
			var BPos = endbox.css('fontSize', BFontSize).center(endElem.center()).offset();
			var A = function() { endbox.css('fontSize', 0).offset(APos); };
			var B = function() { endbox.css('fontSize', BFontSize).offset(BPos); };
			(reverse ? B : A)();
			window.setTimeout(function() {
				endbox.addClass('transition'); // enable animations
				(reverse ? A : B)();
				endbox.delay(700).qRemoveClass('transition');
				if (reverse) endbox.qRemove();
				next();
			}, 0);
		}, 0);
	});
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
	var cache = {};
	this.get = function(k, arg) {
		return cache[k] = cache[k] || $.Deferred(function(dfd) { resolver(k, dfd, arg); }).fail(function() { cache[k] = false; }).promise();
	};
}
