
var Viewer = require("./Viewer");
var Photosphere = require("./Photosphere");

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

              console.log(self.options);
              return;

              /* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */

              var initGltf = function(){

                var viewerOptions = {};
                var fileUrl = self.options.assetUrl + "/models/acoma.glb";
                var $viewerEl = $("#fields-gltf-viewer .viewer");

                var viewer = new Viewer($viewerEl[0], viewerOptions);

                viewer
                 .load(fileUrl, "", new Map())
                 .catch((e) => this.onError(e))
                 .then(cleanup);

              }

              var initPhotosphere = function(){

                var $viewerEl = $("#fields-gltf-viewer .viewer");
                var fileUrl = self.options.assetUrl + "/photospheres/photosphere-outside-1.jpg";

                console.log(fileUrl);

            		var photosphere = new Photosphere($viewerEl, fileUrl, {
            			view: 75,
            			speed: 0,
            			y: 0
            		});

                window.onresize = photosphere.resize;

              }

              // initPhotosphere();

            });
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
