const log = require('../log.js');
const cli = require('../cli.js');
const propel = require('../propel.js');

const fs = require('fs');
const path = require('path');


module.exports = function (/*namespace, */options) {

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
    process.chdir('inc/db');

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
        cli('mv data ../../data');
    }

    process.chdir('../..');

    if (options.createDb) {
        propel('sql:insert');
    }

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

    if (composer_json.autoload.classmap.indexOf("inc/db/generated-classes/") === -1) {
        composer_json.autoload.classmap.push(
            "inc/db/generated-classes/",
            "inc/db/generated-api/",
            "inc/classes"
        );
    }


    fs.writeFileSync('composer.json', JSON.stringify(composer_json, null, 4))

    cli('composer dump-autoload -o');

    //
    log('message', 'please edit data/main.sqlite to fill out meta data entries');

}
