
// var Viewer = require("./Viewer");
// var Photosphere = require("./Photosphere");

// <script src="../node_modules/three/build/three.js"></script>
// <script src="../node_modules/d.js/lib/D.js"></script>
// <script src="../node_modules/uevent/uevent.js"></script>
// <script src="../node_modules/dot/doT.js"></script>
// <script src="../node_modules/nosleep.js/dist/NoSleep.js"></script>
// <script src="../node_modules/three/examples/js/renderers/CanvasRenderer.js"></script>
// <script src="../node_modules/three/examples/js/renderers/Projector.js"></script>
// <script src="../node_modules/three/examples/js/controls/DeviceOrientationControls.js"></script>
// <script src="../node_modules/three/examples/js/effects/StereoEffect.js"></script>
// <script src="../dist/photo-sphere-viewer.js"></script>

window.THREE = require("three");
require ("three/examples/js/renderers/CanvasRenderer");
require ("three/examples/js/renderers/Projector");
require("photo-sphere-viewer");

window._ = require("underscore");

var PhotoSphereViewer = require("photo-sphere-viewer");

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

              //
              // Init'ing our Editaable Table. Attaching some events, and tweaking the javascript slightly.
              //
              var $editableTable = $('#' + self.options.smartModelFeaturesNamespacedId);
              var $editableTableClass = $editableTable.data("editable-table");
              $editableTableClass = self.addMethodToEditableTable($editableTable);

              // Binding events to our capture button
              $editableTable.on("dblclick", "[name*='featureCoordinates']", function(){

                // adding coordinates
                var $el = $(this);
                var coordinates = self.photosphere.getPosition();
                coordinates = [coordinates.longitude, coordinates.latitude];
                $el.val(coordinates);

                // updating marker
                var rowIndex = $el.parent().parent().data("id");
                self.updateMarker(rowIndex, coordinates);

              });

              // handing our delete row action - we need to bind onto mousedown
              // because the event is already removed if we bind onto the "click" event
              $editableTable.on("mousedown", "a.delete", function(){
                var $el = $(this);
                var rowIndex = $el.parent().parent().data("id");
                self.removeMarker(rowIndex);
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

          var self = this;
          var $viewerEl = $("#fields-gltf-viewer .viewer");
          this.markers = this.getMarkers();

          this.photosphere = new PhotoSphereViewer({
            container: $viewerEl[0],
            panorama: fileUrl,
            anim_speed: '0rpm',
            default_long: '-80deg',
            default_lat: '-10deg',
            markers: this.markers
          });

          // moving marker
          this.photosphere.on("select-marker", function(marker, dblClick){
            self.photosphere.gotoMarker(marker, 400);
          });

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
            self.initPhotosphereViewer(data.url);
          })

        },

        /**
         * Returning our makers / coordinates from the features array
         */
        getMarkers: function(){

          var self = this;
          var markers = [];

          $.each(this.options.smartModelFeaturesRows, function(index){
            var coordinates = this.featureCoordinates.value.split(",");
            var newMarker = self.buildMarkerObject(index, coordinates);
            markers.push(newMarker);
          });

          return markers;

        },

        /**
         * Removing a marker from the UI after it's been removed from the table
         */
        removeMarker: function(rowIndex){

          // Removing a marker from our list
          this.markers = _.without(this.markers, _.findWhere(this.markers, {
            id: "marker-" + rowIndex
          }));

          // getting the marker from photosphere
          var marker = this.photosphere.getMarker("marker-" + rowIndex);

          // removing the marker from photosphere
          this.photosphere.removeMarker(marker);

        },

        /**
         * Updating an individual marker
         */
        updateMarker: function(rowIndex, coordinates){

          var index = _.findIndex(this.markers, { id: "marker-" + rowIndex });

          if(index > -1){

            // updating an existing marker
            this.markers[index].longitude = coordinates[0];
            this.markers[index].latitude = coordinates[1];

          }else{

            var newMarker = this.buildMarkerObject(rowIndex, coordinates);

            // adding a new marker
            this.markers.push(newMarker);
            this.photosphere.addMarker(newMarker);

          }

          this.refreshMarkers();

        },

        /**
         * Looping over all our markers and updating them
         */
        refreshMarkers: function(){

          var self = this;

          $.each(this.markers, function(index){
            var marker = this;
            self.photosphere.updateMarker(marker);
          });

        },

        /**
         * Returning properties for a new marker
         */
        buildMarkerObject: function(rowIndex, coordinates){

          return {
            id: "marker-" + rowIndex,
            circle: 10,
            latitude: coordinates[1],
            longitude: coordinates[0],
            style: {
              fill: 'rgba(255, 255, 255, 0.8)'
            }
          };

        },

        /**
         * This is a method to attach onto the Craft CMS editableTable javascript class,
         * so that we can pass data to prepopulate into a new table row.
         */
        addMethodToEditableTable: function($editableTable){


          // Overriding addRowWithValues so we can pass some default values
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
