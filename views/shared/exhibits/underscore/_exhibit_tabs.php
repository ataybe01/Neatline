<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=76; */

/**
 * Exhibit tabs.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<script id="exhibit-tabs-template" type="text/templates">

  <p class="lead"><?php echo $exhibit->title; ?></p>

  <ul class="nav nav-pills">
    <li class="tab records"><a href="#records">Records</a></li>
    <li class="tab styles"><a href="#styles">Styles</a></li>
  </ul>

</script>