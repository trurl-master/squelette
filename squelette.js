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

function cli(command, options) {
	log('command', command);
	var result = execSync(command, options);
	console.log(result.toString())
}

function beginDb() {
	process.chdir('inc/db');
}

function endDb() {
	process.chdir('../..');
}

function propel(what) {
	// log('command', 'propel ' + what);
	cli(propel_path + ' ' + what, {
		cwd: 'inc/db'
	});
}


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

	//
	cli('composer install');

	// build propel
	propel('sql:build');
	propel('model:build');
	propel('config:convert');

	// copy extended propel models over default propel ones
	beginDb();

	log('message', 'apply squelette model patches');
	const read = (dir) =>
	  fs.readdirSync(dir)
	    .reduce((files, file) =>
	      fs.statSync(path.join(dir, file)).isDirectory() ?
	        files.concat(read(path.join(dir, file))) :
	        files.concat(path.join(dir, file)),
	      []);

	cli('cp -r ./Squelette/. ./generated-classes/Squelette/');

	if (options.createDb) {
		cli('mkdir -p data');
		cli('touch data/main.sqlite');
		propel('sql:insert');
		cli('mv data ../../data');
	}

	endDb();

	// add core classes to autoload
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
	cli('composer dump-autoload -o');

	//
	log('message', 'please edit inc/db/data/main.sqlite to fill out meta data entries');

}



function refresh(what) {

	// refresh propel
	switch (what) {
		case 'db-model':
			propel('model:build');
			break;
		case 'db-config':
			propel('config:convert');
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
	.description('refresh some part of config')
	.action(refresh)

program
	.command('diff')
	.description('migration diff')
	.action(function() {
		propel('diff');
	})

program
	.command('migrate')
	.description('migration migrate')
	.action(function() {
		propel('migrate');
	})

program
	.version('0.1.0')
	.parse(process.argv);
