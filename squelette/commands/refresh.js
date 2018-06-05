const log = require('../log.js');
const cli = require('../cli.js');
const propel = require('../propel.js');

module.exports = function (what) {

    // refresh propel
    switch (what) {
        case 'db-model':
            if (!propel('model:build')) {
                return;
            }
            cli('composer dump-autoload -o');
            break;
        case 'db-config':
            propel('config:convert');
            break;
        default:
            log('message', 'uknown property to refresh');
            break;
    }

}
