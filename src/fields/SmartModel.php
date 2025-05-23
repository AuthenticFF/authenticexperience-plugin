<?php
/**
 * Authentic Experience plugin for Craft CMS 3.x
 *
 * Authentic Experience
 *
 * @link      https://authenticff.com
 * @copyright Copyright (c) 2018 Authentic F&F
 */

namespace authenticff\authenticexperience\fields;

use authenticff\authenticexperience\AuthenticExperience;
use authenticff\authenticexperience\assetbundles\smartmodelfield\SmartModelFieldAsset;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;
use craft\elements\Asset;

/**
 * SmartModel Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Authentic F&F
 * @package   AuthenticExperience
 * @since     0.1
 */
class SmartModel extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */
    public $someAttribute = 'Some Default';

    public $smartModelData = [
      "smartModelAssetId" => [],
      "features" => []
    ];

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('authentic-experience', 'SmartModel');
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            // ['someAttribute', 'string'],
            // ['someAttribute', 'default', 'value' => 'Some Default'],
        ]);
        return $rules;
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_JSON;
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {

      if (is_string($value)) {

        // decoding our json
        $smartModelData = json_decode($value, true);

      }

      else {

        $smartModelData = $value;

      }

      // var_dump($value);
      // die();

      // $smartModelData = [
      //   "smartModelAssetId" => 1,
      //   "features" => [
      //     [
      //       "featureTitle" => "Feature Title 1",
      //       "featureBody" => "Feature Body 1",
      //       "featureCoordinates" => [0,0,1]
      //     ]
      //   ]
      // ];

      return $smartModelData;

    }

    /**
     * Modifies an element query.
     *
     * This method will be called whenever elements are being searched for that may have this field assigned to them.
     *
     * If the method returns `false`, the query will be stopped before it ever gets a chance to execute.
     *
     * @param ElementQueryInterface $query The element query
     * @param mixed                 $value The value that was set on this field’s corresponding [[ElementCriteriaModel]] param,
     *                                     if any.
     *
     * @return null|false `false` in the event that the method is sure that no elements are going to be found.
     */
    public function serializeValue($value, ElementInterface $element = null)
    {

        // var_dump($value);
        // var_dump(Craft::$app->request);

        // var_dump( Craft::$app->request->getParam("fields[smartModelFeatures]") );
        // die();

        // die();

        // $features = [];
        // $fieldExists = true;
        // $index = 0;
        //
        // while ($fieldExists) {
        //
        //   $featureTitle = Craft::$app->request->getParam('fields[smartModelFeatures][$index]["featureTitle"]', false)
        //   $featureBody = Craft::$app->request->getParam('fields[smartModelFeatures][$index]["featureBody"]', false)
        //   $featureCoordinates = Craft::$app->request->getParam('fields[smartModelFeatures][$index]["featureCoordinates"]', false)
        //
        //   if(! $featureTitle)
        //   {
        //     $fieldExists = false;
        //   }
        //   else
        //   {
        //       $features[] = [
        //         "featureTitle" => $featureTitle,
        //         "featureBody" => $featureBody,
        //         "featureCoordinates" => $featureCoordinates
        //       ]
        //   }
        //
        // }

        // fields[smartModelAsset]:
        // $smartModelAssetId = Craft::$app->request->getParam("fields[smartModelAsset][]", []);
        // fields[smartModelFeatures]:
        // fields[smartModelFeatures][0][featureTitle]: a
        // fields[smartModelFeatures][0][featureBody]: b
        // fields[smartModelFeatures][0][featureCoordinates]: c

        // $smartModelData = [
        //   "smartModelAssetId" => 2,
        //   "features" => [
        //     [
        //       "featureTitle" => "Feature Title 1",
        //       "featureBody" => "Feature Body 1",
        //       "featureCoordinates" => [0,0,1]
        //     ]
        //   ]
        // ];

        return parent::serializeValue($value, $element);
    }

    /**
     * Returns the component’s settings HTML.
     *
     * An extremely simple implementation would be to directly return some HTML:
     *
     * ```php
     * return '<textarea name="foo">'.$this->getSettings()->foo.'</textarea>';
     * ```
     *
     * For more complex settings, you might prefer to create a template, and render it via
     * [[\craft\web\View::renderTemplate()]]. For example, the following code would render a template loacated at
     * craft/plugins/myplugin/templates/_settings.html, passing the settings to it:
     *
     * ```php
     * return Craft::$app->getView()->renderTemplate('myplugin/_settings', [
     *     'settings' => $this->getSettings()
     * ]);
     * ```
     *
     * If you need to tie any JavaScript code to your settings, it’s important to know that any `name=` and `id=`
     * attributes within the returned HTML will probably get [[\craft\web\View::namespaceInputs() namespaced]],
     * however your JavaScript code will be left untouched.
     *
     * For example, if getSettingsHtml() returns the following HTML:
     *
     * ```html
     * <textarea id="foo" name="foo"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * …then it might actually look like this before getting output to the browser:
     *
     * ```html
     * <textarea id="namespace-foo" name="namespace[foo]"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * As you can see, that JavaScript code will not be able to find the textarea, because the textarea’s `id=`
     * attribute was changed from `foo` to `namespace-foo`.
     *
     * Before you start adding `namespace-` to the beginning of your element ID selectors, keep in mind that the actual
     * namespace is going to change depending on the context. Often they are randomly generated. So it’s not quite
     * that simple.
     *
     * Thankfully, [[\craft\web\View]] service provides a couple handy methods that can help you deal
     * with this:
     *
     * - [[\craft\web\View::namespaceInputId()]] will give you the namespaced version of a given ID.
     * - [[\craft\web\View::namespaceInputName()]] will give you the namespaced version of a given input name.
     * - [[\craft\web\View::formatInputId()]] will format an input name to look more like an ID attribute value.
     *
     * So here’s what a getSettingsHtml() method that includes field-targeting JavaScript code might look like:
     *
     * ```php
     * public function getSettingsHtml()
     * {
     *     // Come up with an ID value for 'foo'
     *     $id = Craft::$app->getView()->formatInputId('foo');
     *
     *     // Figure out what that ID is going to be namespaced into
     *     $namespacedId = Craft::$app->getView()->namespaceInputId($id);
     *
     *     // Render and return the input template
     *     return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'settings'     => $this->getSettings()
     *     ]);
     * }
     * ```
     *
     * And the _settings.html template might look like this:
     *
     * ```twig
     * <textarea id="{{ id }}" name="foo">{{ settings.foo }}</textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('{{ namespacedId }}');
     * </script>
     * ```
     *
     * The same principles also apply if you’re including your JavaScript code with
     * [[\craft\web\View::registerJs()]].
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'authentic-experience/_components/fields/SmartModel_settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     *
     * An extremely simple implementation would be to directly return some HTML:
     *
     * ```php
     * return '<textarea name="'.$name.'">'.$value.'</textarea>';
     * ```
     *
     * For more complex inputs, you might prefer to create a template, and render it via
     * [[\craft\web\View::renderTemplate()]]. For example, the following code would render a template located at
     * craft/plugins/myplugin/templates/_fieldinput.html, passing the $name and $value variables to it:
     *
     * ```php
     * return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *     'name'  => $name,
     *     'value' => $value
     * ]);
     * ```
     *
     * If you need to tie any JavaScript code to your input, it’s important to know that any `name=` and `id=`
     * attributes within the returned HTML will probably get [[\craft\web\View::namespaceInputs() namespaced]],
     * however your JavaScript code will be left untouched.
     *
     * For example, if getInputHtml() returns the following HTML:
     *
     * ```html
     * <textarea id="foo" name="foo"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * …then it might actually look like this before getting output to the browser:
     *
     * ```html
     * <textarea id="namespace-foo" name="namespace[foo]"></textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('foo');
     * </script>
     * ```
     *
     * As you can see, that JavaScript code will not be able to find the textarea, because the textarea’s `id=`
     * attribute was changed from `foo` to `namespace-foo`.
     *
     * Before you start adding `namespace-` to the beginning of your element ID selectors, keep in mind that the actual
     * namespace is going to change depending on the context. Often they are randomly generated. So it’s not quite
     * that simple.
     *
     * Thankfully, [[\craft\web\View]] provides a couple handy methods that can help you deal with this:
     *
     * - [[\craft\web\View::namespaceInputId()]] will give you the namespaced version of a given ID.
     * - [[\craft\web\View::namespaceInputName()]] will give you the namespaced version of a given input name.
     * - [[\craft\web\View::formatInputId()]] will format an input name to look more like an ID attribute value.
     *
     * So here’s what a getInputHtml() method that includes field-targeting JavaScript code might look like:
     *
     * ```php
     * public function getInputHtml($value, $element)
     * {
     *     // Come up with an ID value based on $name
     *     $id = Craft::$app->getView()->formatInputId($name);
     *
     *     // Figure out what that ID is going to be namespaced into
     *     $namespacedId = Craft::$app->getView()->namespaceInputId($id);
     *
     *     // Render and return the input template
     *     return Craft::$app->getView()->renderTemplate('myplugin/_fieldinput', [
     *         'name'         => $name,
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'value'        => $value
     *     ]);
     * }
     * ```
     *
     * And the _fieldinput.html template might look like this:
     *
     * ```twig
     * <textarea id="{{ id }}" name="{{ name }}">{{ value }}</textarea>
     *
     * <script type="text/javascript">
     *     var textarea = document.getElementById('{{ namespacedId }}');
     * </script>
     * ```
     *
     * The same principles also apply if you’re including your JavaScript code with
     * [[\craft\web\View::registerJs()]].
     *
     * @param mixed                 $value           The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {

      //
      // Setting up feature rows
      //
      $featureRows = [];
      foreach ($value["smartModelFeatures"] as $key => $feature)
      {

        $featureRows[] = [
          "featureIndex" => [
            "value" => $key
          ],
          "featureTitle" => [
            "value" => $feature["featureTitle"]
          ],
          "featureBody" => [
            "value" => $feature["featureBody"]
          ],
          "featureCoordinates" => [
            "value" => $feature["featureCoordinates"]
          ],
          "featureCaptureCoordinates" => [
              "value" => '<a href="javascript:;" class="btn capture">Capture</a>' ,
              "class" => "thin"
          ]
        ];

      }

      //
      // Setting up our Asset Data
      //

      $assetElements = false;

      if( ! empty($value["smartModelAsset"]) ){
        $assetElements = \craft\elements\Asset::find()->id($value["smartModelAsset"]);
      }


      // Register our asset bundle
      Craft::$app->getView()->registerAssetBundle(SmartModelFieldAsset::class);

      //
      // Top level field info
      //
      $name = $this->handle;
      $id = Craft::$app->getView()->formatInputId($this->handle);
      $namespacedId = Craft::$app->getView()->namespaceInputId($id);

      //
      // Setting up the names, ids, and namespaces for our fields
      //
      $smartModelAssetName = $this->handle . "[smartModelAsset]";
      $smartModelAssetId = Craft::$app->getView()->formatInputId("smartModelAssetId");
      $smartModelAssetNamespacedId = Craft::$app->getView()->namespaceInputId($smartModelAssetId);

      $smartModelFeaturesName = $this->handle . "[smartModelFeatures]";
      $smartModelFeaturesId = Craft::$app->getView()->formatInputId("smartModelFeaturesId");
      $smartModelFeaturesNamespacedId = Craft::$app->getView()->namespaceInputId($smartModelFeaturesId);

      // old asset url
      // $assetUrl = \Craft::$app->assetManager->getPublishedUrl('@authenticff/authenticexperience/assetbundles/smartmodelfield/dist', true);

      //
      // Variables for javascript
      //
      $jsonVars = [
        'smartModelAssetName' => $smartModelAssetName,
        'smartModelAssetId' => $smartModelAssetId,
        'smartModelAssetNamespacedId' => $smartModelAssetNamespacedId,
        'smartModelAssetElements' => $value["smartModelAsset"],
        'smartModelAssetElementType' => Asset::class,
        'smartModelFeaturesName' => $smartModelFeaturesName,
        'smartModelFeaturesId' => $smartModelFeaturesId,
        'smartModelFeaturesNamespacedId' => $smartModelFeaturesNamespacedId,
        'smartModelFeaturesRows' => $featureRows,
        'prefix' => Craft::$app->getView()->namespaceInputId(''),
        'assetUrl' => $assetElements ? $assetElements[0]->url : false
        ];
      $jsonVars = Json::encode($jsonVars);

      // attach to top-level field
      Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').AuthenticExperienceSmartModel(" . $jsonVars . ");");

      //
      // Variables for input
      //
      $variables = [];
      $variables["smartModelAssetName"] = $smartModelAssetName;
      $variables["smartModelAssetId"] = $smartModelAssetId;
      $variables["smartModelAssetNamespacedId"] = $smartModelAssetNamespacedId;
      $variables["smartModelAssetElements"] = $assetElements;
      $variables["smartModelAssetElementType"] = Asset::class;
      $variables["smartModelAssetSources"] = ["handle:". "experience"];

      $variables["smartModelFeaturesName"] = $smartModelFeaturesName;
      $variables["smartModelFeaturesId"] = $smartModelFeaturesId;
      $variables["smartModelFeaturesNamespacedId"] = $smartModelFeaturesNamespacedId;
      $variables["smartModelFeaturesRows"] = $featureRows;

      // Render the input template
      return Craft::$app->getView()->renderTemplate(
          'authentic-experience/_components/fields/SmartModel_input',
          $variables
      );

    }
}
