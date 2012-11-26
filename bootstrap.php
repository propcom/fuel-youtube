<?php

\Config::load('youtube', true);

Autoloader::add_classes(array(
	'Youtube\Feeds' => __DIR__.'/classes/feeds.php',
));
