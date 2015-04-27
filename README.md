# YouTube package for FuelPHP #

## Instalation ##

### Install this package ###

### Install Google APIs Client Library ###

Add to composer.json
```php
"require": {
  "google/apiclient": "1.1.*"
}
```

and update packages:
```sh
composer install
```

### Configure ###

Example *youtube.php*:
```php
<?php
return [
	'api_key' => '<your_api_key>',
];
```
- **api_key** Google Developers API key. If OAuth token is not provided, this key must be set

## Usage ##

At the moment this package is only able to pull out the latest videos uploaded by a user.


### Controller ###

    <?php
    
    \Package::load('youtube');
    
    $params = array(
    	'max-results' => 5,
    );
    
    $videos = \Youtube\Feeds::get_videos($params);


### View ###

    <?php if ($videos): ?>
    	<div class="js_youtube_videos">
    		<?php foreach ($videos as $video): ?>
    			<div class="js_youtube_video">
    				<a href="<?= $video['url'] ?>" class="two columns mt50 ml80"><img src="<?= $video['thumbnails'][1] ?>" width="120" height="90" alt="<?= $video['title'] ?>"></a>
    				<div class="three columns mt50 omega">
    					<h6 class="red"><a href="<?= $video['url'] ?>"><?= $video['title'] ?></a></h6>
    					<p><a href="<?= $video['url'] ?>"><?= $video['description'] ?></a></p>
    					<p><a href="<?= $video['author_url'] ?>">Our Youtube Channel</a></p>
    				</div>
    			</div>
    		<?php endforeach; ?>
    	</div>
    <?php endif; ?>


## YouTube Documentation ##

https://developers.google.com/youtube/
