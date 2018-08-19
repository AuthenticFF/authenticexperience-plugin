
window._ = require("underscore");
var Viewer = require("./Viewer");

/**
 * Authentic Experience plugin for Craft CMS
 *
 * SmartModel Field JS
 *
 * @author    Authentic F&F
 * @copyright Copyright (c) 2018 Authentic F&F
 * @link      https://authenticff.com
 * @package   AuthenticExperience
 * @since     0.1AuthenticExperienceSmartModel
 */

 ;(function ( $, window, document, undefined ) {

    var pluginName = "AuthenticExperienceSmartModel",
        defaults = {
        };

    // Plugin constructor
    function Plugin( element, options ) {
        this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    Plugin.prototype = {


        init: function(id) {

            var self = this;

            $(function () {

              //
              // Binding to the Element Select field, to load our image after it's been selected
              //
              $('#' + self.options.smartModelAssetNamespacedId).data('elementSelect').on('selectElements', function(e) {
                self.loadAsset(e.elements[0].id);
              });

              // If we already have an asset loaded, load it in the viewer
              if(self.options.assetUrl){
                self.initGltfViewer(self.options.assetUrl);
              }

              return;

            });

        },

        /**
         * Loading the GLTF viewer
         */
        initGltfViewer: function(){

          var viewerOptions = {};
          var fileUrl = self.options.assetUrl + "/models/acoma.glb";
          var $viewerEl = $("#fields-gltf-viewer .viewer");

          var viewer = new Viewer($viewerEl[0], viewerOptions);

          viewer
           .load(fileUrl, "", new Map())
           .catch((e) => this.onError(e))
           .then(cleanup);

        },

        /**
         * After we have our asset id from the Element Selector, loading the url via ajax
         */
        loadAsset: function(assetId){

          var self = this;

          $.ajax({
            url: '/admin/actions/authentic-experience/assets/get-asset',
            type: 'GET',
            dataType: 'json',
            data: {
              id: assetId
            }
          })
          .done(function(data) {
            self.initGltfViewer(data.url);
          })

        }

    };




    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                new Plugin( this, options ));
            }
        });
    };



})( jQuery, window, document );
