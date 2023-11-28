# soundcloudDownloader
by using this library you can access to soundcloud contents and their download urls

## How use
you need a HTTPS connection for access to soundcloud's resources then you must include soundcloudDownloader.php into your file and use library methods
```php
require 'path/to/soundcloudDownloader.php';
```
check examples.php for testing library

## Download a music
you must use two methods for this proccess
```php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();
// you need access to track page first :
$track_data = $sc->url('https://soundcloud.com/therug13/silent-heart');
// all available download links
$download_links = $track_data['download'];

// download a url
// please use download() method for download automatically progressive or stream urls
$path = 'my_music.mp3';
$file_content = $sc->download($download_links[0]['download']);
// download :
file_put_contents($path,$file_content);
```

## a simple request
you can download a track with two ways :
```php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();
// #1 download a track by page URL :
$track_data = $sc->url('https://soundcloud.com/therug13/silent-heart');
//    you can also use a shorted URL :
$track_data = $sc->url('https://soundcloud.app.goo.gl/CmeEF');

// #2 if you have track ID can use ( track ) method
$track_data = $sc->track('1304378476');
```

## access to any soundcloud page
this method returns any contents data with their URL
```php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();
// access to a track :
$track_data = $sc->url('https://soundcloud.com/therug13/silent-heart');
// access to an album :
$album_data = $sc->url('https://soundcloud.com/therug13/sets/relaxing-with-trap');
// access to an artist :
$artist_data = $sc->url('https://soundcloud.com/therug13');
```

## search into soundcloud
you can use a string to search it value into soundcloud database
```php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();

$limit = 20; // this parameter into search() method is optional
$offset = 0; // this parameter into search() method is optional

$search_data = $sc->search("drake",$limit,$offset);
```

## client id
library automaticaly will generate a client id for any requests but you can create it value as separately
```php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();
$client_id = $sc->clientId();
```
also if you have a client id you can set it into your requests by this method
```php
$sc->setClientId('YOUR_CLIENT_ID');
```

## access to an artist data
bottom methods helps you to get diffrent data about an artist
```php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();

// access to an artist :
$artist_data = $sc->url('https://soundcloud.com/therug13');
$artist_id = $artist_data['user']['id'];

$limit = 20; // this parameter into all methods is optional
$offset = 0; // this parameter into all methods is optional

// artist data methods:
$albums = $sc->userAlbums($artist_id,$limit,$offset);
$top_tracks = $sc->userTopTracks($artist_id,$limit,$offset);
$tracks = $sc->userTracks($artist_id,$limit,$offset);
$spotlights = $sc->userSpotlights($artist_id,$limit,$offset);
$followings = $sc->userFollowings($artist_id,$limit,$offset);
$likes = $sc->userLikes($artist_id,$limit,$offset);
$featured_profiles = $sc->userFeaturedProfiles($artist_id,$limit,$offset);
$reposts = $sc->userReposts($artist_id,$limit,$offset);
```
