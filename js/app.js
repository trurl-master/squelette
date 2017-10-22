import "babel-polyfill";

import "../css/normalize.less";
import "../css/main.less";

import "../node_modules/slick-carousel/slick/slick.css";


import picturefill from 'picturefill'
import './libs/jquery.reflow';
// import './libs/midnight';
import './libs/jquery-ui.min.js';

$(function () {
	picturefill();
});


//
var active_modules = require.context('[modules]/', true, /^((?!admin).)*\.js$/)
var active_modules_list = active_modules.keys();


//
app.modules = {};
app.duration = 500; // default duration for all ui animations

//
app.active_modules.forEach((module_name)=>{
	var module_filename = './' + module_name + '.js'

	if (active_modules_list.indexOf(module_filename) >= 0) {
		var module_exports = active_modules(module_filename)
		app.modules[module_name.replace(/\/|-/g, '_')] = typeof module_exports === 'function' ? module_exports() : module_exports;
	}
})