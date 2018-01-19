<?php
/*
	Author: kvaX
	Description: Teamspeak3 banner for server info (TS3 and SAMP)
	Last Update: 19.01.2018 16:00
	Contact: fb.com/almir.kvakic.10
*/
#TS3 ADMIN
include 'ts3admin.class.php';
include 'sampquery.class.php';

#Config
$ts3['ip'] = '51.255.4.229';
$ts3['qport'] = '10011';
$ts3['port'] = '9987';
$ts3['username'] = 'almirBOT';
$ts3['password'] = 'SJWADQEy';

$samp['ip'] = '51.255.4.229';
$samp['port'] = '7777';
/*----------------------------------------------------------------------*/
#SAMP
/*----------------------------------------------------------------------*/
$samp_query = new SampQueryAPI($samp['ip'], $samp['port']);

if($samp_query->isOnline()){
	$serverinfo 			  	= $samp_query->getInfo();
	$sampserver['players']   	= $serverinfo['players'];
	$sampserver['maxplayers'] 	= $serverinfo['maxplayers'];
	$sampserver['gamemode'] 	= $serverinfo['gamemode'];
	$sampserver['status']		= 'online';
}else{
	$sampserver['players']   	= 0;
	$sampserver['maxplayers'] 	= 0;
	$sampserver['status']		= 'offline';
	$sampserver['gamemode']		= 'offline';
}
/*----------------------------------------------------------------------*/

/*----------------------------------------------------------------------*/
#TS3
/*----------------------------------------------------------------------*/
$tsAdmin = new ts3admin($ts3['ip'], $ts3['qport']);
if($tsAdmin->getElement('success', $tsAdmin->connect())){
	$tsAdmin->login($ts3['username'], $ts3['password']);
	$tsAdmin->selectServer($ts3['port']);	
	$tsAdmin->setName("Lospions Bot by Almir");
	
	$serverinfo 			 	 = $tsAdmin->serverInfo();
	$ts3server['clients']    	 = $serverinfo['data']['virtualserver_clientsonline'] - $serverinfo['data']['virtualserver_queryclientsonline'];
    $ts3server['maxclients'] 	 = $serverinfo['data']['virtualserver_maxclients'];
	$ts3server['status']	 	 = $serverinfo['data']['virtualserver_status'];
	
	// Get all clients on ts3 server and get current player with that ip(Function: getRealIpAddr)
	$clients = $tsAdmin->clientList("-uid -ip -groups");
	for($i = 0; $i < count($clients['data']); $i++){
		if($clients['data'][$i]['connection_client_ip'] == getRealIpAddr()){
			$nick = $clients['data'][$i]['client_nickname'];
			$clientIP = $clients['data'][$i]['connection_client_ip'];
			$channel = $tsAdmin->channelInfo($clients['data'][$i]['cid'])['data']['channel_name'];
        }
	}
	
	if(!isset($clientIP)){
		$nick = 'Guest';
		$channel = 'You are not on TS3 Server!';
	}
}else{
	$ts3server['clients'] 		= 0;
	$ts3server['maxclients'] 	= 0;
	$ts3server['status']		= 'offline';
}

/*----------------------------------------------------------------------*/
#FUNCTIONS
/*----------------------------------------------------------------------*/
function getRealIpAddr(){
	if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}elseif(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function getCountry($ip){
	$drzava = file_get_contents('http://freegeoip.net/json/'.$ip);
	$drzava = json_decode($drzava, true);
	return $drzava['country_code'];	
}

function config_set($config_file, $section, $key, $value){
    $config_data = parse_ini_file($config_file, true);
    $config_data[$section][$key] = $value;
    $new_content = '';
    foreach ($config_data as $section => $section_content){
        $section_content = array_map(function($value, $key){
            return "$key=$value";
        }, array_values($section_content), array_keys($section_content));
        $section_content = implode("\n", $section_content);
        $new_content .= "[$section]\n$section_content\n";
    }
    file_put_contents($config_file, $new_content);
}

function Rekordi($get, $novi_rekord){
	$rekord = parse_ini_file('rekordi.txt', true);
	if($rekord[$get]['Rekord'] < $novi_rekord){
		config_set('rekordi.txt', $get, 'Rekord', $novi_rekord);
		config_set('rekordi.txt', $get, 'Datum', date('d.m.Y'));
	}
}
Rekordi('SAMP', $sampserver['players']);
Rekordi('TS3', $ts3server['clients']);
$rekord = parse_ini_file('rekordi.txt', true);

#Images
$image = @imagecreatefrompng('assets/images/bg.png');
$overlay = imagecreatefrompng('assets/images/bg-overlay.png');

#Fonts
$font = 'assets/fonts/manteka.ttf';
$font_overload = 'assets/fonts/d-la-cruz-font.ttf';
$font_overload2 = 'assets/fonts/impact.ttf';

#Colors
$color_black = imagecolorallocate($image, 0, 0, 0); 
$color_black2 = imagecolorallocate($image, 0, 0, 0); 
$color_blue  = imagecolorallocate($image, 26, 106, 156);
$color_blue2  = imagecolorallocate($image, 8, 117, 182);
$color_white = imagecolorallocate($image, 255, 255, 255);
$color_red   = imagecolorallocate($image, 255, 0, 0);
$color_orange = imagecolorallocate($image, 255, 165, 0);
$color_green = imagecolorallocate($image, 112, 219, 147);
$color_greenlime = imagecolorallocate($image, 50, 205, 50);
$color_yellow = imagecolorallocate($image, 255, 204, 0);
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 55);
$transparent2 = imagecolorallocatealpha($image, 0, 0, 0, 20);

#Image blur
$blurs = 7;
for ($i = 0; $i < $blurs; $i++) {
	imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
}

#TS3 INFO
if($ts3server['status'] == 'online'){$ts3_color = $color_green;$ts3_text = 'ONLINE';}else{$ts3_color = $color_red;$ts3_text = 'OFFLINE';}
imagecopymerge($image, $overlay, 536, 5, 0, 60, 258, 170, 50);
imagecopymerge($image, $overlay, 536, 5, 0, 60, 258	, 25, 50);
imagettftext($image, 10, 0, 600, 24, $color_white, $font, 'INFORMATION OF TS3');
imagettftext($image, 10, 0, 555, 50, $color_white, $font, 'STATUS:');
imagettftext($image, 10, 0, 620, 50, $ts3_color, $font, $ts3_text);
imagettftext($image, 10, 0, 555, 70, $color_white, $font, 'PLAYERS:');
imagettftext($image, 10, 0, 620, 70, $color_white, $font, $ts3server['clients'].' / '.$ts3server['maxclients']);
imagettftext($image, 10, 0, 555, 90, $color_white, $font, 'RECORD:');
imagettftext($image, 10, 0, 620, 90, $color_white, $font, $rekord['TS3']['Rekord']);
imagettftext($image, 10, 0, 555, 110, $color_white, $font, 'YOUR NAME:');
imagettftext($image, 10, 0, 640, 110, $color_yellow, $font, $nick);
imagettftext($image, 10, 0, 555, 130, $color_white, $font, 'CHANNEL:');
imagettftext($image, 10, 0, 625, 130, $color_white, $font, $channel);
imagettftext($image, 10, 0, 555, 150, $color_white, $font, 'COUNTRY:');
imagettftext($image, 10, 0, 625, 150, $color_orange, $font, getCountry(getRealIpAddr()));
imagettftext($image, 10, 0, 555, 170, $color_white, $font, 'YOUR IP:');
imagettftext($image, 10, 0, 615, 170, $color_orange, $font, getRealIpAddr());

#SAMP INFO
if($sampserver['status'] == 'online'){$samp_color = $color_green;$samp_text = 'ONLINE';}else{$samp_color = $color_red;$samp_text = 'OFFLINE';}
imagecopymerge($image, $overlay, 276, 5, 0, 60, 255, 170, 50);
imagecopymerge($image, $overlay, 276, 5, 0, 60, 255, 25, 50);
imagettftext($image, 10, 0, 330, 24, $color_white, $font, 'INFORMATION OF SAMP');
imagettftext($image, 10, 0, 295, 50, $color_white, $font, 'STATUS:');
imagettftext($image, 10, 0, 360, 50, $samp_color, $font, $samp_text);
imagettftext($image, 10, 0, 295, 70, $color_white, $font, 'PLAYERS:');
imagettftext($image, 10, 0, 360, 70, $color_white, $font, $sampserver['players'].' / '.$sampserver['maxplayers']);
imagettftext($image, 10, 0, 295, 90, $color_white, $font, 'RECORD:');
imagettftext($image, 10, 0, 360, 90, $color_white, $font, $rekord['SAMP']['Rekord']);
imagettftext($image, 10, 0, 295, 110, $color_white, $font, 'MODE VERSION:');
imagettftext($image, 10, 0, 405, 110, $color_white, $font, $sampserver['gamemode']);

#SERVERS INFO
imagecopymerge($image, $overlay, 5, 5, 0, 60, 265, 170, 50);
imagecopymerge($image, $overlay, 5, 5, 0, 60, 265, 25, 50);
imagettftext($image, 10, 0, 100, 24, $color_white, $font, 'SERVERS');
imagettftext($image, 10, 0, 80, 50, $color_yellow, $font, 'SAMP SERVER');
imagettftext($image, 10, 0, 75, 70, $color_white, $font, $samp['ip'].':'.$samp['port']);
imagettftext($image, 10, 0, 80, 90, $color_yellow, $font, 'TS3 SERVER');
imagettftext($image, 10, 0, 75, 110, $color_white, $font, $ts3['ip'].':'.$ts3['port']);
imagettftext($image, 10, 0, 80, 130, $color_yellow, $font, 'CS 1.6 SERVER');
imagettftext($image, 10, 0, 75, 150, $color_white, $font, 'Soon!');

imagecopymerge($image, $overlay, 0, 260, 0, 30, 800, 25, 50);
imagettftext($image, 10, 0, 20, 280, $color_white, $font, 'It is currently:');
imagettftext($image, 10, 0, 150, 280, $color_green, $font, date('m.d.Y H:i:s'));
imagettftext($image, 10, 0, 295, 280, $color_white, $font, 'and our samp server has');
imagettftext($image, 10, 0, 485, 280, $color_green, $font, $sampserver['players']);
imagettftext($image, 10, 0, 530, 280, $color_white, $font, 'players. Please join us!');

imagettftext($image, 40, 0, 120, 235, $color_white, $font_overload2, 'BLACK SCHOOL COMMUNITY');

#header
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);