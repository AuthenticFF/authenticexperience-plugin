
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

              /* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */

              var fileUrl = self.options.assetUrl + "/models/acoma.glb";
              console.log(fileUrl);

              //
              var $viewerEl = $("#fields-gltf-viewer .viewer");
              var options = {};
              var viewer = new Viewer($viewerEl[0], options);

              viewer
               .load(fileUrl, "", new Map())
               .catch((e) => this.onError(e))
               .then(cleanup);

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
