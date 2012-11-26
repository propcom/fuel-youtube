# YouTube package for FuelPHP


## Usage

At the moment this package is only able to pull out the latest videos uploaded by a user.


### Controller

    <?php
    
    \Package::load('youtube');
    
    $params = array(
    	'max-results' => 5,
    );
    
    $videos = \Youtube\Feeds::get_videos($params);


### View

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


## YouTube Documentation

https://developers.google.com/youtube/
