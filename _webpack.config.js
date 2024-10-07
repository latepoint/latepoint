const path = require('path');
var webpack = require('webpack');

module.exports = {
	mode: 'none',
  entry: {
		'block-book-button': {
			'import': './blocks/src/book-button/index.js',
			'filename': '../blocks/build/book-button/[name].js'
		},
  	'sprintf' : './node_modules/sprintf-js/dist/sprintf.min.js',
		'dragula' : './node_modules/dragula/dist/dragula.min.js',
		'Chart' : './node_modules/chart.js/dist/Chart.min.js',
		'moment' : './node_modules/moment/min/moment-with-locales.min.js',
		'jquery.inputmask' : './node_modules/inputmask/dist/jquery.inputmask.min.js',
		'daterangepicker' : './node_modules/daterangepicker/daterangepicker.js',
		'pickr' : './node_modules/pickr-widget/dist/pickr.min.js'
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'public', 'javascripts', 'vendor'),
  },
	plugins: [
	new webpack.ProvidePlugin({
	      moment: "moment"
	    })
	]
};