
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=76; */

/**
 * Search public API.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

Neatline.module('Editor.Exhibit.Search', function(
  Search, Neatline, Backbone, Marionette, $, _) {


  /**
   * Append the form to the editor container.
   *
   * @param {Object} container: The container element.
   */
  var display = function(container) {
    Search.__view.showIn(container);
  };
  Neatline.commands.setHandler('SEARCH:display', display);


  /**
   * Initialize the record list from route parameters.
   *
   * @param {String} query: The search query.
   * @param {Number} start: The paging offset.
   */
  var initialize = function(query, start) {

    query = query || null;
    start = start || 0;

    // Set the search query.
    Search.__view.setQueryFromUrl(query);

    // Load the record list.
    if (!Search.__view.mirroring) {
      Neatline.execute('RECORDS:load', _.extend(Search.__view.query, {
        limit:  Neatline.globals.page_length,
        offset: start
      }));
    }

  };
  Neatline.commands.setHandler('SEARCH:initialize', initialize);


  /**
   * If mirroring is enabled, render the map collection in the browser.
   */
  var mirrorMap = function(records) {

    // Get the record collection on the map.
    records = records || Neatline.request('MAP:getRecords');

    // Render in the record browser.
    if (records && Search.__view.mirroring) {
      Neatline.execute('RECORDS:ingest', records);
    }

  };
  Neatline.commands.setHandler('SEARCH:mirrorMap', mirrorMap);
  Neatline.vent.on('MAP:ingest', mirrorMap);


  /**
   * Get the current query as a route parameter.
   *
   * @return {String}: The query.
   */
  var getQueryForUrl = function() {
    return Search.__view.getQueryForUrl();
  };
  Neatline.reqres.setHandler('SEARCH:getQueryForUrl', getQueryForUrl);


});
