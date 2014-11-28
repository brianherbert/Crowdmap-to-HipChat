<?php
require 'config.php';

mb_internal_encoding("UTF-8");
date_default_timezone_set('UTC');

require 'vendor/autoload.php';

$after = file_get_contents('timestamp');
// Next time, we'll get all posts after this timestamp
file_put_contents('timestamp', time());

$url = 'https://api.crowdmap.com/v1/posts/?fields=posts.message,media.file_location,media.filename_t&limit=100&after='.$after.'&apikey='.generate_signature('GET','/posts/');

$response = get($url,$headers);

$data = json_decode($response);
$data = $data->posts;

// Create the messages that we will post to HipChat
foreach($data AS $i => $post) {
	$url_to_post = 'https://crowdmap.com/post/'.$post->post_id.'/';
	$img = '';
	if(isset($post->media[0])) {
		$img = '<img src="'.$post->media[0]->file_location.''.$post->media[0]->filename_t.'"><br>';
	}
	$post->message = $img.strip_tags($post->message).' - <a href="'.$url_to_post.'">'.$url_to_post.'</a>';
}

if(count($data) < 1) {
	echo "No new posts.";
	die();
}else{
	echo "Proceeding to pass ".count($data)." to HipChat.\n";
}

use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

$auth = new OAuth2(HIPCHAT_TOKEN); // Crowdmap Posts Room
$client = new Client($auth);

$roomAPI = new RoomAPI($client);

//In case you need a quick look at the rooms available and their IDs
//$room = $roomAPI->getRooms(array('max-results' => 30));
//var_dump($room);

foreach($data AS $post) {
	$message = new Message();
	$message->setMessage($post->message);
	$roomAPI->sendRoomNotification('1009466', $message);
}

echo "Finished. Have a nice day!\n";

function generate_signature($http_method,$url) {
	$date = time();
	return 'A' . CM_PUBLIC . hash_hmac('sha1', "{$http_method}\n{$date}\n{$url}\n", CM_PRIVATE);
}

function get($url) {
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15); // Timeout set to 15 seconds. This is somewhat arbitrary and can be changed.
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); //Set curl to store data in variable instead of print
	curl_setopt($ch,CURLOPT_HTTPGET,true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($ch,CURLOPT_USERAGENT,'Crowdmap to HipChat Application v0');
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);

	$buffer = curl_exec($ch);
	curl_close($ch);

	return $buffer;
}



