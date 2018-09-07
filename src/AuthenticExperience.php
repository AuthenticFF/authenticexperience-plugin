<?php
/**
 * Authentic Experience plugin for Craft CMS 3.x
 *
 * Authentic Experience
 *
 * @link      https://authenticff.com
 * @copyright Copyright (c) 2018 Authentic F&F
 */

namespace authenticff\authenticexperience;

use authenticff\authenticexperience\fields\SmartModel as SmartModelField;
use authenticff\authenticexperience\fields\SmartPhotosphere as SmartPhotosphereField;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\View;
use yii\base\Event;

use markhuot\CraftQL\Types\VolumeInterface;
use markhuot\CraftQL\FieldBehaviors\AssetQueryArguments;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Authentic F&F
 * @package   AuthenticExperience
 * @since     0.1
 *
 */
class AuthenticExperience extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * AuthenticExperience::$plugin
     *
     * @var AuthenticExperience
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.1';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * AuthenticExperience::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
          'endpoints' => \authenticff\authenticexperience\services\Endpoints::class,
        ]);

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SmartModelField::class;
                $event->types[] = SmartPhotosphereField::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );


        Event::on(
          \authenticff\authenticexperience\fields\SmartPhotosphere::class,
          'craftQlGetFieldSchema',
          function (\markhuot\CraftQL\Events\GetFieldSchema $event) {

          $event->handled = true;
          $field = $event->sender;
          $schema = $event->schema;

          $object = $schema->createObjectType("SmartPhotosphere");

          //
          // Assets field
          //
          $object->addField('assets')
            ->type(VolumeInterface::class)
            ->lists()
            ->resolve(function($root, $args) {
              $criteria = \craft\elements\Asset::find();
              $criteria = $criteria->id($root['smartPhotosphereAsset']);
              return $criteria->all();
            });

          //
          // Features Photosphere
          //
          $featuresObject = $schema->createObjectType("FeaturesModel");

          // title
          $featuresObject->addField("title")
            ->resolve(function($root, $args){
              return $root["featureTitle"];
            });

          // body
          $featuresObject->addStringField("body")
            ->resolve(function($root, $args){
              return $root["featureBody"];
            });

          // coordinates
          $coordinatesModel = $schema->createObjectType("CoordinatesModel");
          $coordinatesModel->addField("latitude")
            ->resolve(function($root, $args){
              return explode(",", $root)[1];
            });
          $coordinatesModel->addField("longitude")
            ->resolve(function($root, $args){
              return explode(",", $root)[0];
            });

          $featuresObject->addStringField("coordinates")
            ->type($coordinatesModel)
            ->resolve(function($root, $args){
              return $root["featureCoordinates"];
            });


          //
          // Adding features to schema
          //
          $object->addField('features')
            ->type($featuresObject)
            ->lists()
            ->resolve(function($root, $args) {

              if(empty($root["smartPhotosphereFeatures"]))
              {
                return [];
              }

              else
              {
                return $root["smartPhotosphereFeatures"];
              }

            });

          $schema->addField($field)
            ->type($object);

        }
      );

      /**
       * Registering our templates
       */
      Event::on(
        View::class,
        View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
        function(RegisterTemplateRootsEvent $event) {
          $event->roots['authenticexperience'] = __DIR__ . '/templates';
        }
      );

      Event::on(
        View::class,
        View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
        function(RegisterTemplateRootsEvent $event) {
          $event->roots['authenticexperience'] = __DIR__ . '/templates';
        }
      );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'authentic-experience',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
