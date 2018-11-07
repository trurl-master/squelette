const log = require('../log.js');
const cli = require('../cli.js');
const propel = require('../propel.js');

const fs = require('fs');
const path = require('path');
var prompt = require('prompt-sync')();


module.exports = function (/*namespace, */options) {

    var namespace, done = false;

    while(!done) {
        namespace = prompt('Please enter namespace for db classes: ');

        if (namespace === null) {
            return;
        }

        namespace = namespace.replace(/\W/g, '').trim()

        if (namespace !== '') {
            done = true;
        } else {
            console.log('Incorrect namespace (no symbols except letters and numbers are allowed).')
        }
    }

    namespace = namespace.charAt(0).toUpperCase() + namespace.slice(1);

    // rename db connection and namespace
    log('message', 'rename namespace to: ' + namespace);
    try {
        fs.writeFileSync(
            './inc/db/schema.xml',
            fs.readFileSync('./inc/db/schema.xml', 'utf8').replace(/namespace="Squelette"/g, 'namespace="' + namespace + '"'),
            'utf8'
        );
    } catch (err) {
        return console.log(err);
    }

    // rename squelette model patches namespace

    const read = (dir) =>
      fs.readdirSync(dir)
        .reduce((files, file) =>
          fs.statSync(path.join(dir, file)).isDirectory() ?
            files.concat(read(path.join(dir, file))) :
            files.concat(path.join(dir, file)),
          []);


    try {
        read('./inc/db/Squelette/').forEach(function(file) {
            fs.writeFileSync(
                file,
                fs.readFileSync(file, 'utf8').replace(/Squelette/g, namespace),
                'utf8'
            );
        })

    } catch (err) {
        return console.log(err);
    }

    //
    cli('composer install');

    // build propel
    propel('sql:build');
    propel('model:build');
    propel('config:convert');

    // copy extended propel models over default propel ones
    process.chdir('inc/db');

    log('message', 'apply squelette model patches');

    cli('cp -r ./Squelette/. ./generated-classes/' + namespace + '/');

    //
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
    if (options.createDb) {
        log('message', 'please edit data/main.sqlite to fill out meta data entries');
    }

}
