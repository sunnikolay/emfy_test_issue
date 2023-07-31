<?php

include_once 'config.php';
include_once 'OAuth2.php';

if($_GET['secret'] != $my_secret) die('access denied');

header("Access-Control-Allow-Origin: *");

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use League\OAuth2\Client\Token\AccessTokenInterface;

$accessToken = getToken();

$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

$apiClient->setAccessToken($accessToken)
    ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
    ->onAccessTokenRefresh(
        function (AccessTokenInterface $accessToken, $baseDomain) {
            saveToken(
                [
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $baseDomain,
                ]
            );
        }
    );

$lead_id = (int)$_GET['id'];

echo "<table style=\"font-family: arial, sans-serif; border-collapse: collapse; width: 100%; \">
<caption style=\"font-weight: bold; padding: 8px;\">All Products Lead #".$lead_id."</caption>
  <tr style=\"background-color: #dddddd;\">
    <th style=\"border: 1px solid #dddddd; text-align: left; padding: 8px;\">Name</th>
    <th style=\"border: 1px solid #dddddd; text-align: left; padding: 8px;\">Quantity</th>
  </tr>";
try {
	$service = $apiClient->leads();
	$lead = $service->getOne( $lead_id );
	$links = $apiClient->leads()->getLinks($lead);

	$i = 1;

	foreach($links->toArray() as $key=>$value){
		$i++;
	    if ( $value['to_entity_type'] == 'catalog_elements' ) {
	    	$catalogElement = $apiClient->catalogElements($value['metadata']['catalog_id'])->getOne($value['to_entity_id']);
	    if($i % 2 == 0)echo "<tr>";
	    else echo "<tr style=\"background-color: #dddddd;\">";
	    echo "<td style=\"border: 1px solid #dddddd; text-align: left; padding: 8px;\">".$catalogElement->toArray()['name']."</td>
	    <td style=\"border: 1px solid #dddddd; text-align: left; padding: 8px;\">".$value['metadata']['quantity']."</td>
	    </tr>";
	    }
	}
}
catch(AmoCRMMissedTokenException $e) {
	echo "AmoCRMMissedTokenException: " . $e->getMessage();
}
catch(AmoCRMoAuthApiException $e) {
	echo "AmoCRMoAuthApiException: " . $e->getMessage();
}
catch(AmoCRMApiException $e) {
	echo "AmoCRMApiException: " . $e->getMessage();
}

echo "</table>";



