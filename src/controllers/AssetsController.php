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
class AssetsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'get-asset'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/authentic-experience/assets/do-something
     *
     * @return mixed
     */
    public function actionGetAsset()
    {

        $assetId = Craft::$app->request->getParam("id", false);
        $asset = \craft\elements\Asset::find()->id($assetId)->first();
        return $this->asJson($asset);

    }
}
