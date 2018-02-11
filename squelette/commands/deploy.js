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

function propel_remote_dry(command) {
    cli_remote_dry('cd "' + deploy.remote.path + deploy.remote._version_path + '/inc/db/"; ../../vendor/bin/propel ' + command)
}

function composer_remote(command) {
	cli_remote('cd "' + deploy.remote.path + deploy.remote._version_path + '/"; php composer.phar ' + command)
}

function composer_remote_dry(command) {
	cli_remote_dry('cd "' + deploy.remote.path + deploy.remote._version_path + '/"; php composer.phar ' + command)
}

function cli_remote(command, pre) {
    cli((typeof pre === 'undefined' ? '' : pre + ' ') + 'ssh ' + deploy.remote._host + ' \'' + command + '\'');
}

function cli_remote_dry(command, pre) {
    log('command', (typeof pre === 'undefined' ? '' : pre + ' ') + 'ssh ' + deploy.remote._host + ' \'' + command + '\'');
}

function remote_mkdir(dir) {
    cli_remote('mkdir ' + deploy.remote.path + deploy.remote._version_path);
}

function remote_mkdir_dry(dir) {
    cli_remote_dry('mkdir ' + deploy.remote.path + deploy.remote._version_path)
}

function remote_rmdir(dir) {
    cli_remote('rm -rf ' + deploy.remote.path + dir);
}

function remote_rmdir_dry(dir) {
    cli_remote_dry('rm -rf ' + deploy.remote.path + dir);
}


function remote_cp(from, to) {
    cli_remote('cp -r ' + deploy.remote.path + from + ' ' + deploy.remote.path + to);
    // cli('ssh ' + deploy.remote._host + ' \'cp -r ' + deploy.remote.path + from + ' ' + deploy.remote.path + to + '\'');
}

function remote_cp_dry(from, to) {
    cli_remote_dry('cp -r ' + deploy.remote.path + from + ' ' + deploy.remote.path + to);
    // log('command', 'ssh ' + deploy.remote._host + ' \'cp -r ' + deploy.remote.path + from + ' ' + deploy.remote.path + to + '\'');
}

function scp(from, to) {
	cli('scp -r ' + from + ' ' + deploy.remote._host + ':' + deploy.remote.path + to)
}

function scp_dry(from, to) {
	log('command', 'scp -r ' + from + ' ' + deploy.remote._host + ':' + deploy.remote.path + to)
}


function deployInit() {
	deploy = JSON.parse(fs.readFileSync('./deploy.json', 'utf8'));
	deploy.remote._host = deploy.remote.username + '@' + deploy.remote.server;
    deploy.remote.next_version = deploy.remote.latest_version === false ? 1 : deploy.remote.latest_version + 1;
    deploy.remote._version_path = 'apps/v' + deploy.remote.next_version;
}

function deployIncrementVersion() {
	var d = JSON.parse(fs.readFileSync('./deploy.json', 'utf8'));
    d.remote.latest_version = deploy.remote.latest_version === false ? 1 : deploy.remote.latest_version + 1;
    fs.writeFile('deploy.json', JSON.stringify(d, null, 4));
}


//
function deployBegin(is_real) {

    deployInit();

	log('message', 'deploy — start');

	if (remote_is_dir_exists(deploy.remote.path + deploy.remote._version_path)) {

		log('error', 'version #' + deploy.remote.next_version + ' already exists');

		return false;
	}

	log('message', 'deploy — creating new version');

	if (is_real) {
        remote_mkdir(deploy.remote._version_path);
	} else {
        remote_mkdir_dry(deploy.remote._version_path);
    }

    return true;



	// console.log('exists = ', is_dir_exists)

}



function deployEnd(is_real) {

    // log('message', 'deploy — ');

    // copy data from previous version
    if (deploy.remote.latest_version !== false) {

        log('message', 'deploy — copying data from latest version');

    	if (!remote_is_dir_exists(deploy.remote.path + 'apps/v' + deploy.remote.latest_version)) {

    		log('error', 'latest version #' + deploy.remote.latest_version + ' doesn\'t exist');

    		return false;
    	}

        if (is_real) {
            remote_cp('apps/v' + deploy.remote.latest_version + '/data', deploy.remote._version_path + '/data');
			propel_remote('diff');
			propel_remote('migrate');
			composer_remote('dump-autoload -o');
        } else {
            remote_cp_dry('apps/v' + deploy.remote.latest_version + '/data', deploy.remote._version_path + '/data');
			propel_remote_dry('diff');
			propel_remote_dry('migrate');
			composer_remote_dry('dump-autoload -o');
        }

    } else {

        log('message', 'deploy — no latest version to copy data from, copying from local');

        if (is_real) {
            scp('./data', deploy.remote._version_path + '/data');
        } else {
            scp_dry('./data', deploy.remote._version_path + '/data');
        }


    }

    log('message', 'deploy — finish');

}




module.exports = function(state) {
    const ERRORMSG = 'Please make sure you\'ve indicated correct parameters'

    if (typeof state === 'undefined') {
        log('error', ERRORMSG);
        return;
    }

    switch(state) {
        case 'dry':



            log('message', 'Running dry-run');

            // const read = (dir) =>
            //   fs.readdirSync(dir)
            //     .reduce((files, file) =>
            //       fs.statSync(path.join(dir, file)).isDirectory() ?
            //         files.concat(read(path.join(dir, file))) :
            //         files.concat(path.join(dir, file)),
            //       []);
            //
            // console.log(read('./'))

            if(!deployBegin(false)) {
                log('message', 'deploy — abort')
                return;
            }

            log('message', 'deploy — building assets');
            cli('NODE_ENV=production webpack');

            log('message', 'deploy — copying project files');
            scp_dry('./assets', deploy.remote._version_path + '/assets');
            scp_dry('./inc', deploy.remote._version_path + '/inc');
            scp_dry('./modules', deploy.remote._version_path + '/modules');
            scp_dry('./templates', deploy.remote._version_path + '/templates');
            scp_dry('./composer.json', deploy.remote._version_path + '/composer.json');
            scp_dry('./composer.phar', deploy.remote._version_path + '/composer.phar');
            scp_dry('./webpack.php', deploy.remote._version_path + '/webpack.php');
            scp_dry('./index.php', deploy.remote._version_path + '/index.php');
            scp_dry('./.htaccess', deploy.remote._version_path + '/.htaccess');

            cli_remote_dry('php ' + deploy.remote.path + deploy.remote._version_path + '/composer.phar install');
			propel_remote_dry('config:convert');

            // scp_dry('./webpack.php', deploy.remote._version_path + '/webpack.php');
            // scp_dry('./.htaccess', deploy.remote._version_path . '/.htacess');

            deployEnd(false);

            // cli('rsync --dry-run -az --force --delete --progress --exclude-from=rsync_exclude -e "ssh -p 22" ' + deploy.remote.username + '@' + deploy.remote.server + ':' + deploy.remote.path + ' ./');
            break;
        case 'new':
            log('message', 'Running actual deploy');
            // cli('rsync -az --force --delete --progress --exclude-from=rsync_exclude -e "ssh -p22" ./ username@server:/var/www/website-name');
            if(!deployBegin(true)) {
                log('message', 'deploy — abort')
                return;
            }

            log('message', 'deploy — building assets');
            cli('NODE_ENV=production webpack');

            log('message', 'deploy — copying project files');
            scp('./assets', deploy.remote._version_path + '/assets');
            scp('./inc', deploy.remote._version_path + '/inc');
            scp('./modules', deploy.remote._version_path + '/modules');
            scp('./templates', deploy.remote._version_path + '/templates');
            // scp('./vendor', deploy.remote._version_path + '/vendor');
            scp('./composer.json', deploy.remote._version_path + '/composer.json');
            scp('./composer.phar', deploy.remote._version_path + '/composer.phar');
            scp('./webpack.php', deploy.remote._version_path + '/webpack.php');
            scp('./index.php', deploy.remote._version_path + '/index.php');
            scp('./config.php', deploy.remote._version_path + '/config.php');
            scp('./.htaccess', deploy.remote._version_path + '/.htaccess');

			composer_remote('install');
            propel_remote('config:convert')

            deployEnd(true);
            break;

        case 'switch':

            deployInit();

        	if (!remote_is_dir_exists(deploy.remote.path + deploy.remote._version_path)) {

        		log('error', 'version #' + deploy.remote.next_version + ' doesn\'t exists');

        		return false;
        	}

            var deploy_htaccess = fs.readFileSync('./' + deploy.remote.root_htaccess.filename, 'utf8')

            deploy_htaccess = deploy_htaccess.replace(/{{{version}}}/g, 'v' + deploy.remote.next_version);
            deploy_htaccess = deploy_htaccess.replace(/{{{site}}}/g, deploy.remote.root_htaccess.site);

            fs.writeFileSync('./converted.htaccess', deploy_htaccess, 'utf8');
            scp('./converted.htaccess', '.htaccess');
            fs.unlinkSync('./converted.htaccess');

            //
            var deploy_app_php = fs.readFileSync('./' + deploy.remote.root_app.filename, 'utf8')

            deploy_app_php = deploy_app_php.replace(/{{{version}}}/g, 'v' + deploy.remote.next_version);

            fs.writeFileSync('./converted.app.php', deploy_app_php, 'utf8');
            scp('./converted.app.php', 'app.php');
            fs.unlinkSync('./converted.app.php');

            deployIncrementVersion();

            // echo 'Some Text' | ssh user@remotehost "cat > /remotefile.txt"
            // cli_remote_dry('cat > ' + deploy.remote.path + '.htaccess', 'echo \'' + deploy_htaccess + '\' | ')
            // cli_remote_dry('echo "' + deploy_htaccess + '" >> ' + deploy.remote.path + '.htaccess')

            break;

		case 'cleanup':

			deployInit();
			// cli_remote_dry('rm -rf')
			var apps = remote_list_apps();

			apps = apps.filter(function(x) {
				return
					(x !== ('v' + deploy.remote.latest_version)) &&
					(x !== ('v' + (deploy.remote.latest_version-1)));
			})

			for (var ai in apps) {
				remote_rmdir(apps[ai]);
			}

			// console.log(apps)

			break;

        default:
            log('error', ERRORMSG);
            return;
    }
}
