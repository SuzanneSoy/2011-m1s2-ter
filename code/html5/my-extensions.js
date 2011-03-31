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
