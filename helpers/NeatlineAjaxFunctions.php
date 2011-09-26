<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Ajax helper functions.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      Bethany Nowviskie <bethany@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2011 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */
?>

<?php

/**
 * Query for items and crop down the result objects for the
 * item browser
 *
 * @return array of Omeka_records $items The items.
 */
function neatline_getItemsForBrowser(
    $search = null,
    $tags = null,
    $types = null,
    $collections = null,
    $all = null
)
{

    // If nothing is selected, return an empty array.
    if (!$all && $tags == null && $types == null && $collections == null) {
        return array();
    }

    $_db = get_db();
    $itemsTable = $_db->getTable('Item');

    $select = $itemsTable->getSelect();
    $params = array();

    if ($search) {
        $params['search'] = $search;
    }

    // Apply the search string to the table class.
    $itemsTable->applySearchFilters($select, $params);

    // Construct the final where clause.
    if (!$all) {

        $whereClause = array();

        // Build types clause.
        if ($types != null) {
            $typesString = implode(',', $types);
            $whereClause[] = 'item_type_id IN (' . $typesString . ')';
        }

        // Build collections clause.
        if ($collections != null) {
            $collectionsString = implode(',', $collections);
            $whereClause[] = 'collection_id IN (' . $collectionsString . ')';
        }

        // Build tags clause.
        if ($tags != null) {
            foreach ($tags as $id) {
                $whereClause[] = 'EXISTS (SELECT * FROM omeka_taggings '
                    . 'WHERE i.id = relation_id AND tag_id = ' . $id . ')';
            }
        }

        // Collapse into single string.
        $whereClause = implode(' OR ', $whereClause);

        if ($whereClause != '') {
            $select->where($whereClause);
        }

    }

    return $itemsTable->fetchObjects($select);

}

/**
 * Starting with the month/day and year values from the forms in
 * the Neatline editor, convert to timestamps for database store.
 *
 * @param array $date The json_decoded date array.
 *
 * @return array $dates A two-element associative array with 'start'
 * and 'end', each containing a 2011-09-26 16:37:00 stamp.
 */
function neatline_parseSemanticDates($date)
{

    // Pluck out the pieces.
    $startDate = $date['start']['date'];
    $startTime = $date['start']['year'];
    $endDate = $date['end']['date'];
    $endTime = $date['end']['year'];

    // Trim and axe double-spaces.
    foreach (array(
        $startDate,
        $startTime,
        $endDate,
        $endTime
    ) as $dateElement) {

        // Trim and lowercase.
        $dateElement = strtolower(trim($dateElement));

        // Replace commas with spaces.
        $dateElement = str_replace(',', ' ', $dateElement);

        // Split on spaces.
        $dateElement = explode(' ', $dateElement);

    }

    // Get the timestamps.
    $start = neatline_dateAndTimeToTimestamp($startDate, $startTime);
    $end = neatline_dateAndTimeToTimestamp($endDate, $endTime);

    return array(
        'start' => $start,
        'end' => $end
    );

}

/**
 * Generate a timestamp from two arrays, one containing string parts of
 * a date (potentially a month, day, and year) and another containing a time.
 *
 * @param array $date The date pieces array.
 * @param array $time The time pieces array.
 *
 * @return string $stamp The generated timestamp, in format 2011-09-26 16:37:00.
 */
function neatline_dateAndTimeToTimestamp($date, $time)
{



}
