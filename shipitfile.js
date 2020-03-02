module.exports = function (shipit) {
    require('shipit-deploy')(shipit);
    require('shipit-shared')(shipit);
    var os = require('os');

    shipit.initConfig({
        default: {
          branch: process.env.BRANCH || 'master',
            deployTo: '/var/www/invoicing',
            repositoryUrl: 'git@vps51718.ovh.net:invoicing ',
            key: process.env['HOME'] + '/.ssh/id_rsa',
            keepReleases: 3,
            shallowClone: true,

            // These files/folders will not be transfered
            ignores: ['.git', 'node_modules',
                // usual folders for symfony applications
                'web/bundles',
                'web/app',
                'var/cache/.gitkeep',
                'var/logs',
                'web/app_dev.php',
                'web/config.php',
                'vendor',
                'node_modules'
            ],

            // Configuration for shipit-shared (great module !)
            shared: {
                overwrite: true,
                dirs: [
                    'logs/logs',
                    'app/data'
                ],
                files: [
                    'app/config/parameters.yml',
                    'src/Invoicing/Bundle/AppBundle/Resources/invoice/default-settings.yml',
                    'src/Invoicing/Bundle/AppBundle/Resources/views/invoice/finnish.twig',
                    'src/Invoicing/Bundle/AppBundle/Resources/views/invoice/english.twig',
                    'src/Invoicing/Bundle/AppBundle/Resources/views/invoice/aaa.gif'
                ]
            }
        },
        production: {
            servers: 'deployment@vps51721.ovh.net'
        }
    });

    shipit.on('fetched', function () {
    });

    shipit.on('sharedEnd', function () {
        shipit.start('frontend', 'composer:download', 'symfony:install');
    });

    shipit.on('published', function () {
        shipit.start('cache:opcode-clear')
    });

    shipit.task('cache:opcode-clear', function () {
        return cmdAtRemote('php bin/console cache:accelerator:clear --opcode  --env=prod');
    });

    shipit.blTask('composer:download', function () {
        return cmdAtRemote('curl -sS https://getcomposer.org/installer | php');
    });

    shipit.blTask('frontend', function () {
        return cmdAtRemote('npm install --production')
            .then(function () {
                return cmdAtRemote('npm run build');
            });
    });
    shipit.blTask('symfony:install', function() {
        return composerInstall()
            .then(clearCacheProd)
            .then(function() {
                return shipit.emit('cacheCleared');
            });

        function composerInstall() {
            shipit.log('Installing composer');
            return cmdAtRemote('SYMFONY_ENV=prod php composer.phar install --no-dev --verbose --prefer-dist --optimize-autoloader --no-progress --no-interaction')
                .then(function () {
                    return cmdAtRemote('rm composer.phar');
                })
                .then(function () {
                    cmdAtRemote('setfacl -Rdm u:apache:rwx var/cache', true);
                    cmdAtRemote('setfacl -Rdm u:deployment:rwx var/cache', true);
                });
        }

        function clearCacheProd() {
            shipit.log('Clear cache for prod');
            return cmdAtRemote('php bin/console cache:clear --env=prod --no-debug').then(function () {
            });
        }
    });

    function cmdAtRemote(cmd, sudo) {
        if (typeof sudo !== 'undefined' && sudo) {
            cmd = "sudo " + cmd;
        }

        return shipit.remote('cd ' + shipit.releasePath + ' && ' + cmd);
    }

    function cmdAtCurrent(cmd) {
        return shipit.remote('cd ' + shipit.config.deployTo + '/current && ' + cmd);
    }


    shipit.blTask('deploy:migrate', function() {
        return cmdAtCurrent('php bin/console doctrine:migrations:migrate --env=prod --no-interaction');
    });
};
