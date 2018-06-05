const log = require('../log.js');
const cli = require('../cli.js');

const fs = require('fs');
const path = require('path');

//
var deploy;


//
function remote_is_dir_exists(dir) {
	var is_dir_exists = cli(
		'if ssh ' + deploy.remote._host + ' \'[ -d ' + dir + ' ]\'; then echo \'yes\'; else echo \'no\'; fi;',
		null,
		true,
		true
	);

    return is_dir_exists.trim() === 'yes'
}

function remote_list_apps() {

	var dirs = cli(
		'ssh ' + deploy.remote._host + ' \'' + 'cd ' + deploy.remote.path + 'apps;ls -d */' + '\'',
		null,
		true,
		true
	);

	return dirs
		.split("\n")
		.filter(function(x) {return (x !== '')})
		.map(function(x) {
			return x.replace(/\/$/, '');
		});
}

function propel_remote(command) {
    cli_remote('cd "' + deploy.remote.path + deploy.remote._version_path + '/inc/db/"; ../../vendor/bin/propel ' + command)
}

function composer_remote(command) {
	cli_remote('cd "' + deploy.remote.path + deploy.remote._version_path + '/"; php composer.phar ' + command)
}

function cli_remote(command, pre) {
    return cli((typeof pre === 'undefined' ? '' : pre + ' ') + 'ssh ' + deploy.remote._host + ' \'' + command + '\'');
}

function remote_mkdir(dir) {
    return cli_remote('mkdir ' + deploy.remote.path + dir);
}

function remote_rmdir(dir) {
    cli_remote('rm -rf ' + deploy.remote.path + dir);
}

function remote_cp(from, to) {
    cli_remote('cp -r ' + deploy.remote.path + from + ' ' + deploy.remote.path + to);
}

function scp(from, to) {
	return cli('scp -r ' + from + ' ' + deploy.remote._host + ':' + deploy.remote.path + to)
}


//
function deployInit(dconf) {

	try {
		deploy = JSON.parse(fs.readFileSync('./deploy/' + dconf + '/config.json', 'utf8'));
	} catch (err) {
		if (err.code === 'ENOENT') {
			console.log('Deploy config ' + dconf + ' not found');
		} else {
			throw err;
		}
		return false;
	}

	deploy.remote._host = deploy.remote.username + '@' + deploy.remote.server;
	deploy.remote.next_version = deploy.remote.latest_version === false ? 1 : deploy.remote.latest_version + 1;
	deploy.remote.copy_db = typeof deploy.remote.copy_db !== 'undefined' ? deploy.remote.copy_db : true;
    deploy.remote._version_path = 'apps/v' + deploy.remote.next_version;

	return true;
}

function deployIncrementVersion(dconf) {
	try {
		var d = JSON.parse(fs.readFileSync('./deploy/' + dconf + '/config.json', 'utf8'));
	} catch (err) {
		if (err.code === 'ENOENT') {
			log('error', 'Deploy config ' + dconf + ' not found');
		} else {
			throw err;
		}
		return false;
	}

    d.remote.latest_version = deploy.remote.latest_version === false ? 1 : deploy.remote.latest_version + 1;
    fs.writeFile('./deploy/' + dconf + '/config.json', JSON.stringify(d, null, 4));
}


//
function deployBegin() {

	log('message', 'deploy — start');

	if (remote_is_dir_exists(deploy.remote.path + deploy.remote._version_path)) {

		throw 'version #' + deploy.remote.next_version + ' already exists';

		return false;
	}

	log('message', 'deploy — creating new version');

	remote_mkdir(deploy.remote._version_path);

    return true;
}



function deployEnd() {

    // copy data from previous version
	if (deploy.remote.latest_version !== false && deploy.remote.copy_db) {

        log('message', 'deploy — copying data from latest version');

    	if (!remote_is_dir_exists(deploy.remote.path + 'apps/v' + deploy.remote.latest_version)) {

    		log('error', 'latest version #' + deploy.remote.latest_version + ' doesn\'t exist');

    		return false;
    	}

		remote_cp('apps/v' + deploy.remote.latest_version + '/data', deploy.remote._version_path + '/data');
		propel_remote('migrate');
		composer_remote('dump-autoload -o');

    } else {

		if (deploy.remote.copy_db) {
			log('message', 'deploy — no latest version to copy data from, copying from local');
		} else {
			log('message', 'deploy — copying data from local (forced)');
		}

		scp('./data', deploy.remote._version_path + '/data');

    }

    log('message', 'deploy — finish');

}




module.exports = function(state, dconf) {
    const ERRORMSG = 'Please make sure you\'ve indicated correct parameters'

    if (typeof state === 'undefined') {
        log('error', ERRORMSG);
        return;
    }

	if (!deployInit(dconf)) {
		log('message', 'deploy — abort')
		return;
	}

    switch(state) {

        case 'new':

            log('message', 'Deploy to "' + dconf + '"');

			try {

				deployBegin();

	            log('message', 'deploy — building assets');
	            cli('npm run build');

	            log('message', 'deploy — copying project files');
				scp('./assets ./inc ./modules ./templates ./composer.json ./composer.phar ./webpack.php ./index.php ./.htaccess', deploy.remote._version_path);
				scp('./deploy/' + dconf + '/deploy.config.php', deploy.remote._version_path + '/config.php'); 

			} catch (err) {

				log('error', err)

			    return false;
		   	}


			composer_remote('install');
            propel_remote('config:convert')

            deployEnd();
            break;

        case 'switch':

        	if (!remote_is_dir_exists(deploy.remote.path + deploy.remote._version_path)) {

        		log('error', 'version #' + deploy.remote.next_version + ' doesn\'t exists');

        		return false;
        	}

			//
            var deploy_htaccess = fs.readFileSync('./deploy/' + dconf + '/' + deploy.remote.root_htaccess.filename, 'utf8')

            deploy_htaccess = deploy_htaccess.replace(/{{{version}}}/g, 'v' + deploy.remote.next_version);
            deploy_htaccess = deploy_htaccess.replace(/{{{site}}}/g, deploy.remote.root_htaccess.site);

            fs.writeFileSync('./deploy/' + dconf + '/converted.htaccess', deploy_htaccess, 'utf8');
            scp('./deploy/' + dconf + '/converted.htaccess', '.htaccess');
            fs.unlinkSync('./deploy/' + dconf + '/converted.htaccess');

            //
            var deploy_app_php = fs.readFileSync('./deploy/' + dconf + '/' + deploy.remote.root_app.filename, 'utf8')

            deploy_app_php = deploy_app_php.replace(/{{{version}}}/g, 'v' + deploy.remote.next_version);

            fs.writeFileSync('./deploy/' + dconf + '/converted.app.php', deploy_app_php, 'utf8');
            scp('./deploy/' + dconf + '/converted.app.php', 'app.php');
            fs.unlinkSync('./deploy/' + dconf + '/converted.app.php');

            deployIncrementVersion(dconf);

            break;

		case 'cleanup':

			var apps = remote_list_apps();

			apps = apps.filter(function(x) {
				return
					(x !== ('v' + deploy.remote.latest_version)) &&
					(x !== ('v' + (deploy.remote.latest_version-1)));
			})

			for (var ai in apps) {
				remote_rmdir(apps[ai]);
			}

			break;

        default:
            log('error', ERRORMSG);
            return;
    }
}
