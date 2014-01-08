<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


/**
 * Construct exhibit globals array.
 *
 * @param NeatlineExhibit $exhibit The exhibit.
 * @return array The array of globals.
 */
function nl_globals($exhibit)
{

    // Get style defaults from `styles.ini`.
    $styles = new Zend_Config_Ini(NL_DIR.'/styles.ini');

    return array('neatline' => array(

        // EXHIBIT
        // --------------------------------------------------------------------

        'exhibit'           => $exhibit->toArray(),

        // API ENDPOINTS
        // --------------------------------------------------------------------

        'record_api'        => public_url('neatline/records'),
        'exhibit_api'       => public_url('neatline/exhibits/'.$exhibit->id),
        'item_search_api'   => public_url('items/browse'),
        'item_body_api'     => public_url('neatline/items'),

        // CONSTANTS
        // --------------------------------------------------------------------

        'per_page'          => (int) get_plugin_ini('Neatline', 'per_page'),
        'styles'            => $styles->toArray(),

        // LAYERS
        // --------------------------------------------------------------------

        'spatial_layers'    => nl_getLayersForExhibit($exhibit),

        // STRINGS
        // --------------------------------------------------------------------

        'strings' => array(

            'record' => array(
                'save' => array(
                    'success' => __('The record was saved successfully!'),
                    'error' => __('ERROR: The record was not saved.'),
                ),
                'remove' => array(
                    'success' => __('The record was deleted successfully!'),
                    'error' => __('ERROR:Tthe record was not deleted.'),
                ),
                'add' => array(
                    'success' => __('The record was created successfully!'),
                    'error' => __('ERROR: The record was not saved.'),
                ),
                'placeholders' => array(
                    'title' => __('[Untitled]'),
                )
            ),

            'exhibit' => array(
                'save' => array(
                    'success' => __('The exhibit was saved successfully!'),
                    'error' => __('ERROR: The exhibit was not saved.'),
                )
            ),

            'styles' => array(
                'save' => array(
                    'success' => __('The styles were saved successfully!'),
                    'error' => __('ERROR: The styles were not saved.'),
                )
            ),

            'svg' => array(
                'parse' => array(
                    'success' => __('SVG parsed successfully!'),
                    'error' => __('ERROR: SVG could not be parsed.'),
                )
            )

        )

    ));

}
