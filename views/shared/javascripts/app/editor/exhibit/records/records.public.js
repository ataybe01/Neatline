
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=76; */

/**
 * Records list public API.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

Neatline.module('Editor.Exhibit.Records', function(
  Records, Neatline, Backbone, Marionette, $, _) {


  /**
   * Append the list to the editor container.
   *
   * @param {Object} container: The container element.
   */
  var display = function(container) {
    Records.__view.showIn(container);
  };
  Neatline.commands.addHandler('RECORDS:display', display);


  /**
   * Query for new records.
   *
   * @param {Object} params: The query parameters.
   */
  var load = function(params) {
    Records.__collection.update(params, function(records) {
      ingest(records);
    });
  };
  Neatline.commands.addHandler('RECORDS:load', load);


  /**
   * Render a records collection in the list.
   *
   * @param {Object} records: The collection of records.
   */
  var ingest = function(records) {
    Records.__view.ingest(records);
  };
  Neatline.commands.addHandler('RECORDS:ingest', ingest);


  /**
   * Navigate to the record list.
   */
  var navToList = function() {
    Records.__router.navigate('records', true);
  };
  Neatline.commands.addHandler('RECORDS:navToList', navToList);


  /**
   * Get a record model from the collection.
   *
   * @param {Number} id: The record id.
   * @param {Function} cb: A callback, called with the model.
   */
  var getModel = function(id, cb) {
    Records.__collection.getOrFetch(id, cb);
  };
  Neatline.reqres.addHandler('RECORDS:getModel', getModel);


});