#!/usr/bin/env node

const program = require('commander');
const chalk = require('chalk');
const fs = require('fs');
const path = require('path');
const execSync = require('child_process').execSync;

//
const propel_path_from_root = 'vendor/bin/propel'
const propel_path = '../../' + propel_path_from_root;


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

function log(type, message) {
	switch (type) {
		case 'command':
			console.log(chalk.inverse('Command') + ' ' + message)
			break;
		case 'message':
			console.log(chalk.inverse('Message') + ' ' + message)
			break;
	}
}


function init(/*namespace, */options, a,b,c) {

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
	// console.log(chalk.inverse('npm install'));
	// execSync('npm install');

	//
	log('command', 'composer install');
	execSync('composer install');

	// build propel

	process.chdir('inc/db');
	log('command', 'propel sql:build');
	execSync(propel_path + ' sql:build');
	log('command', 'propel model:build');
	execSync(propel_path + ' model:build');
	log('command', 'propel config:convert');
	execSync(propel_path + ' config:convert');

	// copy extended propel models over default propel ones
	log('message', 'apply squelette model patches');
	const read = (dir) =>
	  fs.readdirSync(dir)
	    .reduce((files, file) =>
	      fs.statSync(path.join(dir, file)).isDirectory() ?
	        files.concat(read(path.join(dir, file))) :
	        files.concat(path.join(dir, file)),
	      []);

	execSync('cp -r ./Squelette/. ./generated-classes/Squelette/');
	// read('./Squelette').forEach(function(filename) {
	// 	fs.createReadStream(filename).pipe(fs.createWriteStream('generated-classes/Squelette/' + path.basename(filename)));
	// })

	if (options.createDb) {
		execSync('mkdir -p data');
		execSync('touch data/main.sqlite');
		execSync(propel_path + ' sql:insert');
		execSync('mv data ../../data');
	}

	process.chdir('../..');

	// add core classes to autoload

	// process.chdir('../..');

	log('message', 'modify composer.json to add autoloading of the core classes');
	var composer_json = fs.readFileSync('composer.json', 'utf8');
	composer_json = JSON.parse(composer_json);
	if (typeof composer_json.autoload === 'undefined') {
		composer_json.autoload = {};
	}

	if (typeof composer_json.autoload.classmap === 'undefined') {
		composer_json.autoload.classmap = [];
	}

	composer_json.autoload.classmap.push(
		"inc/db/generated-classes/",
		"inc/db/generated-api/",
		"inc/classes"
	);

	fs.writeFileSync('composer.json', JSON.stringify(composer_json, null, 4))

	log('command', 'composer dump-autoload -o');
	execSync('composer dump-autoload -o');

	//
	log('message', 'please edit inc/db/data/main.sqlite to fill out meta data entries');

}



function refresh(what) {

	// refresh propel
	process.chdir('inc/db');
	switch (what) {
		case 'db-model':
			log('command', 'propel model:build');
			execSync(propel_path + ' model:build');
			break;
		case 'db-config':
			log('command', 'propel config:convert');
			execSync(propel_path + ' config:convert');
			break;
		default:
			log('message', 'uknown property to refresh');
			break;
	}

}



//
program
	.command('init') // [namespace]')
	.description('run init command for a new app')
	.option("-c, --create-db", "Create db file and insert basic structure")
	.action(init);

program
	.command('refresh [what]')
	.description('refresh some part of install')
	.action(refresh)


program
	.version('0.1.0')
	.parse(process.argv);
