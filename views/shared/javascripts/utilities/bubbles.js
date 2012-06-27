/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Detail bubbles.
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

(function($, undefined) {

    'use strict';

    $.widget('neatline.bubbles', {

        /*
         * Construct template.
         *
         * @return void.
         */
        _create: function() {

            // Getters.
            this._body = $('body');
            this._window = $(window);

            // Build template.
            this.template = _.template(
                $('#bubble-template').html()
            );

            // Trackers.
            this.bubble = null;
            this.title = null;
            this.body = null;
            this.background = null;
            this.frozen = false;
            this.connector = false;

            // When the cursor leaves the window, hide.
            this._window.bind('mouseleave', _.bind(function() {
                this.hide();
            }, this));

        },

        /*
         * Show bubble.
         *
         * @param {String} title: The title.
         * @param {String} body: The body.
         *
         * @return void.
         */
        show: function(title, body) {

            // If bubble is frozen, break.
            if (this.frozen) return;

            // If both fields are empty, break.
            if (title == '' && body == '') return;

            // Store title and body.
            this.title = title;
            this.body = body;

            // If bubble exists, remove.
            if (!_.isNull(this.bubble)) {
                this.bubble.remove();
            }

            // Render template.
            this.bubble = $(this.template({
                title: title,
                body: body
            }));

            // Get native dimensions.
            this._measureBubble();

            // Get components.
            this.freezeLink = this.bubble.find('a.freeze-bubble');
            this.closeLink = this.bubble.find('a.close-bubble');
            this.moreInfoDiv = this.bubble.find('div.click-for-info');
            this.titleDiv = this.bubble.find('div.title');
            this.bodyDiv = this.bubble.find('div.body');

            // If there is no body, hide click for more info.
            if (this.body === '') {
                this.moreInfoDiv.hide();
            }

            // Inject, get color.
            this.element.append(this.bubble);
            this.background = this.bubble.css('background-color');

            // Listen for mousemove.
            this._window.bind({
                'mousemove.bubbles': _.bind(function(e) {
                    this.position(e);
                }, this)
            });

        },

        /*
         * Hide bubble.
         *
         * @return void.
         */
        hide: function() {

            // If bubble is frozen, break.
            if (this.frozen || _.isNull(this.bubble)) return;

            // Remove bubble.
            this.bubble.remove();
            this.connector.remove();
            this.bubble = null;

            // Strip move listener, trigger out.
            this._window.unbind('mousemove.bubbles');

        },

        /*
         * Freeze bubble.
         *
         * @return void.
         */
        freeze: function() {

            // If no bubble, break.
            if (_.isNull(this.bubble)) return;

            // Set tracker.
            this.frozen = true;

            // Strip mousemove listener.
            this._window.unbind('mousemove.bubbles');
            this.bubble.addClass('frozen');

            // Get native dimensions, position.
            this._measureBubble();
            this.position(this.event);

            // Increase opacity.
            this.bubble.animate({ 'opacity': 0.8 }, 60);
            this.triangle.animate({ 'opacity': 0.8 }, 60);

            // Listen for close.
            this.closeLink.mousedown(_.bind(function() {
                this._trigger('close');
                this.frozen = false;
                this.hide();
            }, this));

        },

        /*
         * Position bubble.
         *
         * @param {Object} event: The mousemove event.
         *
         * @return void.
         */
        position: function(event) {

            // Store last event.
            this.event = event;

            // Get container size.
            var containerWidth = this.element.outerWidth();
            var containerHeight = this.element.outerHeight();

            // Get container offset.
            var offset = this.element.offset();
            var containerX = event.clientX - offset.left;
            var containerY = event.clientY - offset.top +
              this._window.scrollTop();

            // If the cursor leaves the container, hide.
            if (event.clientX < offset.left ||
                event.clientX > offset.left + containerWidth ||
                event.clientY < offset.top ||
                event.clientY > offset.top + containerHeight) {
                  this._trigger('cursorleave');
                  this.hide();
                  return;
            }

            // Build starting bubble offsets.
            var bubbleY = containerY - (this.bubbleHeight/3);
            var bubbleX = containerX + 100;

            // If necessary, switch to left side.
            if (bubbleX + this.bubbleWidth > containerWidth) {
                bubbleX = containerX - this.bubbleWidth - 100;
            }

            // Block top cropping.
            if (bubbleY < 0) {
                bubbleY = 0;
            }

            // Block bottom cropping.
            if (bubbleY + this.bubbleHeight > containerHeight) {
                bubbleY = containerHeight-this.bubbleHeight;
            }

            // Catch full-height.
            if (this.bubbleHeight > containerHeight) {
                bubbleY = 0;
                this.bubbleHeight = containerHeight;
                this.bubble.css('overflow-y', 'scroll');
                this.bubble.outerHeight(this.bubbleHeight);
            }

            // Render position.
            this.bubble.css({
                left: bubbleX,
                top: bubbleY
            });

            // Remove existing connector.
            if (this.connector) this.connector.remove();

            // If the bubble is on the right.
            if (bubbleX > containerX) {

                // Build connector paper.
                var offsetX = event.clientX+20;
                var offsetY = offset.top+bubbleY;
                var width = bubbleX-containerX-20;
                this.connector = Raphael(
                    offsetX, offsetY, width, this.bubbleHeight
                );

                // Get connector coordinates.
                var cursorY = containerY - bubbleY;
                var bubbleY1 = this.bubbleHeight*0.2;
                var bubbleY2 = this.bubbleHeight*0.6;

                // Render coordinates.
                this.triangle = this.connector.path(
                    'M1,'+cursorY+'L99,'+bubbleY1+' 99,'+bubbleY2+'Z'
                );

            }

            // If the bubble is on the left.
            else {

                // Build connector.
                var offsetX = bubbleX+this.bubbleWidth+offset.left;
                var offsetY = offset.top+bubbleY;
                var width = containerX-(bubbleX+this.bubbleWidth)-20;
                this.connector = Raphael(
                    offsetX, offsetY, width, this.bubbleHeight
                );

                // Get connector coordinates.
                var cursorY = containerY - bubbleY;
                var bubbleY1 = this.bubbleHeight*0.2;
                var bubbleY2 = this.bubbleHeight*0.6;

                // Render coordinates.
                this.triangle = this.connector.path(
                    'M0,'+bubbleY1+'L'+width+','+cursorY+' 0,'+bubbleY2+'Z'
                );

            }

            // Set connector styles.
            this.triangle.attr({
                fill: this.background,
                stroke: this.background,
                opacity: 0.7
            });

        },

        /*
         * Compute the native dimensions of the bubble.
         *
         * @return void.
         */
        _measureBubble: function() {

            // Clone and append the bubble.
            var clone = this.bubble.clone().css({
                top: -1000,
                left: -1000
            }).appendTo(this._body);

            // Get dimensions.
            this.bubbleHeight = clone.outerHeight();
            this.bubbleWidth = clone.outerWidth();

            // Remove clone.
            clone.remove();

        }

    });


})(jQuery);
