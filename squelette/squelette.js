#!/usr/bin/env node

const propel = require('./propel.js');

const program = require('commander');

//
program
	.command('init') // [namespace]')
	.description('run init command for a new app')
	.option("-c, --create-db", "Create db file and insert basic structure")
	.action(require('./commands/init'));

program
	.command('refresh [what]')
	.description('refresh some part of config')
	.action(require('./commands/refresh'))

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
	.command('migrate-back')
	.description('migration roll back')
	.action(function() {
		propel('migration:down');
	})

program
	.command('deploy [state] [dconf]')
	.description('deploy project')
	.action(require('./commands/deploy'))

program
	.version('0.1.1')
	.parse(process.argv);
