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
     * @todo Remove this if running on Habari r3624 or later
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
                        <br />
                        <strong>Basic Usage:</strong><br />
                        <p>The default mechanism for inserting a player in a post is to use the [audio] syntax:<br />
                        
                        <code>[audio:http://www.yourdomain.com/path/to/your_mp3_file.mp3]</code></p>

                        <p>This will insert a player and load your_mp3_file.mp3 into it.</p>

                        <p>Multiple file can be specified by separating their paths/names with commas.</p>

                        <p>You can configure HB Audio Player with a default audio files
                        location so you don’t have to specify the full URL everytime.
                        You can set this location via the Settings panel. Once set, you
                        can use this syntax:<br />

                        <code>[audio:your_mp3_file.mp3]</code></p>

                        <p>Audio Player will automatically look for the file in your default
                        audio files location. This can be very handy if you decide to move
                        all your audio files to a different location in the future.</p>
                        <br />
                        <strong>Advanced Usage</strong><br />

                        <p>By default, the player gets the track information from the ID3 tags
                        of the mp3 file, however under some circumstances it won\'t be able to,
                        for example if the file is on another domain.  This is a
                        <a href="http://www.adobe.com/devnet/flashplayer/articles/cross_domain_policy.html">restriction</a>
                        of the Flash player, but it can be over-ridden.</p>

                        <p>You can however pass the artice and title information when inserting the
                        player using the following syntax:<br />

                        <code>[audio:your_mp3_file.mp3|titles=The title|artists=The artist]</code></p>

                        <p>For multiple files:<br />

                        <code>[audio:mp3_file_1.mp3,mp3_file_2.mp3|titles=Title 1,Title 2|artists=Artist 1,Artist 2]</code></p>
                        <br />
                        <p><a href="'.URL::get( 'admin', array( 'page' => 'plugins', 'configure' => $this->plugin_id(), 'configaction' => 'Configure' ) ) . '#plugin_options">Configure</a> HB Audio Player now.</p>
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
                'default_path'  => '',
                'width'         => 200,
                'colourScheme'  => array (
                                    'bg'                => 'E5E5E5',
                                    'text'              => '333333',
                                    'leftbg'            => 'CCCCCC',
                                    'lefticon'          => '333333',
                                    'volslider'         => '666666',
                                    'voltrack'          => 'FFFFFF',
                                    'rightbg'           => 'B4B4B4',
                                    'rightbghover'      => '999999',
                                    'righticon'         => '333333',
                                    'righticonhover'    => 'FFFFFF',
                                    'track'             => 'FFFFFF',
                                    'loader'            => '009900',
                                    'border'            => 'CCCCCC',
                                    'tracker'           => 'DDDDDD',
                                    'skip'              => '666666',
                                    'pagebg'            => 'FFFFFF',
                                    'transparentpagebg' => TRUE
                                ),
                'disableAnimation' => FALSE,
                'showRemaining' => FALSE,
                'disableTrackInformation' => FALSE,
                'rtlMode' => FALSE,

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
                    // First all the hidden settings
                    foreach( $this->options['colourScheme'] as $opt => $value ) {
                        $ui->append( 'hidden', "cs_".$opt, 'null:null' );
                            $optn = "cs_$opt";
                            $ui->$optn->value = $value;
                    }
                    // TODO: Find a way to easily list silo locations
                    $ui->append( 'fieldset', 'genfs', _t( 'General' ) );
                        $ui->genfs->append( 'text', 'default_path', 'null:null', _t( 'Default Audio Path:' ), 'hbap_text' );
                            $ui->genfs->default_path->value = $this->options['default_path'];

                    $ui->append( 'fieldset', 'appfs', _t( 'Appearance' ) );
                        $ui->appfs->append( 'text', 'width', 'null:null', _t( 'Player Width' ), 'hbap_text' );
                            $ui->appfs->width->value = $this->options['width'];
                            $ui->appfs->width->helptext = _t( 'You can enter a value in pixels (e.g. 200) or as a percentage (e.g. 100%)' );
                        
                        $ui->appfs->append( 'select', 'fieldsel', 'null:null', _t( 'Colour Scheme Selector' ) );
                            $ui->appfs->fieldsel->template = 'hbap_select';
                            $ui->appfs->fieldsel->options = array (
                                                    'bg'                => _t( 'Background' ),
                                                    'leftbg'            => _t( 'Left Background' ),
                                                    'lefticon'          => _t( 'Left Icon' ),
                                                    'volslider'         => _t( 'Volume Control Slider' ),
                                                    'voltrack'          => _t( 'Volume Control Track' ),
                                                    'rightbg'           => _t( 'Right Background' ),
                                                    'rightbghover'      => _t( 'Right Background (hover)' ),
                                                    'righticon'         => _t( 'Right Icon' ),
                                                    'righticonhover'    => _t( 'Right Icon (hover)' ),
                                                    'text'              => _t( 'Text' ),
                                                    'track'             => _t( 'Progress Bar Track' ),
                                                    'tracker'           => _t( 'Progress Bar' ),
                                                    'loader'            => _t( 'Loading Bar' ),
                                                    'border'            => _t( 'Progress Bar Border' ),
                                                    'skip'              => _t( 'Next/Previous Buttons' )
                                                    );
                            $themeColorStr = '';
                            foreach($this->getThemeColors() as $themeColor) {
                                $themeColorStr .= "<li style='background:#{$themeColor}' title='#{$themeColor}'>#{$themeColor}</li>";
                            }

                            $ui->appfs->fieldsel->helptext = '<input name="colorvalue" type="text" id="colorvalue" size="10" maxlength="7" />
                                                          <span id="colorsample"></span>
                                                          <span id="picker-btn">'. _t( 'Pick' ).'</span>
                                                          <div id="themecolor"><span>'._t( 'Theme Colours' ). '</span>
                                                            <ul>'.$themeColorStr.'</ul></div>';
                        $ui->appfs->append( 'wrapper', 'colour_selector_demo', 'formcontrol' );
                            $ui->appfs->colour_selector_demo->append( 'static', 'demo', '<div id="demoplayer">Audio Player</div>
                                                                                        <script type="text/javascript">
                                                                                        /* AudioPlayer.embed("ap_demoplayer", {demomode:"yes"}); */
                                                                                        </script>');
                        $ui->appfs->append( 'text', 'cs_pagebg', 'null:null', _t( 'Page Background' ), 'hbap_text' );
                            $ui->appfs->cs_pagebg->value = $this->options['colourScheme']['pagebg'];
                            $ui->appfs->cs_pagebg->helptext =  _t( 'In most cases, simply select "transparent" and it will match the background of your page. In some rare cases, the player will stop working in Firefox if you use the transparent option. If this happens, untick the transparent box and enter the color of your page background in the box below (in the vast majority of cases, it will be white: #FFFFFF).');
                        // TODO: Need to put this inline with the pagebg somehow.
                        $ui->appfs->append( 'checkbox', 'cs_transparentpagebg', 'null:null', _t( 'Transparent Page Background' ) );
                            $ui->appfs->cs_transparentpagebg->value = $this->options['colourScheme']['transparentpagebg'];
                        // TODO: Add "Reset colours button
                        $ui->appfs->append( 'checkbox', 'disableAnimation', 'null:null', _t( 'Disable Animation' ), 'hbap_checkbox' );
                            $ui->appfs->disableAnimation->value = $this->options['disableAnimation'];
                            $ui->appfs->disableAnimation->helptext = _t('If you don\'t like the open/close animation, you can disable it here.');
                        $ui->appfs->append( 'checkbox', 'showRemaining', 'null:null', _t( 'Show Remaining' ), 'hbap_checkbox' );
                            $ui->appfs->showRemaining->value = $this->options['showRemaining'];
                            $ui->appfs->showRemaining->helptext = _t( 'This will make the time display count down rather than up.' );
                        $ui->appfs->append( 'checkbox', 'disableTrackInformation', 'null:null', _t( 'Disable Track Information' ), 'hbap_checkbox' );
                            $ui->appfs->disableTrackInformation->value = $this->options['disableTrackInformation'];
                            $ui->appfs->disableTrackInformation->helptext = _t( 'Select this if you wish to disable track information display (the player won\'t show titles or artist names even if they are available.)' );
                        $ui->appfs->append( 'checkbox', 'rtlMode', 'null:null', _t( 'Switch to RTL Layout' ), 'hbap_checkbox' );
                            $ui->appfs->rtlMode->value = $this->options['rtlMode'];
                            $ui->appfs->rtlMode->helptext = _t( 'Select this to switch the player layout to RTL mode (right to left) for Arabic and Hebrew language blogs.' );

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
             //Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player.js', 'audioplayer' );
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
            $output .= '#colour_selector_demo { margin: 25px 0 0 16%;}';
            if (!$this->options["colourScheme"]["transparentpagebg"]) {
                $output .= '#colour_selector_demo {background-color: #'.$this->options["colourScheme"]["pagebg"].'; }';
            }
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
    }

    /**
     * Parses theme stylesheet and pulls out the colours used
     *
     * @return array of colors from current theme
     */
    
    function getThemeColors() {
            $themeCssFile = Themes::get_active()->theme_dir.'style.css';
            $theme_css = implode('', file( $themeCssFile ) );
            preg_match_all('/:[^:,;\{\}].*?#([abcdef1234567890]{3,6})/i', $theme_css, $matches);
            return array_unique($matches[1]);
    }
}
?>