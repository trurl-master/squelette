#!/usr/bin/env node

const program = require('commander');
const chalk = require('chalk');
const fs = require('fs');
const path = require('path');
const execSync = require('child_process').execSync;

//
const propel_path = '../../vendor/bin/propel';


// function cmd(command) {
// 	var stdout = execSync(command);
// 	try {
//     return child_process.execSync(cmd).toString();
//   }
//   catch (error) {
//     error.status;  //
//     error.message; // Holds the message you typically want.
//     error.stderr;  // Holds the stderr output. Use `.toString()`.
//     error.stdout;  // Holds the stdout output. Use `.toString()`.
//   }
// }


function init(/*namespace, */options) {

	// rename db connection and namespace
	// fs.readFile('./inc/db/schema.xml', 'utf8', function (err, data) {
	// 	if (err) {
	// 		return console.log(err);
	// 	}
	//
	// 	var result = data.replace(/Squelette/g, namespace);
	//
	// 	fs.writeFile('./inc/db/schema.xml', result, 'utf8', function (err) {
	// 		if (err) return console.log(err);
	// 	});
	// });

	// npm install
	console.log(chalk.inverse('npm install'));
	execSync('npm install');

	//
	console.log(chalk.inverse('composer install'))
	execSync('composer install');

	// build propel
	process.chdir('inc/db');
	console.log(chalk.inverse('propel sql:build'));
	execSync(propel_path + ' sql:build');
	console.log(chalk.inverse('propel model:build'));
	execSync(propel_path + ' model:build');
	console.log(chalk.inverse('propel config:convert'));
	execSync(propel_path + ' config:convert');

	if (options.insert) {
		execSync(propel_path + ' sql:insert');
	}

	// copy extended propel models over default propel ones
	console.log(chalk.inverse('apply squelette model patches'));
	const read = (dir) =>
	  fs.readdirSync(dir)
	    .reduce((files, file) =>
	      fs.statSync(path.join(dir, file)).isDirectory() ?
	        files.concat(read(path.join(dir, file))) :
	        files.concat(path.join(dir, file)),
	      []);

	read('./Squelette').forEach(function(filename) {
		fs.createReadStream(filename).pipe(fs.createWriteStream('generated-classes/Squelette/' + path.basename(filename)));
	})


	// composer
	process.chdir('../..');
	console.log(chalk.inverse('composer dump-autoload -o'))
	execSync('composer dump-autoload -o');

}



function refresh(what) {

	// refresh propel
	process.chdir('inc/db');
	switch (what) {
		case 'db-model':
			console.log(chalk.inverse('propel model:build'));
			execSync(propel_path + ' model:build');
			break;
		case 'db-config':
			console.log(chalk.inverse('propel config:convert'));
			execSync(propel_path + ' config:convert');
			break;
		default:
			console.log(chalk.inverse('uknown property to refresh'))
			break;
	}

}



//
program
	.command('init') // [namespace]')
	.description('run init command for a new app')
	.option("-i, --insert-sql", "Insert generated sql into database (overwriting everything there)")
	.action(init);

program
	.command('refresh [what]')
	.description('refresh some part of install')
	.action(refresh)


program
	.version('0.1.0')
	.parse(process.argv);
