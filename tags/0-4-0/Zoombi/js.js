/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var Zoombi = new function(e){
	if( Zoombi )
	{
		return Zoombi;
	}
		
	var a = [];
	var l = [];
	var r = [];
	var o = null;

	var parse = function( string ) {
		var res = [];
		console.log('Parse: ' + string );
		var c = string.substring(1);
		c = c.split('&');

		for( i = 0; i < c.length; i++ )
		{
			var t = c[i].split('=');
			res[ t[0] ] = t[1];
		}
		return res;
	}

	var f = function(){
		if( Zoombi === undefined )
			return;

		var h = window.location.hash;
		if( o === null )
		{
			o = h;
			return;
		}

		if( o != h )
		{
			var n_ = h.substring(1);
			var o_ = o.substring(1);
			for( var i = 0; i < a.length; i++)
				a[i](n_,o_);

			o = h;
		}
	}

	window.onload = function(){
		
		setInterval(f,50);

		for( var _i = 0; _i < l.length; _i++ )
			l[ _i ].apply(this);
	};

	document.addEventListener("DOMContentLoaded", function(){
		for( var _i = 0; _i < r.length; _i++ )
			r[ _i ].apply(this);
	}, false);

	return {
		get : function(p) {
			var r = parse( window.location.search );
			if( p === undefined )
				return r;
			return r[p];
		},
		hash : function(p) {
			var r = parse( window.location.hash );
			if( p === undefined )
				return r;
			return r[p];
		},
		each: function(e,func){
			if( typeof func !== "function" )
				return Zoombi;

			var t = Object(e);
			var len = t.length >>> 0;
			for (var i = 0; i < len; i++)
			  if (i in t)
				func.call(t, t[i], i, t);

			return Zoombi;
		},
		parseHash: function( hash, splitter ){
			var a = hash.split( splitter || ';' );
			var r = [];
			Zoombi.each(a,function(item,index,array){
				var s = item.split('=');
				if( s.length == 2 )
					r[ s[0] ] = s[1];
			});
			return r;
		},
		onhashchanged : function(func){
			if( func && typeof func == 'function' )
				a.push(func);
		},
		onload: function(func){
			if( func && typeof func == 'function' )
				l.push(func);
		},
		onready: function(func){
			if( func && typeof func == 'function' )
				r.push(func);
		}
	}
}
