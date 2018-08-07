
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

              // Binding to our select asset events
              $('#' + self.options.smartModelAssetNamespacedId).data('elementSelect').on('selectElements', function(e) {
                self.loadAsset(e.elements[0].id);
              });

              // Init'ing editable table
              var $editableTable = $('#' + self.options.smartModelFeaturesNamespacedId);
              var $editableTableClass = $editableTable.data("editable-table");
              $editableTableClass = self.addMethodToEditableTable($editableTable);

              // Binding events to our capture button
              $editableTable.find("a.btn.capture").on("click", function(){

                // adding coordinates
                var $el = $(this);
                var coordinates = self.photosphere.getCoordinates();
                $el.parent().prev().find("textarea").val(coordinates);

              });

              // If we already have an asset loaded, load it in the viewer
              if(self.options.assetUrl){
                self.initPhotosphereViewer(self.options.assetUrl);
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
         * Loading our photo viewer
         */
        initPhotosphereViewer: function(fileUrl){

          THREE.ImageUtils.crossOrigin = '';
          var $viewerEl = $("#fields-gltf-viewer .viewer");

          this.photosphere = new Photosphere($viewerEl, fileUrl, {
            view: 75,
            speed: 0,
            y: 0
          });

          window.onresize = this.photosphere.resize;

        },

        /**
         * After we have our asset id, loading the url
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
            self.initPhotosphereViewer(data.url);
          })

        },

        /**
         * This is a method to attach onto the Craft CMS editableTable javascript class,
         * so that we can pass data to prepopulate into a new table row.
         */
        addMethodToEditableTable: function($editableTable){

          $editableTable.addRowWithValues = function(values){

            if (!this.canAddRow()) {
                return;
            }

            var rowId = this.settings.rowIdPrefix + (this.biggestId + 1);
            var $tr = this.createRow(rowId, this.columns, this.baseName, $.extend({}, values));

            $tr.appendTo(this.$tbody);
            new Craft.EditableTable.Row(this, $tr);
            this.sorter.addItems($tr);

            // Focus the first input in the row
            $tr.find('input,textarea,select').first().trigger('focus');

            this.rowCount++;
            this.updateAddRowButton();

            // onAddRow callback
            this.settings.onAddRow($tr);

          }

          return $editableTable;

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
