Number.prototype.clip = function(min, max, floor) {
	try {
	return Math.min(Math.max(floor ? Math.floor(this) : this, min), max);
	} catch(e) {alert("Error Number.prototype.clip");alert(e);}
};

function dichotomy(start, isBigger, foo) {
	try {
	var i = 0, min = 0, max, half;

	for (max = start || 1; ++i < 10 && !isBigger(max); max *= 2);
	for (half = start; Math.abs(min-max) > 0.1; half = (min + max) / 2) {
		if (!isBigger(half)) min = half;
		else                 max = half;
	}
	while (half > 1 && isBigger(half)) { --half; ++i; }
		console.log(i,foo);
	return half;
	} catch(e) {alert("Error dichotomy");alert(e);}
}

$.fn.maxWidth = function() {
	try {
	max = 0;
	this.each(function(i,e){ max = Math.max(max, $(e).width()); });
	return max;
	} catch(e) {alert("Error $.fn.maxWidth");alert(e);}
}
$.fn.maxHeight = function() {
	try {
	max = 0;
	this.each(function(i,e){ max = Math.max(max, $(e).height()); });
	return max;
	} catch(e) {alert("Error $.fn.maxHeight");alert(e);}
}

$.fn.sumHeight = function() {
	try {
	sum = 0;
	this.each(function(i,e){ sum += $(e).height(); });
	return sum;
	} catch(e) {alert("Error sumHeight");alert(e);}
}

$.fn.fitFont = function(w, h, minFont, maxFont) {
	try {
	var oldpos = this.css("position");
	this.css({
		position: "absolute",
		maxWidth: w
	});
	var wrappers = this.wrapInner("<span/>").children();
	
	var that = this;
	this.css("font-size", dichotomy(parseInt(this.css("font-size"), 10), function(x) {
		try {
		that.css("fontSize", x);
		fubar = wrappers;
		return (wrappers.maxHeight() > h || wrappers.maxWidth() > w);
		} catch(e) {alert("Error anonymous in $.fn.fitFont");alert(e);}
	},this).clip(minFont || 0, maxFont || Infinity));

	// Restore stuff
	this.css("position", oldpos);
	//wrappers.children().unwrap();
	return this;
	} catch(e) {alert("Error $.fn.fitFont");alert(e);}
}

$.fn.fitIn = function(e, t, r, b, l) {
	try {
	e = $(e);
	if (isNaN(+t)) t = 0;
	if (isNaN(+r)) r = t;
	if (isNaN(+b)) b = t;
	if (isNaN(+l)) l = r;
	var w = e.width();
	var h = e.height();
	t *= h;
	r *= w;
	b *= h;
	l *= w;
	this.fitFont(w - r - l, h - t - b, 20).center(e.center());
	return this;
	} catch(e) {alert("Error $.fn.fitIn");alert(e);}
}

function queueize(method) {
	try {
	return function() {
		var $this = this;
		return this.queue(function(next) {
			$this[method].apply($this,arguments);
			next();
		});
	};
	} catch(e) {alert("Error queueize");alert(e);}
}

$.fn.qAddClass = queueize("addClass");
$.fn.qRemoveClass = queueize("removeClass");

$.fn.wh = function(w, h) {
	try {
	return this.width(w).height(h);
	} catch(e) {alert("Error $.fn.wh");alert(e);}
}

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