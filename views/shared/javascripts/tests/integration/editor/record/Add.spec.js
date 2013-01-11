
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=76; */

/**
 * Tests for record add flow.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

describe('Record Form Add', function() {


  var els;


  beforeEach(function() {

    _t.loadEditor();

    els = {
      add:    _t.vw.menu.$('a[href="#records/add"]'),
      save:   _t.vw.record.$('a[name="save"]'),
      lead:   _t.vw.record.$('p.lead')
    };

    // Add record.
    _t.click(els.add);

  });


  it('should show the form when "New Record" is clicked', function() {

    // --------------------------------------------------------------------
    // When the "New Record" button is clicked, the record form should be
    // displayed with the model for the new record.
    // --------------------------------------------------------------------

    expect(_t.el.editor).toContain(_t.el.record);

  });


  it('should display placeholder in form header', function() {

    // --------------------------------------------------------------------
    // When an edit form is displayed for a record that has a null title
    // (as is the case when a new record is created), a placeholder title
    // should be displayed in the form header.
    // --------------------------------------------------------------------

    expect(els.lead.text()).toEqual(STRINGS.placeholders.title);

  });


  it('should generate well-formed POST request', function() {

    // --------------------------------------------------------------------
    // When a record is saved for the first time, the form should issue a
    // POST request with the new data.
    // --------------------------------------------------------------------

    // Click "Save".
    els.save.trigger('click');

    // Capture outoing request.
    var request = _t.getLastRequest();
    var params = $.parseJSON(request.requestBody);

    // Check method and route.
    expect(request.method).toEqual('POST');
    expect(request.url).toEqual(__exhibit.api.record);

  });


  it('should update the route after first save', function() {

    // --------------------------------------------------------------------
    // When a record is saved for the first time, the URL hash should be
    // updated to point to the records/:id resource for the new record.
    // --------------------------------------------------------------------

    // Click "Save".
    els.save.trigger('click');
    _t.respondNewRecord();

    // Get the id of the new record.
    var id = $.parseJSON(_t.json.record.add).id;

    // Check for updated route.
    expect(Backbone.history.fragment).toEqual('records/'+id);

  });


});