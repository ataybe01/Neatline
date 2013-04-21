<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=76; */

/**
 * Top-level Neatline partial.
 *
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<div id="neatline">
  <div id="neatline-map" class="neatline-block"></div>
</div>

<!-- Globals constants. -->
<script type="text/javascript">
  Neatline.global = <?php echo Zend_Json::encode(
    nl_getGlobals(nl_exhibit())
  ); ?>
</script>

<!-- Underscore templates. -->
<?php echo $this->partial('exhibits/underscore/bubble.php'); ?>

<!-- Plugin templates. -->
<?php fire_plugin_hook('neatline_public_templates', array(
  'exhibit' => nl_exhibit()
)); ?>
