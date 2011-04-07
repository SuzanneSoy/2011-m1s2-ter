$.fn.fitFont = function(w, h, minFont, maxFont) {
	minFont = minFont || 0;
	maxFont = maxFont || Infinity;
	var oldpos = this.css("position");
	this.css("position", "absolute");
	// TODO : reset temporairement le max-width.
	var size = parseInt(this.css("font-size"), 10);
	
	var i = 0;
	while ((this.width() < w || this.height() < h) && ++i < 10) {
		size *= 2;
		this.css("font-size", size);
	}

	var max = size;
	var min = 0;
	i=0;
	while (min < max && ++i < 10) {
		size = (max + min) / 2;
		this.css("font-size", size);
		if (this.width() < w && this.height() < h) {
			min = size;
		} else {
			max = size;
		}
	}

	if (this.width() > w || this.height() > h) --size;
	if (size < minFont) size = minFont;
	if (size > maxFont) size = maxFont;
	this.css("font-size", size);

	this.css("position", oldpos);
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

$.fn.relativePos = function(xAnchor, yAnchor, to) {
	var deltaX = this.outerWidth()  * xAnchor;
	var deltaY = this.outerHeight() * yAnchor;

	if (to) {
		this.css("position", "absolute");
		this.offset({
			left: to.left - deltaX,
			top:  to.top  - deltaY
		});
		return this;
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
	$.fn[i] = function(to) { return this.relativePos(x, y, to); };
});
