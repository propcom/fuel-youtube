# YouTube package for FuelPHP #

## Instalation ##

Go to your version in the command line and type
```sh
$ install-package propcom/fuel-youtube
```

Add to composer.json
```php
"require": {
  "google/apiclient": "1.1.*"
}
```

and update packages:
```sh
$ composer update
```

### Configure ###

Example *fuel/app/youtube.php*:
```php
<?php

return [
	'api_key' => '<your_api_key>',
	'user' => '<your_youtube_username>',
];
```
- **api_key** Google Developers API key. Required.
- **user** Youtube username

## Usage ##

Get latest uploads
```php
\Package::load('youtube');
$user = \Youtube\User::forge(\Config::get('youtube.user'));
$videos = $user->videos(['max_results' => 5]);
```

### View ###

```php
<?php if ($videos): ?>
    <div class="js_youtube_videos">
        <?php foreach ($videos as $video): ?>
            <div class="js_youtube_video">
                <a href="<?= $video->get_url() ?>" class="two columns mt50 ml80"><img src="<?= $video['thumbnails'][1] ?>" width="120" height="90" alt="<?= $video->get_title() ?>"></a>
                <div class="three columns mt50 omega">
                    <h6 class="red"><a href="<?= $video->get_url() ?>"><?= $video->get_title() ?></a></h6>
                    <p><a href="<?= $video->get_url() ?>"><?= $video->get_description() ?></a></p>
                    <p><a href="<?= $video->get_author_url() ?>"><?= $video->get_author() ?></a></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
```

## YouTube Documentation ##

https://developers.google.com/youtube/

https://github.com/google/google-api-php-client
