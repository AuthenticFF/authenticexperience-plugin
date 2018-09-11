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
    protected $allowAnonymous = ['get-token-data', 'get-remote-token-data'];

    // Public Methods
    // =========================================================================

    /**
     * An action endpoint that returns us our token information: endpoint, team data, accessible projects, accessible experiences
     */
    public function actionGetTokenData()
    {

      $token = Craft::$app->request->getParam("activationToken", false);

      $endpoint = false;
      $teamData = false;

      if($tokenEntry = AuthenticExperience::getInstance()->endpoints->getToken($token))
      {

        if(AuthenticExperience::getInstance()->endpoints->validateTokenRelationships($tokenEntry))
        {

          // Get local token data
          $endpoint = Craft::$app->request->getHostInfo() . "/api";
          $teamData = AuthenticExperience::getInstance()->endpoints->getTeamData($tokenEntry);

        }

        else
        {

          return $this->asJson([
            "error" => [
              "message" => AuthenticExperience::getInstance()->endpoints->getValidationErrorMessage()
            ]
          ]);

        }

      }

      else
      {
          $teamData = AuthenticExperience::getInstance()->endpoints->getRemoteTokenData($token);
      }

      if($endpoint === false)
      {
        return $this->asJson([
          "error" => [
            "message" => "Token not found"
          ]
        ]);
      }

      /**
       * Returning the correct endpoint
       */
      return $this->asJson([
        "data" => [
          "endpoint" => $endpoint,
          "team" => $teamData
        ]
      ]);

    }

    /**
     * This is called from another Craft instance, to get our remote token data
     */
    public function actionGetRemoteTokenData()
    {

      $token = Craft::$app->request->getParam("activationToken", false);
      $endpoint = false;
      $teamData = false;

      if($tokenEntry = AuthenticExperience::getInstance()->endpoints->getToken($token))
      {

        if(AuthenticExperience::getInstance()->endpoints->validateTokenRelationships($tokenEntry))
        {

          // Get local token data
          $endpoint = Craft::$app->request->getHostInfo() . "/api";
          $teamData = AuthenticExperience::getInstance()->endpoints->getTeamData($tokenEntry);

        }

        else
        {

          return $this->asJson([
            "error" => [
              "message" => AuthenticExperience::getInstance()->endpoints->getValidationErrorMessage()
            ]
          ]);

        }

      }

      else
      {

        /**
         * Token not found here
         */
        return $this->asJson([
          "data" => [
            "hasToken" => false
          ]
        ]);

      }

      /**
       * Returning the correct endpoint
       */
      return $this->asJson([
        "data" => [
          "hasToken" => true,
          "endpoint" => $endpoint,
          "team" => $teamData
        ]
      ]);

    }


}
