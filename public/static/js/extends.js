Date.prototype.format = function (format) {
	var o = {
		"M+": this.getMonth() + 1, //month
		"d+": this.getDate(),    //day
		"h+": this.getHours(),   //hour
		"m+": this.getMinutes(), //minute
		"s+": this.getSeconds(), //second
		"q+": Math.floor((this.getMonth() + 3) / 3),  //quarter
		"S": this.getMilliseconds() //millisecond
	}
	if (/(y+)/.test(format))
		format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	for (var k in o)
		if (new RegExp("(" + k + ")").test(format))
			format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
	return format;
}
jQuery.fn.rotate = function (angle, click, whence) {
	var p = this.get(0);
	if (!whence) {
		p.angle = ((p.angle == undefined ? 0 : p.angle) + angle) % 360;
	} else {
		p.angle = angle;
	}
	if (p.angle >= 0) {
		var rotation = Math.PI * p.angle / 180;
	} else {
		var rotation = Math.PI * (360 + p.angle) / 180;
	}
	var costheta = Math.cos(rotation);
	var sintheta = Math.sin(rotation);
	var canvas = document.createElement('canvas');
	if (!p.oImage) {
		canvas.oImage = new Image();
		canvas.oImage.src = p.src;
		canvas.oImage.onload = function () {
			onload();
		};
	} else {
		canvas.oImage = p.oImage;
		onload();
	}
	function onload() {
		canvas.style.width = canvas.width = Math.abs(costheta * canvas.oImage.width) + Math.abs(sintheta * canvas.oImage.height);
		canvas.style.height = canvas.height = Math.abs(costheta * canvas.oImage.height) + Math.abs(sintheta * canvas.oImage.width);
		var context = canvas.getContext('2d');
		context.save();
		if (rotation <= Math.PI / 2) {
			context.translate(sintheta * canvas.oImage.height, 0);
		} else if (rotation <= Math.PI) {
			context.translate(canvas.width, -costheta * canvas.oImage.height);
		} else if (rotation <= 1.5 * Math.PI) {
			context.translate(-costheta * canvas.oImage.width, canvas.height);
		} else {
			context.translate(0, -sintheta * canvas.oImage.width);
		}
		context.rotate(rotation);
		context.drawImage(canvas.oImage, 0, 0, canvas.oImage.width, canvas.oImage.height);
		context.restore();
		canvas.className = 'maxImg';
		canvas.angle = p.angle;
		if (click) {
			canvas.onclick = click;
		}
		p.parentNode.replaceChild(canvas, p);
	}
}
jQuery.fn.drawImage = function (click) {
	this.rotate(0, click);
}
jQuery.fn.rotateRight = function (angle, click) {
	this.rotate(angle == undefined ? 90 : angle, click);
}
jQuery.fn.rotateLeft = function (angle, click) {
	this.rotate(angle == undefined ? -90 : -angle, click);
}
jQuery.cookie = function (name, value, options) {
	if (typeof value != 'undefined') {
		options = options || {};
		if (value === null) {
			value = '';
			options.expires = -1;
		}
		var expires = '';
		if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
			var date;
			if (typeof options.expires == 'number') {
				date = new Date();
				date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
			} else {
				date = options.expires;
			}
			expires = '; expires=' + date.toUTCString();
		}
		var path = options.path ? '; path=' + options.path : '; path=/';
		var domain = options.domain ? '; domain=' + options.domain : '';
		var secure = options.secure ? '; secure' : '';
		document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	} else {
		var cookieValue = null;
		if (document.cookie && document.cookie != '') {
			var cookies = document.cookie.split(';');
			for (var i = 0; i < cookies.length; i++) {
				var cookie = jQuery.trim(cookies[i]);
				if (cookie.substring(0, name.length + 1) == (name + '=')) {
					cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
					break;
				}
			}
		}
		return cookieValue;
	}
};
copy2Clipboard = function (txt) {
	if (window.clipboardData) {
		window.clipboardData.clearData();
		window.clipboardData.setData("Text", txt);
	} else if (window.netscape) {
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		}
		catch (e) {
			alert(langjs['firefox_tip']);
			return false;
		}
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip) { return; }
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans) { return; }
		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext = txt; str.data = copytext;
		trans.setTransferData("text/unicode", str, copytext.length * 2);
		var clipid = Components.interfaces.nsIClipboard;
		if (!clip) { return false; }
		clip.setData(trans, null, clipid.kGlobalClipboard);
	}
};
jQuery.fn.extend({/*ctrl+enter提交*/
	ctrlSubmit: function (fn, thisObj) {
		var obj = thisObj || this;
		var stat = false;
		return this.each(function () {
			$(this).keyup(function (event) {
				if (event.keyCode == 17) {
					stat = true;
					setTimeout(function () {
						stat = false;
					}, 300);
				}
				if (event.keyCode == 13 && (stat || event.ctrlKey)) {
					fn.call(obj, event);
				}
			});
		});
	}
});