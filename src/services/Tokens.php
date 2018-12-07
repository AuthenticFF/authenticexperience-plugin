<?php
namespace authenticff\authenticexperience\services;

use yii\base\Component;
use craft\elements\Entry;

class Tokens extends Component
{


  /**
   * A listing of all our experience endpoints
   */
  protected $allExperienceEndpoints = [
    'https://boilerplate.amplify.authenticff.com',
    'https://www.76commercecenter.com',
    'https://www.northernstacksmn.com',
    'https://tritenre.com'
  ];

  /**
   * An error message we return if validation fails
   */
  public $validationErrorMessage = false;



  public function getToken($token)
  {

    $entries = Entry::find()
      ->section("expTokens")
      ->with(["expTokenTeam"])
      ->search([
        "attribute" => "expTokenToken",
        "query" => $token,
        "exact" => true,
        "subLeft" => false,
        "subRight" => false
      ])
      ->one();

    if($entries)
    {
      return $entries;
    }

    else
    {
      return false;
    }

  }

  /**
   * Making sure our token has the necessary relationships
   */
  public function validateTokenRelationships($tokenEntry)
  {

    // Validate Teams

    if(! count($tokenEntry->expTokenTeam))
    {
      $this->validationErrorMessage = "Token not assigned to team.";
      return false;
    }

    // Validate Projects
    $teamEntry = $tokenEntry->expTokenTeam[0];

    if(! count($teamEntry->expTeamProjects))
    {
      $this->validationErrorMessage = "Team not assigned to projects.";
      return false;
    }

    return true;

  }

  /**
   * Getting our validation error message
   */
  public function getValidationErrorMessage()
  {

    $message = $this->validationErrorMessage;

    $this->validationErrorMessage = false;

    return $message;

  }

  /**
   * Getting our team data
   */
  public function getTeamData($tokenEntry)
  {

    $teamData = false;

    if(count($tokenEntry->expTokenTeam))
    {

      $teamEntry = $tokenEntry->expTokenTeam[0];

      $teamData = [
        "teamTitle" => $teamEntry->title,
        "teamDisplayName" => $teamEntry->expTeamDisplayName,
        "teamImage" => count($teamEntry->expTeamImage) ? $teamEntry->expTeamImage->first()->url : false,
        "teamBio" => $teamEntry->expTeamBio,
        "teamProjects" => count($teamEntry->expTeamProjects) ? array_map('intval', $teamEntry->expTeamProjects->ids()) : false,
      ];

    }

    return $teamData;

  }

  /**
   * Getting our experience ids
   */
  public function getExperiencesData($tokenEntry)
  {

    return [];

  }


  /**
   * Fetching our remote endpoints
   */
  public function getRemoteTokenData($token)
  {

    $remoteTokenData = false;

    foreach ($this->allExperienceEndpoints as $url) {

      if($remoteTokenData !== false)
      {
        continue;
      }

      // making sure we don't double request with a local url
      if($url === \Craft::$app->request->getHostInfo())
      {
        continue;
      }

      $remoteTokenData = $this->_makeRemoteEndpointRequest($url, $token);

    }

    return $remoteTokenData;

  }

  /**
   * Get image markers
   */
  public function getImageMarkers($localMarkersOnly = true)
  {

    $imageMarkerData = [];
    $localImageMarkers = $this->_getLocalImageMarkers();
    $imageMarkerData = array_merge($imageMarkerData, $localImageMarkers);

    if($localMarkersOnly === false)
    {
      $remoteImageMarkers = $this->_getRemoteImageMarkers();
      $imageMarkerData = array_merge($imageMarkerData, $remoteImageMarkers);
    }

    return $imageMarkerData;

  }

  /**
   * Private Methods
   */

  public function _makeRemoteEndpointRequest($url, $token)
  {

    $client = new \GuzzleHttp\Client();
    $uri = '/actions/authentic-experience/tokens/get-remote-token-data';

    $response = $client->request('GET', $url . $uri, [
      "query" => [
        "activationToken" => $token
      ]
    ]);

    $body = $response->getBody();
    $jsonBody = json_decode($body);

    // error with token or configuration
    if(isset($jsonBody->error))
    {
      return false;
    }

    // we're good
    elseif($jsonBody->data->hasToken)
    {
      // converting Std class back to an array
      return json_decode(json_encode($jsonBody->data), true);
    }

    // no token found
    else
    {
      return false;
    }

  }

  /**
   * Returning our local image markers
   */
  public function _getLocalImageMarkers()
  {

    $entries = Entry::find()
      ->section("expTokens")
      ->with("expTokenImageMarker")
      ->all();

    if($entries)
    {

      $markers = [];

      foreach($entries as $tokenEntry)
      {
        if(count($tokenEntry->expTokenImageMarker))
        {
          $markers[] = [
            "url" => $tokenEntry->expTokenImageMarker[0]->url,
            "token" => $tokenEntry->expTokenToken,
            "siteUrl" => \Craft::$app->request->getHostInfo()
          ];
        }
      }

      return $markers;

    }

    else
    {
      return false;
    }

  }

  /**
   * Getting our remote image markers
   */
  public function _getRemoteImageMarkers()
  {

    $remoteMarkerData = [];

    foreach ($this->allExperienceEndpoints as $url)
    {

      // making sure we don't double request with a local url
      if($url === \Craft::$app->request->getHostInfo())
      {
        continue;
      }

      $endpointMarkerData = $this->_makeRemoteImageMarkerRequest($url);
      $remoteMarkerData = array_merge($remoteMarkerData, $endpointMarkerData["imageMarkers"]);

    }

    return $remoteMarkerData;

  }

  /**
   * Our actual request for getting remote image markers
   */
  public function _makeRemoteImageMarkerRequest($url)
  {
    $client = new \GuzzleHttp\Client();
    $uri = '/actions/authentic-experience/tokens/get-local-image-markers';

    $response = $client->request('GET', $url . $uri);

    $body = $response->getBody();
    $jsonBody = json_decode($body);

    // error with token or configuration
    if(isset($jsonBody->error))
    {
      return false;
    }

    // we're good
    else
    {
      // converting Std class back to an array
      return json_decode(json_encode($jsonBody->data), true);
    }

  }

}
