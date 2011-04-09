Number.prototype.clip = function(min, max) {
	return Math.min(Math.max(this, min), max);
};

function dichotomy(start, isBigger) {
	var i = 0, min = 0, max, half;

	for (max = start || 1; ++i < 10 && !isBigger(max); max *= 2);
	for (half = start; Math.abs(min-max) > 0.1; half = (min + max) / 2) {
		if (!isBigger(half)) min = half;
		else                 max = half;
	}
	while (half > 1 && isBigger(half)) { --half; }
	return half;
}

$.fn.maxWidth = function() {
	max = 0;
	this.each(function(i,e){ max = Math.max(max, $(e).width()); });
	return max;
}
$.fn.maxHeight = function() {
	max = 0;
	this.each(function(i,e){ max = Math.max(max, $(e).height()); });
	return max;
}

$.fn.fitFont = function(w, h, minFont, maxFont) {
	var oldpos = this.css("position");
	this.css({
		position: "absolute",
		maxWidth: w
	});
	var wrappers = this.wrapInner("<span/>").children();
	
	var that = this;
	this.css("font-size", dichotomy(parseInt(this.css("font-size"), 10), function(x) {
		that.css("fontSize", x);
		return (wrappers.maxHeight() > h || wrappers.maxWidth() > w);
	}).clip(minFont || 0, maxFont || Infinity));

	// Restore stuff
	this.css("position", oldpos);
	wrappers.children().unwrap();
	return this;
}

$.fn.fitIn = function(e, t, r, b, l) {
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
}

function queueize(method) {
	return function() {
		var $this = this;
		return this.queue(function(next) {
			$this[method].apply($this,arguments);
			next();
		});
	};
}

$.fn.qAddClass = queueize("addClass");
$.fn.qRemoveClass = queueize("removeClass");

$.fn.wh = function(w, h) {
	return this.width(w).height(h);
}

$.fn.relativePos = function(xAnchor, yAnchor, to, justCss) {
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
	var x = e.x;
	var y = e.y;
	$.fn[i] = function(to, justCss) { return this.relativePos(x, y, to, justCss); };
});
