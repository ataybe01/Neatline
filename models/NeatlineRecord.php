<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class NeatlineRecord extends Neatline_Row_Expandable
    implements Zend_Acl_Resource_Interface
{


    public $owner_id = 0;
    public $item_id;
    public $exhibit_id;
    public $added;
    public $modified;
    public $is_coverage = 0;
    public $is_wms = 0;
    public $slug;
    public $title;
    public $body;
    public $coverage;
    public $tags;
    public $widgets;
    public $presenter;
    public $fill_color;
    public $fill_color_select;
    public $stroke_color;
    public $stroke_color_select;
    public $fill_opacity;
    public $fill_opacity_select;
    public $stroke_opacity;
    public $stroke_opacity_select;
    public $stroke_width;
    public $point_radius;
    public $zindex;
    public $weight;
    public $start_date;
    public $end_date;
    public $after_date;
    public $before_date;
    public $point_image;
    public $wms_address;
    public $wms_layers;
    public $min_zoom;
    public $max_zoom;
    public $map_zoom;
    public $map_focus;


    /**
     * Required style attributes set in `styles.ini`.
     */
    protected static $requiredStyles = array(
        'presenter',
        'fill_color',
        'fill_color_select',
        'stroke_color',
        'stroke_color_select',
        'fill_opacity',
        'fill_opacity_select',
        'stroke_opacity',
        'stroke_opacity_select',
        'stroke_width',
        'point_radius'
    );


    /**
     * Set exhibit and item references.
     *
     * @param NeatlineExhibit $exhibit The exhibit record.
     * @param Item $item The item record.
     */
    public function __construct($exhibit=null, $item=null)
    {

        parent::__construct();

        // Set exhibit and item foreign keys.
        if (!is_null($exhibit)) $this->exhibit_id = $exhibit->id;
        if (!is_null($item)) $this->item_id = $item->id;

        // Read style defaults from `styles.ini`.
        $styles = new Zend_Config_Ini(NL_DIR.'/styles.ini');

        // Set default styles.
        foreach (self::$requiredStyles as $prop) {
            if (is_null($this->$prop)) $this->$prop = $styles->$prop;
        }

    }


    /**
     * Get the parent exhibit record.
     *
     * @return NeatlineExhibit The parent exhibit.
     */
    public function getExhibit()
    {
        return get_record_by_id('NeatlineExhibit', $this->exhibit_id);
    }


    /**
     * Get the parent item record.
     *
     * @return Item The parent item.
     */
    public function getItem()
    {
        return get_record_by_id('Item', $this->item_id);
    }


    /**
     * Save data from a POST or PUT request.
     *
     * @param array $values The POST/PUT values.
     */
    public function saveForm($values)
    {

        // Cache the original tags string.
        $oldTags = nl_explode($this->tags);

        // Mass-assign the form.
        $this->setArray($values);

        // (1) Pull styles for new tags from the exhibit stylesheet.

        $newTags = nl_explode($this->tags);
        $this->pullStyles(array_diff($newTags, $oldTags));
        $this->save();

        // (2) Update the exhibit stylesheet with the record values.

        $exhibit = $this->getExhibit();
        $exhibit->pullStyles($this);
        $exhibit->save();

        // (3) Propagate the updated styles to sibling records.

        $exhibit->pushStyles();

    }


    /**
     * Before saving, replace the string raw values the coverage fields with
     * the MySQL expression to populate the `GEOMETRY` value. If `coverage` is
     * null, use `POINT(0 0)` as a de facto NULL value (ignored in queries).
     *
     * @return array An array representation of the record.
     */
    public function toArrayForSave()
    {

        $fields = parent::toArrayForSave();

        // Set `is_coverage`.
        $fields['is_coverage'] = $fields['coverage'] ? 1 : 0;

        // Set the `coverage` column.
        $fields['coverage'] = nl_setGeometry($fields['coverage']);

        return $fields;

    }


    /**
     * Update record styles to match exhibit CSS. For example, if `styles` on
     * the parent exhibit is:
     *
     * .tag1 {
     *   fill-color: #111111;
     * }
     * .tag2 {
     *   stroke-color: #222222;
     * }
     *
     * And `array('tag1', 'tag2')` is passed, `fill_color` should be set to
     * '#111111' and `stroke_color` to '#222222'.
     *
     * @param array $tags An array of tags to pull.
     */
    public function pullStyles($tags)
    {

        // If the record is new, pull the `all` tag.
        if (!$this->exists()) {
            $tags = array_merge($tags, array('all'));
        }

        // Parse the stylesheet.
        $css = nl_readCSS($this->getExhibit()->styles);

        // Gather style columns.
        $valid = nl_getStyles();

        // Walk CSS rules.
        foreach ($css as $selector => $rules) {

            // If the tag is being pulled.
            if (in_array($selector, $tags)) {

                // Walk valid CSS rules.
                foreach ($rules as $prop => $val) {

                    // Is the property valid?
                    if (in_array($prop, $valid)) {

                        // Set value if not `auto` or `none`.
                        if ($val != 'auto' && $val != 'none') {
                            $this->$prop = $val;
                        }

                        // If `none`, set NULL.
                        else if ($val == 'none') {
                            $this->$prop = null;
                        }

                    }

                }

            }

        }

    }


    /**
     * If the record has a WMS layer, set a special coverage that ensures that
     * the record will always get matched by the real-time querying system.
     */
    public function compileWms()
    {

        // If a WMS address and layer are defined.
        if ($this->wms_address && $this->wms_layers) {

            // Set the special coverage.
            $this->coverage = 'GEOMETRYCOLLECTION(
                POINT( 9999999  9999999),
                POINT(-9999999  9999999),
                POINT(-9999999 -9999999),
                POINT( 9999999 -9999999)
            )';

            $this->is_coverage = 1;
            $this->is_wms = 1;

        }

    }


    /**
     * Import coverage from Neatline Features, if it's installed, or from the
     * Dublin Core "Coverage" field.
     */
    public function compileCoverage()
    {
        if (!$this->coverage) {
            $wkt = nl_getNeatlineFeatures($this);
            if (is_string($wkt)) $this->coverage = $wkt;
        }
    }


    /**
     * Compile the Omeka item reference, if one exists.
     */
    public function compileItem()
    {

        // Get parent item.
        $item = $this->getItem();
        if (!$item) return;

        // Set title and body.
        $this->title = metadata($item, array('Dublin Core', 'Title'));
        $this->body  = nl_getItemMarkup($this);

    }


    /**
     * Before saving, compile the coverage and item reference.
     */
    public function save()
    {
        $this->compileWms();
        $this->compileCoverage();
        $this->compileItem();
        parent::save();
    }


    /**
     * Alias unmodified save (used for testing).
     */
    public function __save()
    {
        parent::__save();
    }


    /**
     * Associate the model with an ACL resource id.
     *
     * @return string The resource id.
     */
    public function getResourceId()
    {
        return 'Neatline_Records';
    }


}
