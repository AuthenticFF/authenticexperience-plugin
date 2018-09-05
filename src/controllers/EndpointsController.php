<?php
/**
 * Authentic Experience plugin for Craft CMS 3.x
 *
 * Authentic Experience
 *
 * @link      https://authenticff.com
 * @copyright Copyright (c) 2018 Authentic F&F
 */

namespace authenticff\authenticexperience\controllers;

use authenticff\authenticexperience\AuthenticExperience;

use Craft;
use craft\web\Controller;

/**
 * Assets Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Authentic F&F
 * @package   AuthenticExperience
 * @since     0.1
 */
class EndpointsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'get-endpoint'];

    // Public Methods
    // =========================================================================

    /**
     * This is called from our App to get our projects endpoint
     */
    public function actionGetEndpoint()
    {

      //$token = Craft::$app->request->getParam("token", false);
      $token = "default-team:default-project";
      $token = explode(":", $token);
      $teamToken = $token[0];
      $projectToken = $token[1];
      $endpoint = false;

      if($this->_hasTeamAndProject($teamToken, $projectToken))
      {
        $endpoint = Craft::$app->request->getHostInfo() . "/api";
      }

      else
      {
        $endpoint = AuthenticExperience::getInstance()->endpoints->getRemoteEndpoint($teamToken, $projectToken);
      }

      /**
       * Error Handlding
       */
      if($endpoint === false)
      {
        return $this->asJson([
          "error" => [
            "message" => "Endpoint not found"
          ]
        ]);
      }

      /**
       * Returning the correct endpoint
       */
      return $this->asJson([
        "data" => [
          "endpoint" => $endpoint
        ]
      ]);

    }

    /**
     * This is called from another Craft instance, to determine if we own the team and project
     */
    public function actionHasTeamAndProject()
    {

      $token = explode(":", $token);
      $teamToken = $token[0];
      $projectToken = $token[1];

      $hasTeamAndProject = $this->_hasTeamAndProject($teamToken, $projectToken);

      return $this->asJson([
        "data" => [
          "hasTeamAndProject" => $hasTeamAndProject
        ]
      ]);

    }

    /**
     * Private methods
     */

     /**
      * A non-action version of the method for internal use
      */
    public function _hasTeamAndProject($teamToken, $projectToken)
    {

      $hasTeamAndProject = true;

      if(! $team = AuthenticExperience::getInstance()->endpoints->getTeam($teamToken))
      {
        $hasTeamAndProject = false;
      }

      if(! $team = AuthenticExperience::getInstance()->endpoints->getProject($projectToken))
      {
        $hasTeamAndProject = false;
      }

      return $hasTeamAndProject;

    }


}
