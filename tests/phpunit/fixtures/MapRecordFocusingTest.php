<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=76; */

/**
 * Fixture generator for "Map Record Focusing" Jasmine suite.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class FixturesTest_MapRecordFocusing extends Neatline_RecordsFixtureCase
{


    /**
     * `records.mapRecordFocusing.json`
     */
    public function testMapRecordFocusing()
    {

        $record = $this->__record($this->exhibit);
        $record->map_focus = '100,200';
        $record->map_zoom = 10;
        $record->save();

        $this->writeFixtureFromRoute('neatline/records',
            'records.mapRecordFocusing.json');

    }


}