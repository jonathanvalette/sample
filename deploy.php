<?php
namespace Deployer;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

require 'recipe/symfony.php';

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

// Le nom de votre projet
set('application', 'tuto-deployer'); /***** A COMPLETER *****/

// Hosts
host($_ENV['DEPLOYER_REPO_HOST'])
    ->hostname($_ENV['DEPLOYER_REPO_HOSTNAME'])
    ->set('deploy_path', '/var/www/html/{{application}}')
    ;

// Nombre de déploiements à conserver avant de les supprimer.
set('keep_releases', 4);

// Votre repo
set('repository', $_ENV['DEPLOYER_REPO_URL']);

set('bin_dir', 'bin');

set('clear_paths', ['var/cache']);

add('shared_files', ['.env.local']); // vous pouvez ajouter des fichiers partagés et surcharger la recette de base
add('shared_dirs', []); // vous pouvez ajouter des dossiers partagés et surcharger la recette de base

task('deploy:vendors', function () {
    if (!commandExist('unzip')) {
        writeln('<comment>To speed up composer installation setup "unzip" command with PHP zip extension https://goo.gl/sxzFcD</comment>');
    }
    run('cd {{release_path}} && {{bin/composer}} install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');

});

task('deploy:assets:install', function () {
    run('{{bin/php}} {{bin/console}} assets:install {{console_options}} --symlink');
})->desc('Install bundle assets');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:clear_paths',
    'deploy:shared',
    'deploy:vendors',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:assets:install',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');

after('deploy:failed', 'deploy:unlock');
