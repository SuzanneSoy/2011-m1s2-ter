$.fn.fitFont = function(w, h, minFont, maxFont) {
	minFont = minFont || 0;
	maxFont = maxFont || Infinity;
	e = $(this)
	var oldpos = e.css("position");
	e.css("position", "absolute");
	var size = parseInt(e.css("font-size"), 10);
	
	var i = 0;
	while ((e.width() < w || e.height() < h) && ++i < 10) {
		size *= 2;
		e.css("font-size", size);
	}

	var max = size;
	var min = 0;
	i=0;
	while (min < max && ++i < 10) {
		size = (max + min) / 2;
		e.css("font-size", size);
		if (e.width() < w && e.height() < h) {
			min = size;
		} else {
			max = size;
		}
	}

	if (e.width() > w || e.height() > h) --size;
	if (size < minFont) size = minFont;
	if (size > maxFont) size = maxFont;
	e.css("font-size", size);

	e.css("position", oldpos);
	return e;
}

function queueize(method) {
	return function() {
		var that = $(this);
		var args = arguments;
		return that.queue(function(next) {
			that[method].apply(that,args);
			next();
		});
	};
}

$.fn.qAddClass = queueize("addClass");
$.fn.qRemoveClass = queueize("removeClass");

$.fn.wh = function(w, h) {
	return $(this).width(w).height(h);
}

$.fn.relativePos = function(xAnchor, yAnchor, to) {
	var that = $(this);
	var deltaX = that.width()  * xAnchor;
	var deltaY = that.height() * yAnchor;

	if (to) {
		that.css("position", "absolute");
		that.offset({
			left: to.left - deltaX,
			top:  to.top  - deltaY
		});
		return that;
	} else {
		var pos = that.offset();
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
	$.fn[i] = function(to) { return $(this).relativePos(x, y, to); };
});
