const chalk = require('chalk');

function log(type, message) {
	switch (type) {
		case 'command':
			console.log(chalk.hex('#4040ff')(' Command ') + ' ' + message)
			break;
		case 'message':
			console.log(chalk.inverse(' Message ') + ' ' + message)
			break;
		case 'error':
			console.log(chalk.hex('#ff4040')('  Error  ') + ' ' + message)
			break;
	}
}

module.exports = log;
