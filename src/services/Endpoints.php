<?php
namespace authenticff\authenticexperience\services;

use yii\base\Component;
use craft\elements\Entry;

class Endpoints extends Component
{


  /**
   * A listing of all our experience endpoints
   */
  protected $allExperienceEndpoints = [
    'https://amplify.authenticff.com'
  ];

  /**
   * Getting a team based on the token provided
   */
  public function getTeam($token)
  {

    $entries = Entry::find()
        ->section('expTeams')
        ->slug($token)
        ->one();

    if(count($entries))
    {
      return $entries;
    }

    else
    {
      return false;
    }

  }

  /**
   * Getting the project based on the slug
   */
  public function getProject($token)
  {

    $entries = Entry::find()
        ->section('expProjects')
        ->slug($token)
        ->one();

    if(count($entries))
    {
      return $entries;
    }

    else
    {
      return false;
    }

  }

  /**
   * Fetching our remote endpoints
   */
  public function getRemoteEndpoint($teamToken, $projectToken)
  {

    return false;

  }

}
