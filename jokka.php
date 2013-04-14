<?php

//include the composer autoloader
$autoloader = require_once __DIR__ . '/vendor/autoload.php';

//add 'our' source directory to the autoloader
$autoloader->add('PharCompiler', 'src');

//create a new applocation instance
$application = new Symfony\Component\Console\Application("
 _______    _____      _   __	  _   __        _
|__   __|  / ___ \    | | / /    | | / /       / \
   | |	  / /   \ \   | |/ /     | |/ /       / / \
   | |   | |    | |   | | /      | | /       / /_\ \
   | |   | |    | |   | |\ \     | |\ \     / _____ \
 __| |   \ \___/ /    | | \ \    | | \ \   / /     \ \
|____/    \_____/     |_|  \_\   |_|  \_\ /_/       \_\ 
" . "
(c) Jeremy Mills <jeremy.mills89@gmail.com>
(c) Insu Mun <mis8680@gmail.com>
(c) Carlie Hiel <carlie.hiel@gmail.com>
Jokka is a complete symfony based phar compiling tool.
Jokka is built off the premises of (c) Jeremy Perret\'s <jeremy@devstar.org> Empir php compiling tool.
phar.readonly must be set to 0 within your php.ini in order to run", '1.0.0');

//adds one or more command objects
$application->addCommands(array(
    new PharCompiler\Console\Make(),
    
));

//runs the application
$application->run();


