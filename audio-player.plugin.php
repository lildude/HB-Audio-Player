<?php
/**
 *
 * Copyright 2009 Colin Seymour - http://www.lildude.co.uk/projects/slimbox2
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * A Habari implementation of the WPAudioPlayer [http://wpaudioplayer.com/] plugin
 * by Martin Laine.
 *
 * @package HBAudioPlayer
 * @version 0.1
 * @author Colin Seymour - http://www.colinseymour.co.uk
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0 (unless otherwise stated)
 * @link http://www.lildude.co.uk/projects/hb-audio-player
 *
 */

class HBAudioPlayer extends Plugin
{

    private $options = array();
    const OPTNAME = 'hbaudioplayer__options';

    /**
     * Plugin information
     *
     * @access public
     * @return void
     */
    public function info()
    {
        return array (
            'name' => 'HB AudioPlayer',
            'url' => 'http://www.lildude.co.uk/projects/hb-audio-player',
            'author' => 'Colin Seymour',
            'authorurl' => 'http://www.colinseymour.co.uk/',
            'version' => '0.1',
            'description' => 'HB Audio Player is a highly configurable but simple mp3 player for all your audio needs.',
            'license' => 'Apache License 2.0',
            'guid' => '4031D1D4-5409-11DE-B1F6-65BE56D89593',
            'copyright' => date( '%Y' )
        );
    }

    /**
     * Beacon Support for Update checking
     *
     * @access public
     * @return void
     **/
    public function action_update_check()
    {
        Update::add( 'HBAudioPlayer', $this->info->guid, $this->info->version );
    }

    /**
     * The help message - it provides a larger explanation of what this plugin
     * does
     * 
     * @return string
     */
    public function help()
    {
            return _t(' <p>HB Audio Player is a highly configurable but simple mp3 player
                        for all your audio needs. You can customise the player\'s
                        colour scheme to match your blog theme, have it automatically
                        show track information from the encoded ID3 tags and more.</p>
                        
                        <strong>Basic Usage:</strong><br />

                        <p>The default mechanism for inserting a player in a post is to use the [audio] syntax:</p>

                        <code>[audio:http://www.yourdomain.com/path/to/your_mp3_file.mp3]</code>

                        <p>This will insert a player and load your_mp3_file.mp3 into it.</p>

                        <p>Multiple file can be specified by separating their paths/names with commas.</p>

                        <p>You can configure HB Audio Player with a default audio files
                        location so you don’t have to specify the full URL everytime.
                        You can set this location via the Settings panel. Once set, you
                        can use this syntax:</p>

                        <code>[audio:your_mp3_file.mp3]</code>

                        <p>Audio Player will automatically look for the file in your default
                        audio files location. This can be very handy if you decide to move
                        all your audio files to a different location in the future.</p>

                        <strong>Advanced Usage</strong><br />

                        <p>By default, the player gets the track information from the ID3 tags
                        of the mp3 file, however under some circumstances it won\'t be able to,
                        for example if the file is on another domain.  This is a
                        <a href="http://www.adobe.com/devnet/flashplayer/articles/cross_domain_policy.html">restriction</a>
                        of the Flash player, but it can be over-ridden.</p>

                        <p>You can however pass the artice and title information when inserting the
                        player using the following syntax:</p>

                        <code>[audio:your_mp3_file.mp3|titles=The title|artists=The artist]</code>

                        <p>For multiple files:</p>

                        <code>[audio:your_mp3_file_1.mp3,your_mp3_file_2.mp3|titles=The title 1,The title 2|artists=The artist 1,The artist 2]</code>
                       
                        <p>Configuring it now.</p>
                       ');
    }
    /**
     * Plugin activation
     *
     * @access public
     * @param string $file
     * @return void
     */
    public function action_plugin_activation( $file )
    {
        if( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
            $defOptions = array(

                        );

            $this->options = Options::get( self::OPTNAME );

            if ( empty( $this->options ) ) {
                Options::set( self::OPTNAME, $defOptions );
            }
            else {
                Session::notice( _t( 'Using previous HB Audio Player options' ) );
            }
        }
    }

    /**
     * Plugin De-activation
     *
     * @access public
     * @param string $file
     * @return void
     */
    public function action_plugin_deactivation( $file )
    {
        if ( realpath( $file ) == __FILE__ ) {
           Options::delete(self::OPTNAME);
        }
    }

    /**
     * Add the Configure option for the plugin
     *
     * @access public
     * @param array $actions
     * @param string $plugin_id
     * @return array
     */
    public function filter_plugin_config( $actions, $plugin_id )
    {
        if ( $plugin_id == $this->plugin_id() ) {
            $actions[]= _t( 'Configure' );
        }
        return $actions;
    }


    /**
     * Plugin UI
     *
     * @access public
     * @param string $plugin_id
     * @param string $action
     * @return void
     */
    public function action_plugin_ui( $plugin_id, $action )
    {
        $this->add_template( 'hbap_checkbox', dirname( $this->get_file() ) . '/lib/formcontrols/optionscontrol_checkbox.php' );
        $this->add_template( 'hbap_text', dirname( $this->get_file() ) . '/lib/formcontrols/optionscontrol_text.php' );
        $this->add_template( 'hbap_select', dirname( $this->get_file() ) . '/lib/formcontrols/optionscontrol_select.php' );
        $this->add_template( 'hbap_radio', dirname( $this->get_file() ) . '/lib/formcontrols/optionscontrol_radio.php' );


        if ( $plugin_id == $this->plugin_id() ) {
            switch ( $action ) {
                case _t( 'Configure' ):
                    $this->options = Options::get( self::OPTNAME );
                    $durations = array( 1 => 'Disabled', 100 => 100, 200 => 200, 300 => 300, 400 => 400, 500 => 500, 600 => 600, 700 => 700, 800 => 800, 900 => 900, 1000 => 1000 );

                    $ui = new FormUI( strtolower( get_class( $this ) ) );
                    $ui->append( 'checkbox', 'autoload', 'null:null', _t( 'Autoload?' ), 'slim_checkbox' );
                        $ui->autoload->value = $this->options['autoload'];
                        $ui->autoload->helptext = _t( 'Automatically activate Slimbox on all links pointing to ".jpg" or ".png" or ".gif". All image links contained in the same block or paragraph (having the same parent element) will automatically be grouped together in a gallery. If this isn\'t activated you will need to manually add \'rel="lightbox"\' for individual images or \'rel="lightbox-imagesetname"\' for groups on all links you wish to use Slimbox.' );
                    $ui->append( 'checkbox', 'picasa', 'null:null', _t( 'Enable Picasaweb Integration?' ), 'slim_checkbox' );
                        $ui->picasa->value = $this->options['picasa'];
                        $ui->picasa->helptext = _t( 'Automatically add the Slimbox effect to Picasaweb links when provided an appropriate thumbnail (this is separate from the autoload script which only functions on image links).' );
                    $ui->append( 'checkbox', 'flickr', 'null:null', _t( 'Enable Flickr Integration?' ), 'slim_checkbox' );
                        $ui->flickr->value = $this->options['flickr'];
                        $ui->flickr->helptext = _t( 'Automatically add the Slimbox effect to Flickr links when provided an appropriate thumbnail (this is separate from the autoload script which only functions on image links).' );
                    $ui->append( 'checkbox', 'smugmug', 'null:null', _t( 'Enable SmugMug Integration?' ), 'slim_checkbox' );
                        $ui->smugmug->value = $this->options['smugmug'];
                        $ui->smugmug->helptext = _t( 'Automatically add the Slimbox effect to SmugMug links when provided an appropriate thumbnail (this is separate from the autoload script which only functions on image links).' );
                    $ui->append( 'checkbox', 'loop', 'null:null', _t( 'Loop?' ), 'slim_checkbox' );
                        $ui->loop->value = $this->options['loop'];
                        $ui->loop->helptext = _t( 'Loop between the first and last images of a Slimbox gallery when there is more than one image to display.' );
                    $ui->append( 'select', 'overlay_opacity', 'null:null', _t( 'Overlay Opacity' ) );
                        $ui->overlay_opacity->value = $this->options['overlay_opacity'];
                        $ui->overlay_opacity->helptext = _t( 'Adjust the opacity of the background overlay. 1 is completely opaque, 0 is completely transparent.' );
                        $ui->overlay_opacity->options = array( '0' => 0, '0.1' => 0.1, '0.2' => 0.2, '0.3' => 0.3, '0.4' => 0.4, '0.5' => 0.5, '0.6' => 0.6, '0.7' => 0.7, '0.8' => 0.8, '0.9' => 0.9, '1' => 1);
                        $ui->overlay_opacity->template = 'slim_select';
                    $ui->append( 'text', 'overlay_color', 'null:null', _t( 'Overlay Color' ), 'slim_text' );
                        $ui->overlay_color->value = $this->options['overlay_color'];
                        $ui->overlay_color->helptext = '<div id="picker"></div>'. _t( 'Set the color of the overlay by selecting your hue from the circle and color gradient from the square. Alternatively you may manually enter a valid HTML color code. The color of the entry field will change to reflect your selected color. Default is #000000.' );
                   
                    $ui->append( 'select', 'overlay_fade_duration', 'null:null', _t( 'Overlay Fade Duration' ) );
                        $ui->overlay_fade_duration->value = $this->options['overlay_fade_duration'];
                        $ui->overlay_fade_duration->helptext = _t( 'Adjust the duration of the overlay fade-in and fade-out animations, in milliseconds.' );
                        $ui->overlay_fade_duration->options = $durations;
                        $ui->overlay_fade_duration->template = 'slim_select';
                    $ui->append( 'select', 'resize_duration', 'null:null', _t( 'Resize Duration' ) );
                        $ui->resize_duration->value = $this->options['resize_duration'];
                        $ui->resize_duration->helptext = _t( 'Ajust the duration of the resize animation for width and height, in milliseconds. ' );
                        $ui->resize_duration->options = $durations;
                        $ui->resize_duration->template = 'slim_select';
                    $ui->append( 'select', 'resize_easing', 'null:null', _t( 'Resize Easing' ) );
                        $ui->resize_easing->value = $this->options['resize_easing'];
                        $ui->resize_easing->helptext = _t( 'Select the name of the easing effect that you want to use for the resize animation (jQuery Easing Plugin required and included). Many easings require a longer execution time to look good, so you should adjust the resizeDuration option above as well.' );
                        $ui->resize_easing->options = array('swing' => 'swing', 'easeInQuad' => 'easeInQuad', 'easeOutQuad' => 'easeOutQuad', 'easeInOutQuad' => 'easeInOutQuad', 'easeInCubic' => 'easeInCubic', 'easeOutCubic' => 'easeOutCubic', 'easeInOutCubic' => 'easeInOutCubic', 'easeInQuart' => 'easeInQuart', 'easeOutQuart' => 'easeOutQuart', 'easeInOutQuart' => 'easeInOutQuart', 'easeInQuint' => 'easeInQuint', 'easeOutQuint' => 'easeOutQuint', 'easeInOutQuint' => 'easeInOutQuint', 'easeInSine' => 'easeInSine', 'easeOutSine' => 'easeOutSine', 'easeInOutSine' => 'easeInOutSine', 'easeInExpo' => 'easeInExpo', 'easeOutExpo' => 'easeOutExpo', 'easeInOutExpo' => 'easeInOutExpo', 'easeInCirc' => 'easeInCirc', 'easeOutCirc' => 'easeOutCirc', 'easeInOutCirc' => 'easeInOutCirc', 'easeInElastic' => 'easeInElastic', 'easeOutElastic' => 'easeOutElastic', 'easeInOutElastic' => 'easeInOutElastic', 'easeInBack' => 'easeInBack', 'easeOutBack' => 'easeOutBack', 'easeInOutBack' => 'easeInOutBack', 'easeInBounce' => 'easeInBounce', 'easeOutBounce' => 'easeOutBounce', 'easeInOutBounce' => 'easeInOutBounce' );
                        $ui->resize_easing->template = 'slim_select';
                    $ui->append( 'text', 'initial_width', 'null:null', _t( 'Initial Width' ), 'slim_text' );
                        $ui->initial_width->value = $this->options['initial_width'];
                        $ui->initial_width->helptext = _t( 'Set the initial width of the box, in pixels. ' );
                    $ui->append( 'text', 'initial_height', 'null:null', _t( 'Initial Height' ), 'slim_text' );
                        $ui->initial_height->value = $this->options['initial_height'];
                        $ui->initial_height->helptext = _t( 'Set the initial height of the box, in pixels. ' );
                    $ui->append( 'select', 'image_fade_duration', 'null:null', _t( 'Image Fade Duration' ) );
                        $ui->image_fade_duration->value = $this->options['image_fade_duration'];
                        $ui->image_fade_duration->helptext = _t( 'Set the duration of the image fade-in animation, in milliseconds. Disabling this effect will make the image appear instantly.' );
                        $ui->image_fade_duration->options = $durations;
                        $ui->image_fade_duration->template = 'slim_select';
                    $ui->append( 'select', 'caption_animation_duration', 'null:null', _t( 'Caption Animation Duration' ) );
                        $ui->caption_animation_duration->value = $this->options['caption_animation_duration'];
                        $ui->caption_animation_duration->helptext = _t( 'Set the duration of the caption animation, in milliseconds. Disabling this effect will make the caption appear instantly.' );
                        $ui->caption_animation_duration->options = $durations;
                        $ui->caption_animation_duration->template = 'slim_select';
                    $ui->append( 'text', 'counter_text', 'null:null', _t( 'Counter Text' ), 'slim_text' );
                        $ui->counter_text->value = $this->options['counter_text'];
                        $ui->counter_text->helptext = _t( 'Customize, translate or disable the counter text which appears in the captions when multiple images are shown. Inside the text, {x} will be replaced by the current image index, and {y} will be replaced by the total number of images. Set it to false (boolean value, without quotes) or "" to disable the counter display. Default is "Image {x} of {y}".' );
                    // TODO: Need to workout how I can get this to span 3 rows of options.
                    $helptxt = 'These options allow the user to specify an array of key codes representing the keys to press to close or navigate to the next or previous images.<br />

Just select the corresponding text box and press the keys you would like to use. Alternately check the box below to manually enter or clear key codes.<br />
<br />
Default close values are [27, 88, 67] which means Esc (27), "x" (88) and "c" (67).<br />
Default previous values are [37, 80] which means Left arrow (37) and "p" (80).<br />
Default next values are [39, 78] which means Right arrow (39) and "n" (78).<br />
<br />
<strong>Enable Manual Key Code Entry?</strong><input id="slimbox_manual_key" type="checkbox" value="manual_key"/>
<input id="slimbox_key_defined" type="hidden" value="That key has already been defined."/>';

                    $ui->append( 'text', 'close_keys', 'null:null', _t( 'Close Keys' ), 'slim_text' );
                        $ui->close_keys->value = $this->options['close_keys'];
                        $ui->close_keys->class = 'formcontrol keys';
                        $ui->close_keys->helptext = _t( $helptxt );
                    $ui->append( 'text', 'previous_keys', 'null:null', _t( 'Previous Keys' ), 'slim_text' );
                        $ui->previous_keys->value = $this->options['previous_keys'];
                        $ui->previous_keys->class = 'formcontrol keys';
                        $ui->previous_keys->helptext = '';
                    $ui->append( 'text', 'next_keys', 'null:null', _t( 'Next Keys' ), 'slim_text' );
                        $ui->next_keys->value = $this->options['next_keys'];
                        $ui->next_keys->class = 'formcontrol keys';
                        $ui->next_keys->helptext = '';
                    // TODO: Add this functionality if I find a need for it.
                    /*$ui->append( 'checkbox', 'maintenance', 'null:null', _t( 'Maintenance mode?' ), 'slim_checkbox' );
                        $ui->maintenance->value = $this->options['maintenance'];
                        $ui->maintenance->helptext = _t( 'Enable a maintenance mode for testing purposes. When enabled slimbox will be disabled until you enable it by appending ?slimbox=on to a url. It will then remain on until you disable it by appending ?slimbox=off to a url, you clear your cookies, or in certain cases you clear your browser cache. This setting only impacts things at a vistor level, not a site wide level.' );
                     */
                    $ui->append( 'submit', 'save', _t( 'Save Options' ) );
                    $ui->on_success ( array( $this, 'storeOpts' ) );
                    $ui->set_option( 'success_message', _t( 'Options successfully saved.' ) );
                    $ui->out();
                break;
            }
        }
    }

    /**
     * Serialize and Store the Options in a single DB entry in the options table
     *
     * @access public
     * @static
     * @param object $ui
     * @return void
     */

     public static function storeOpts ( $ui )
     {
        $newOptions = array();
        foreach( $ui->controls as $option ) {
            if ( $option->name == 'save' ) continue;
            $newOptions[$option->name] = $option->value;
        }
        Options::set( self::OPTNAME, $newOptions );
     }

    /**
     * Add custom CSS information to "Configure" page
     *
     * This needs to be defined at the top for some reason.
     *
     * @access public
     * @param object $theme
     * @return void
     */
    public function action_admin_header( $theme )
    {
        if ( Controller::get_var( 'configure' ) == $this->plugin_id ) {
             Stack::add( 'admin_stylesheet', array( URL::get_from_filesystem( __FILE__ ) . '/lib/js/farbtastic/farbtastic.css', 'screen'), 'farbtastic-css' );
        }
    }

    /**
     * Add custom Javascript and CSS information to "Configure" page
     *
     * @access public
     * @param object $theme
     * @return void
     */
    public function action_admin_footer( $theme )
    {
        if ( Controller::get_var( 'configure' ) == $this->plugin_id ) {
            Stack::add( 'admin_footer_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/keypress.js', 'jquery.keypress', 'jquery' );
            Stack::add( 'admin_footer_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/farbtastic/farbtastic.js', 'jquery.farbtastic', 'jquery' );
            Stack::add( 'admin_footer_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/farbtastic/load_farbtastic.js', 'jquery.load.farbtastic', 'jquery.farbtastic' );

            $output = '<style type="text/css">';
            $output .= 'form#'.strtolower( get_class( $this ) ).' .formcontrol { clear: both; }';
            $output .= 'form#'.strtolower( get_class( $this ) ).' span.pct15 select { width:105%; }';
            $output .= 'form#'.strtolower( get_class( $this ) ).' span.pct15 { text-align:right; }';
            $output .= 'form#'.strtolower( get_class( $this ) ).' span.pct5 input { margin-left:25px; }';
            $output .= 'form#'.strtolower( get_class( $this ) ).' span.helptext { margin-left:25px; }';    // Need this for FF3 on Solaris.
            $output .= 'form#'.strtolower( get_class( $this ) ).' p.error { float:left; color:#A00; }';
            $output .= '.farbtastic { margin-left: -200px; margin-top: 25px; float: left; }';
            $output .= '</style>';
            echo $output;
        }
    }

    /**
     * Add the necessary Javascript and CSS to each page.
     * 
     * @param object $theme 
     */
    public function theme_header( $theme )
    {
        $this->options = Options::get( self::OPTNAME );
        Stack::add( 'template_stylesheet', array( URL::get_from_filesystem( __FILE__ ) . '/lib/css/slimbox2.css', 'screen' ), 'slimbox2' );
        Stack::add( 'template_header_javascript', Site::get_url( 'scripts' ) . '/jquery.js', 'jquery' );
        Stack::add( 'template_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/slimbox2.js', 'jquery.slimbox2', 'jquery' );
        Stack::add( 'template_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/jquery.easing.1.3.packed.js', 'jquery.easing', 'jquery' );

        if ( $this->options['autoload'] ) {
            $autoload = "
                $('a[href]').filter(function() {
                    return /\.(jpg|png|gif)$/i.test(this.href);
                }).slimbox(options,
                    function(el) {  /* LinkMapper */
                        cap = (el.title || el.alt || el.firstChild.title || el.firstChild.title);
                        return [el.href, cap.replace(/\\n/, '<br />')];     /* Replaced \n with a break when showing slimbox */
                    },
                    function(el) {
                        return (this == el) || (this.parentNode && (this.parentNode == el.parentNode));
                    });
            ";
        } else {
            $autoload = "
                $(\"a[rel^='lightbox']\").slimbox(options, 
                    function(el) {
                        cap = (el.title || el.alt || el.firstChild.title || el.firstChild.title);
                        return [el.href, cap.replace(/\\n/, '<br />')];     /* Replaced \n with a break when showing slimbox */
                    },
                    function(el) {
                        return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                    });
            ";
        }

        if ( $this->options['picasa'] ) {
            $autoload .= "
                $(\"a[href^='http://picasaweb.google.'] > img:first-child[src]\").parent().slimbox(options, function(el) {
                    return [el.firstChild.src.replace(/\/s\d+(?:\-c)?\/([^\/]+)$/, '/s640/$2'),
                            (el.title || el.alt || el.firstChild.title || el.firstChild.alt) + '<br /><a href=\'' + el.href + '\'>Picasa Web Albums page<\/a>'];
                });
            ";
        }

        // Shows medium image by default.
        if ( $this->options['flickr'] ) {
            $autoload .= "
                /* $(\"a[href^='http://www.flickr.com/photos/'] > img:first-child[src]\").parent().slimbox(options, function(el) { */
                $(\"a:regex(href, http://(\w+\.)?flickr.com/photos/) > img:first-child[src]\").parent().slimbox(options, function(el) {
                    return [el.firstChild.src.replace(/_[mts]\.(\w+)$/, '.$1'),
                        (el.title || el.alt || el.firstChild.title || el.firstChild.alt) + '<br /><a href=\'' + el.href + '\'>Flickr page<\/a>'];
                });
            ";
        }
        // Shows large image by default.
        if ( $this->options['smugmug'] ) {
            $autoload .= "
                $(\"a:regex(href, http://(.*).smugmug.com/gallery/) > img:first-child[src]\").parent().slimbox(options, function(el) {
                        return [el.firstChild.src.replace(/-[LMTS]\.(\w+)$/, '-L.$1'),
                                (el.title || el.alt || el.firstChild.title || el.firstChild.alt) + '<br /><a href=\'' + el.href + '\'>SmugMug page<\/a>'];
                });
            ";
        }

        Stack::add( 'template_header_javascript', "
/* CNS: Extending jQuery selectors for use with the regex needed by the Flickr
 * and SmugMug features.
 *
 * Src: http://james.padolsey.com/javascript/regex-selector-for-jquery/
 */
jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

jQuery(function($) {
    var options = {
        loop: ".(($this->options['loop']) ? 1 : 0).",
        overlayOpacity: ".$this->options['overlay_opacity'].",
        overlayFadeDuration: ".$this->options['overlay_fade_duration'].",
        resizeDuration: ".$this->options['resize_duration'].",
        resizeEasing: \"".$this->options['resize_easing']."\",
        initialWidth: ".$this->options['initial_width'].",
        initialHeight: ".$this->options['initial_height'].",
        imageFadeDuration: ".$this->options['image_fade_duration'].",
        captionAnimationDuration: ".$this->options['caption_animation_duration'].",
        counterText: \"".$this->options['counter_text']."\",
        closeKeys: [".$this->options['close_keys']."],
        previousKeys: [".$this->options['previous_keys']."],
        nextKeys: [".$this->options['next_keys']."]
    }
    $('#lbOverlay').css('background-color','".$this->options['overlay_color']."');
/* TODO: Alternate images */
    $('#lbPrevLink').hover(
            function () {
                    $(this).css('background-image','url(\"".URL::get_from_filesystem( __FILE__ ) . '/lib/images/photonav/prev.png'."\")');
                    $(this).css('background-position', '0 0');
            },
            function () {
                    $(this).css('background-image','');
            }
    );
    $('#lbNextLink').hover(
            function () {
                    $(this).css('background-image','url(\"".URL::get_from_filesystem( __FILE__ ) . '/lib/images/photonav/next.png'."\")');
                    $(this).css('background-position', '100% 0');
            },
            function () {
                    $(this).css('background-image','');
            }
    );

    /* $('#lbCloseLink').css('background-image', 'url(\"".URL::get_from_filesystem( __FILE__ ) . '/lib/images/photonav/close.png'."\")'); */

    ".$autoload."
});",
    'slimbox2.init', 'jquery.slimbox2');
    }
}
?>