<?php
include_once 'config.php';
include_once 'vendor/autoload.php';

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use League\OAuth2\Client\Token\AccessToken;

$provider = new AmoCRM([
	'clientId' => $clientId, 'clientSecret' => $clientSecret, 'redirectUri' => $redirectUri,
]);
$accessToken = getToken();
$provider->setBaseDomain($accessToken->getValues()['baseDomain']);
if($accessToken->hasExpired()) {
	try {
		$accessToken = $provider->getAccessToken(new League\OAuth2\Client\Grant\RefreshToken(), [
			'refresh_token' => $accessToken->getRefreshToken(),
		]);
		saveToken([
			'accessToken' => $accessToken->getToken(), 'refreshToken' => $accessToken->getRefreshToken(),
			'expires' => $accessToken->getExpires(), 'baseDomain' => $provider->getBaseDomain(),
		]);
	}
	catch(Exception $e) {
		die((string)$e);
	}
}
function saveToken($accessToken)
{
	if(isset($accessToken)
	   && isset($accessToken['accessToken'])
	   && isset($accessToken['refreshToken'])
	   && isset($accessToken['expires'])
	   && isset($accessToken['baseDomain'])) {
		$data = [
			'accessToken' => $accessToken['accessToken'], 'expires' => $accessToken['expires'],
			'refreshToken' => $accessToken['refreshToken'], 'baseDomain' => $accessToken['baseDomain'],
		];
		file_put_contents(TOKEN_FILE, json_encode($data));
	}
	else {
		exit('Invalid access token '.var_export($accessToken, true));
	}
}

function getToken()
{
	$accessToken = json_decode(file_get_contents(TOKEN_FILE), true);
	if(isset($accessToken)
	   && isset($accessToken['accessToken'])
	   && isset($accessToken['refreshToken'])
	   && isset($accessToken['expires'])
	   && isset($accessToken['baseDomain'])) {
		return new AccessToken([
			'access_token' => $accessToken['accessToken'], 'refresh_token' => $accessToken['refreshToken'],
			'expires' => $accessToken['expires'], 'baseDomain' => $accessToken['baseDomain'],
		]);
	}
	else {
		exit('Invalid access token '.var_export($accessToken, true));
	}
}
