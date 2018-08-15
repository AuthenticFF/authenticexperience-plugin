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

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SmartModelField::class;
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
          \authenticff\authenticexperience\fields\SmartModel::class,
          'craftQlGetFieldSchema',
          function (\markhuot\CraftQL\Events\GetFieldSchema $event) {

          $field = $event->sender;
          //
          // $object = $event->schema->createObjectType("SmartModel");
          // $object->addField('smartModelAsset');

          // ->type(VolumeInterface::class);
          // ->use(new AssetQueryArguments)
          // ->lists();
          // $object->addField("smartModelFeatures");
          // var_dump($object);

          // $event->schema
          //   ->addField($field)
          //   ->type($object);

          $object = $event->schema->createObjectType("SmartModel");

          $object->addField('assets')
            ->type(VolumeInterface::class)
            ->use(new AssetQueryArguments)
            ->lists()
            ->resolve(function($root, $args) {

              // $root is the `craft\base\Field` object. So you can call ->normalizeValue() here if you want
              $values = $root->normalizeValue();

              $criteria = \craft\elements\Asset::find();
              $criteria = $criteria->id($values['smartModelAsset']);
              return $criteria->all();

            });

          // $object->addField('features')
          //   ->type(/* this might need to be another custom type */)
          //   ->lists()
          //   ->resolve(function($root, $args) {
          //
          //   });

          $event->schema
            ->addField($field)
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
