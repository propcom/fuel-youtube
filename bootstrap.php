<?php

\Config::load('youtube', true);

Autoloader::add_classes(array(
	'Youtube\Feeds' => __DIR__.'/classes/feeds.php',
	'Youtube\Video' => __DIR__.'/classes/video.php',
	'Youtube\User' => __DIR__.'/classes/user.php',
	'Youtube\Utils' => __DIR__.'/classes/utils.php',
));
