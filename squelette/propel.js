const cli = require('./cli.js');

const propel_path_from_root = 'vendor/bin/propel'
const propel_path = '../../' + propel_path_from_root;

module.exports = function (what) {
	cli(propel_path + ' ' + what, {
		cwd: 'inc/db'
	});
}
