<?php
require 'soundcloudDownloader.php';
$sc = new soundcloudDownloader();

$limit = 20; // this parameter into all methods is optional
$offset = 0; // this parameter into all methods is optional

// create client id
$client_id = $sc->clientId();
// set a custom client id
$sc->setClientId('YOUR_CLIENT_ID');
// download a track by page URL :
$track_data1 = $sc->urlData('https://soundcloud.com/therug13/silent-heart');
// you can also use a shorted URL :
$track_data2 = $sc->urlData('https://soundcloud.app.goo.gl/CmeEF');
// if you have track ID can use ( track ) method
$track_data3 = $sc->track('1304378476');
// access to a track :
$track_data4 = $sc->urlData('https://soundcloud.com/therug13/silent-heart');
// access to an album :
$album_data = $sc->urlData('https://soundcloud.com/therug13/sets/relaxing-with-trap');
// search into soundcloud
$search_data = $sc->search("drake",$limit,$offset);
// access to an artist :
$artist_data = $sc->urlData('https://soundcloud.com/therug13');
$artist_id = $artist_data['user']['id'];
// artist data methods:
$albums = $sc->userAlbums($artist_id,$limit,$offset);
$top_tracks = $sc->userTopTracks($artist_id,$limit,$offset);
$tracks = $sc->userTracks($artist_id,$limit,$offset);
$spotlights = $sc->userSpotlights($artist_id,$limit,$offset);
$followings = $sc->userFollowings($artist_id,$limit,$offset);
$likes = $sc->userLikes($artist_id,$limit,$offset);
$featured_profiles = $sc->userFeaturedProfiles($artist_id,$limit,$offset);
$reposts = $sc->userReposts($artist_id,$limit,$offset);
