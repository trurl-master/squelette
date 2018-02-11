const log = require('./log.js');
const execSync = require('child_process').execSync;

function cli(command, options, silent, return_result) {

	if (!silent) {
		log('command', command);
	}

	try {
	    var result = execSync(command, options);
	} catch (error) {
		log('error', error.message);
		return;
	}

	if (return_result) {
		return result.toString();
	} else {
		console.log(result.toString())
	}

}

module.exports = cli;
