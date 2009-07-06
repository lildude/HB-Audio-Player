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
 * @version 1.0 - the one any ONLY pre r3624 release
 * @author Colin Seymour - http://www.colinseymour.co.uk
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0 (unless otherwise stated)
 * @link http://www.lildude.co.uk/projects/hb-audio-player
 *
 */

class HBAudioPlayer extends Plugin
{
    private $options = array();
    const OPTNAME = 'hbaudioplayer__options';
    private static $defaultColors = array (
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
                                );

    /**
     * Beacon Support for Update checking
     *
     * @access public
     * @return void
     **/
    public function action_update_check()
    {
        Update::add( 'HBAudioPlayer', '4031D1D4-5409-11DE-B1F6-65BE56D89593', '0.1r17' );
    }

    /**
     * The help message - it provides a larger explanation of what this plugin
     * does
     * 
     * @return string
     */
    public function help()
    {
            return _t(' <div style="color:red; font-weight: bold;">THIS IS THE ONE AND ONLY RELEASE OF THIS PLUGIN FOR HABARI 0.6.
                        THIS HAS NOT BEEN TESTED WITH EARLIER RELEASES AND GIVEN THE CHANGES WITH r3624 (SOON TO BE
                        0.7) WILL NOT LIKELY BECOME AN OFFICIAL RELEASE FOR HABARI 0.6 AND EARLIER</div>
                        <br />
                        <p>HB Audio Player is a highly configurable but simple mp3 player
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
                'defaultPath'               => Site::get_url('user').'/files/',
                'customPath'                => '',
                'width'                     => 300,
                'colorScheme'               => self::$defaultColors,
                'enableAnimation'           => TRUE,
                'showRemaining'             => FALSE,
                'disableTrackInformation'   => FALSE,
                'rtlMode'                   => FALSE,
                'feedAlt'                   => 'nothing',
                'feedCustom'                => '[Audio clip: view full post to listen]',
                'initVol'                   => 60,
                'buffer'                    => 5,
                'chkPolicy'                 => FALSE,
                'encode'                    => TRUE,
                'resetColors'               => FALSE
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
                    $ui = new FormUI( strtolower( get_class( $this ) ) );
                    $ui->append( 'wrapper', 'colourselector', 'formcontrol' );

                    // First all the hidden settings
                    foreach( $this->options['colorScheme'] as $opt => $value ) {
                        $ui->colourselector->append( 'hidden', "cs_".$opt, 'null:null' );
                            $optn = "cs_$opt";
                            if ( $optn == 'cs_pagebg' ) continue;
                            $ui->colourselector->$optn->value = '#'.$value;
                            $ui->colourselector->$optn->id = $optn."color";
                    }

                    if ( Plugins::is_loaded( 'Habari Media Silo' ) ) {
                        // Get a list of all the directories available in the loaded Habari Silo
                        $dirs = self::siloDirs();
                        $dirs['custom'] = 'Custom';
                    }
                    
                    $ui->append( 'fieldset', 'genfs', _t( 'General' ) );
                        $ui->genfs->append( 'select', 'defaultPath', 'null:null', _t( 'Default Audio Path' ) );
                            $ui->genfs->defaultPath->template = 'hbap_select';
                            $ui->genfs->defaultPath->id = 'defaultPath';
                            $ui->genfs->defaultPath->pct = 80;
                            $ui->genfs->defaultPath->value = $this->options['defaultPath'];
                            $ui->genfs->defaultPath->helptext = _t( 'This is the default location for your audio files. When you use the [audio] syntax and don\'t provide an absolute URL for the mp3 file (the full URL including "http://") Audio Player will automatically look for the file in this location. You can set this to a folder located inside your blog folder structure or, alternatively, if you wish to store your audio files outside your blog (maybe even on a different server), choose "Custom" from the drop down and enter the absolute URL to that location.' );
                            $ui->genfs->defaultPath->options = $dirs;


                       $ui->genfs->append( 'text', 'customPath', 'null:null', _t( 'Custom Audio Path:' ), 'hbap_text' );
                            $ui->genfs->customPath->value = $this->options['customPath'];
                            $ui->genfs->customPath->pct = 80;
                            $ui->genfs->customPath->id = 'customPath';
                            if ( $this->options['defaultPath'] != 'custom' ) {
                                $ui->genfs->customPath->disabled = TRUE;
                            }
                    
                    $ui->append( 'fieldset', 'appfs', _t( 'Appearance' ) );
                        $ui->appfs->append( 'text', 'width', 'null:null', _t( 'Player Width' ), 'hbap_text' );
                            $ui->appfs->width->value = $this->options['width'];
                            $ui->appfs->width->helptext = _t( 'You can enter a value in pixels (e.g. 200) or as a percentage (e.g. 100%)' );
                        
                        $ui->appfs->append( 'select', 'fieldsel', 'null:null', _t( 'Colour Scheme Selector' ) );
                            $ui->appfs->fieldsel->id = 'fieldsel';
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
                            foreach(self::getThemeColors() as $themeColor) {
                                $themeColorStr .= "<li style='background:#{$themeColor}' title='#{$themeColor}'>#{$themeColor}</li>";
                            }

                            $ui->appfs->fieldsel->helptext = '<input name="colorvalue" type="text" id="colorvalue" size="10" maxlength="7" />
                                                          <span id="colorsample"></span>
                                                          <span id="themecolor-btn">'._t( 'Theme Colours' ). '</span>
                                                          <div id="themecolor">
                                                            <span>'._t( 'Theme Colours' ).'</span>
                                                            <ul>'.$themeColorStr.'</ul></div><input type="button" class="submit" id="doresetcolors" value="'._t( 'Reset Color Scheme' ).'">';
                        $ui->appfs->append( 'hidden', 'resetColors', 'null:null');
                            $ui->appfs->resetColors->value = $this->options['resetColors'];
                            $ui->appfs->resetColors->id = 'resetColors';
                        $ui->appfs->append( 'wrapper', 'colour_selector_demo', 'formcontrol' );
                        // TODO: Get the player to update when saving the options
                            $ui->appfs->colour_selector_demo->append( 'static', 'demo', '
                                <div id="demoplayer">Audio Player</div>
                                <script type="text/javascript">
                                AudioPlayer.setup("'.URL::get_from_filesystem( __FILE__ ).'/lib/player.swf",'.self::php2js($this->getPlayerOptions()).');

                                AudioPlayer.embed("demoplayer", {demomode:"yes"});
                                </script>
                            ');
                        $ui->appfs->append( 'text', 'cs_pagebg', 'null:null', _t( 'Page Background' ), 'hbap_text' );
                            $ui->appfs->cs_pagebg->value = '#'.$this->options['colorScheme']['pagebg'];
                            $ui->appfs->cs_pagebg->id = 'cs_pagebg';
                            if ($this->options['colorScheme']['transparentpagebg']) {
                                $ui->appfs->cs_pagebg->disabled = TRUE;
                            }
                            $ui->appfs->cs_pagebg->helptext =  _t( 'In most cases, simply select "transparent" and it will match the background of your page. In some rare cases, the player will stop working in Firefox if you use the transparent option. If this happens, untick the transparent box and enter the color of your page background in the box below (in the vast majority of cases, it will be white: #FFFFFF).');
                        $ui->appfs->append( 'checkbox', 'cs_transparentpagebg', 'null:null', _t( 'Transparent Page Background' ) );
                            $ui->appfs->cs_transparentpagebg->value = $this->options['colorScheme']['transparentpagebg'];
                            $ui->appfs->cs_transparentpagebg->id = 'cs_transparentpagebg';
                        $ui->appfs->append( 'checkbox', 'enableAnimation', 'null:null', _t( 'Enable Animation' ), 'hbap_checkbox' );
                            $ui->appfs->enableAnimation->value = $this->options['enableAnimation'];
                            $ui->appfs->enableAnimation->helptext = _t('If you don\'t like the open/close animation, you can disable it here.');
                        $ui->appfs->append( 'checkbox', 'showRemaining', 'null:null', _t( 'Show Remaining' ), 'hbap_checkbox' );
                            $ui->appfs->showRemaining->value = $this->options['showRemaining'];
                            $ui->appfs->showRemaining->helptext = _t( 'This will make the time display count down rather than up.' );
                        $ui->appfs->append( 'checkbox', 'disableTrackInformation', 'null:null', _t( 'Disable Track Information' ), 'hbap_checkbox' );
                            $ui->appfs->disableTrackInformation->value = $this->options['disableTrackInformation'];
                            $ui->appfs->disableTrackInformation->helptext = _t( 'Select this if you wish to disable track information display (the player won\'t show titles or artist names even if they are available.)' );
                        $ui->appfs->append( 'checkbox', 'rtlMode', 'null:null', _t( 'Switch to RTL Layout' ), 'hbap_checkbox' );
                            $ui->appfs->rtlMode->value = $this->options['rtlMode'];
                            $ui->appfs->rtlMode->helptext = _t( 'Select this to switch the player layout to RTL mode (right to left) for Arabic and Hebrew language blogs.' );
                    
                    $ui->append( 'fieldset', 'feedfs', _t( 'Feed' ) );
                        $ui->feedfs->append( 'select', 'feedAlt', 'null:null', _t( 'Alternate Content') );
                            $ui->feedfs->feedAlt->id = 'feedAlt';
                            $ui->feedfs->feedAlt->template = 'hbap_select';
                            $ui->feedfs->feedAlt->value = $this->options['feedAlt'];
                            $ui->feedfs->feedAlt->options = array( 'download' => 'Download Link', 'nothing' => 'Nothing', 'custom' => 'Custom' );
                            $ui->feedfs->feedAlt->helptext = _t( 'The following options determine what is included in your feeds. The plugin doesn\'t place a player instance in the feed. Instead, you can choose what the plugin inserts. You have three choices:<br /><br />
                                <strong>Download link</strong>: Choose this if you are OK with subscribers downloading the file.<br />
                                <strong>Nothing</strong>: Choose this if you feel that your feed shouldn\'t contain any reference to the audio file.<br />
                                <strong>Custom</strong>: Choose this to use your own alternative content for all player instances. You can use this option to tell subscribers that they can listen to the audio file if they read the post on your blog.');
                        $ui->feedfs->append( 'text', 'feedCustom', 'null:null', _t( 'Custom alternate content' ), 'hbap_text' );
                            $ui->feedfs->feedCustom->value = $this->options['feedCustom'];
                            $ui->feedfs->feedCustom->pct = 80;
                            $ui->feedfs->feedCustom->id = 'feedCustom';
                            if ( $this->options['feedAlt'] != 'cusom' ) {
                                $ui->feedfs->feedCustom->disabled = TRUE;
                            }

                    $ui->append( 'fieldset', 'advfs', _t( 'Advanced' ) );
                        $ui->advfs->append( 'text', 'initVol', 'null:null', _t( 'Initial Volume' ), 'hbap_text' );
                            $ui->advfs->initVol->value = $this->options['initVol'];
                            $ui->advfs->initVol->helptext = _t( 'This is the volume at which the player defaults to (0 is off, 100 is full volume)' );
                        $ui->advfs->append( 'text', 'buffer', 'null:null', _t( 'Buffer time (in seconds)'), 'hbap_text' );
                            $ui->advfs->buffer->value = $this->options['buffer'];
                            $ui->advfs->buffer->helptext = _t( 'If you think your target audience is likely to have a slow internet connection, you can increase the player\'s buffering time (for standard broadband connections, 5 seconds is enough)' );
                        $ui->advfs->append( 'checkbox', 'chkPolicy', 'null:null', _t( 'Check for policy file' ), 'hbap_checkbox' );
                            $ui->advfs->chkPolicy->value = $this->options['chkPolicy'];
                            $ui->advfs->chkPolicy->helptext = _t( 'Enable this to tell Audio Player to check for a policy file on the server. This allows Flash to read ID3 tags on remote servers. Only enable this if all your mp3 files are located on a server with a policy file.' );
                        $ui->advfs->append( 'checkbox', 'encode', 'null:null', _t( 'Encode MP3 URLs' ), 'hbap_checkbox' );
                            $ui->advfs->encode->value = $this->options['encode'];
                            $ui->advfs->encode->helptext = _t( 'Enable this to encode the URLs to your mp3 files. This is the only protection possible against people downloading the mp3 file to their computers.' );

                    $ui->append( 'submit', 'submit', _t( 'Save Options' ) );
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
        foreach ($ui->controls as $fieldset) {
            if ( is_array( $fieldset->controls ) ) {
                foreach ($fieldset->controls as $option){
                    if ( $option->name == 'save' || $option->name == 'fieldsel' || $option->name == 'colour_selector_demo' ) continue;
                    if ( strstr( $option->name, 'cs_' ) ) {
                        list($a, $name) = explode( "_", $option->name );
                        // Handle booleans
                        if ( $name == 'transparentpagebg' ) {
                            $newOptions['colorScheme'][$name] = (bool) $option->value;
                        } else {
                            $newOptions['colorScheme'][$name] = str_replace( '#', '', $option->value );
                        }
                    } else {
                        if ( $option->name == 'enableAnimation' || $option->name == 'showRemaining' || $option->name == 'disableTrackInformation' || $option->name == 'rtlMode' || $option->name == 'chkPolicy' || $option->name == 'encode' ) {
                            $newOptions[$option->name] = (bool) $option->value;
                        } else {
                            $newOptions[$option->name] = $option->value;
                        }
                    }
                    if ( $option->name == 'resetColors' && $option->value == TRUE ) {
                        $newOptions['colorScheme'] = self::$defaultColors;
                        $newOptions['resetColors'] = FALSE;
                    }

                }
            }
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
            $this->options = Options::get( self::OPTNAME );
            Stack::add( 'admin_stylesheet', array( URL::get_from_filesystem( __FILE__ ) . '/lib/css/admin.css', 'screen'), 'admin-css' );
            Stack::add( 'admin_stylesheet', array( URL::get_from_filesystem( __FILE__ ) . '/lib/js/cpicker/colorpicker.css', 'screen'), 'colorpicker-css' );
            Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/cpicker/colorpicker.min.js', 'jquery.colorpicker', 'jquery' );
            Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player-admin.min.js', 'audioplayer-admin', 'jquery.colorpicker' );
            Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player.js', 'audioplayer', 'jquery' );
            //Stack::add( 'admin_header_javascript', "
            //    AudioPlayer.setup('".URL::get_from_filesystem( __FILE__ )."/lib/player.swf',".self::php2js($this->getPlayerOptions()).");" ,'audioplayer-init', 'audioplayer');
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
            $output = '<style type="text/css">';
            if (!$this->options["colorScheme"]["transparentpagebg"]) {
                $output .= '#colour_selector_demo {background-color: #'.$this->options["colorScheme"]["pagebg"].'; }';
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
        Stack::add( 'template_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player.js', 'audioplayer', 'jquery' );
        Stack::add( 'template_header_javascript', "AudioPlayer.setup('".URL::get_from_filesystem( __FILE__ )."/lib/player.swf',".self::php2js($this->getPlayerOptions()).");" ,'audioplayer-init', 'audioplayer');
    }

    /**
     * Format post content. 
     *
     * We use Format here instead of filter_post_content_out to ensure the code isn't actually replace
     * until the page is displayed.  This prevents errors or the display of rubbish in the event the
     * plugin is deactivated.
     *
     * @access public
     * @return void
     */
    public function action_init()
    {
        Format::apply('processContentOut', 'post_content_out');
        Format::apply('processContentSummary', 'post_content_summary');
        Format::apply('processContentMore', 'post_content_more');
        Format::apply('processContentExcerpt', 'post_content_excerpt');
        Format::apply('processContentAtom', 'post_content_atom');
    }

    /**
     * Replace any instance of the [audio:] tag with the media player.
     *
     * We do this here so we can use class instance variables and private functions.
     *
     * @access public
     * @param string $content
     * @return string
     */
    public function filter_processContent ( $content, $function )
    {
        return preg_replace_callback('#\[audio:(([^]]+))\]#', array($this, 'insertPlayer'.$function), $content);
    }

    /**
     * Insert player into post_content_out.
	 *
	 * @param array $matches from callback function
	 * @return string
     */
    private function insertPlayerOut( $matches )
    {
        $this->options = Options::get( self::OPTNAME );
        static $playerID = 0;
        list( $files, $data ) = $this->getFileData( $matches );

        $playerOptions = array();
        $playerOptions['soundFile'] = ( $this->options['encode'] ) ? self::encodeSource( implode( ",", $files ) ): implode( ",", $files );

        for ($i = 1; $i < count($data); $i++) {
            $pair = explode("=", $data[$i]);
            $playerOptions[trim($pair[0])] = trim($pair[1]);
        }
        // FIXME: The comment here is shown on Safari
        $playerElementID = "audioplayer_$playerID";
        $output = '<p class="audioplayer_container"><span style="display:block;padding:5px;border:1px solid #dddddd;background:#f8f8f8" id="' . $playerElementID . '">' . sprintf(_t( 'Audio clip: Adobe Flash Player (version 9 or above) is required to play this audio clip. Download the latest version <a href="%s" title="Download Adobe Flash Player">here</a>. You also need to have JavaScript enabled in your browser.' ), 'http://get.adobe.com/flashplayer/').'</span>';
        $output .= '<script type="text/javascript">';
        $output .= 'AudioPlayer.embed("' . $playerElementID . '", '.self::php2js($playerOptions).' );';
        $output .= '</script></p>';
        $playerID++;
        
        return $output;
    }

    /**
     * Insert player into post_content_atom.
	 *
	 * @param array $matches from callback function
	 * @return string
     */
    private function insertPlayerAtom( $matches )
    {
        $this->options = Options::get( self::OPTNAME );
        list( $files, $data ) = $this->getFileData( $matches );

        switch ( $this->options['feedAlt'] ) {
            case "nothing":
                $output = '';
                break;
            case "download":
                $output = '';
                for ($i = 0; $i < count($files); $i++) {
                    $fileparts = explode("/", $files[$i]);
                    $fileName = $fileparts[count($fileparts)-1];
                    $output .= '<a href="' . $files[$i] . '">' . _t('Download audio file') . ' (' . $fileName . ')</a><br />';
                }
                break;
            case "custom":
                $output = $this->options['feedCustom'];
                break;
        }
        return $output;
    }

    /**
     * Insert player into post_content_excerpt.
	 *
	 * @param array $matches from callback function
	 * @return string
     * @todo Add post_content_excerpt config and output functionality
     */
    private function insertPlayerExcerpt( $matches )
    {
        $this->options = Options::get( self::OPTNAME );
        list( $files, $data ) = $this->getFileData( $matches );
        return NULL;
    }

    /**
     * Insert player into post_content_more.
	 *
	 * @param array $matches from callback function
	 * @return string
     * @todo Add post_content_more config and output functionality
     */
    private function insertPlayerMore( $matches )
    {
        $this->options = Options::get( self::OPTNAME );
        list( $files, $data ) = $this->getFileData( $matches );
        return NULL;
    }

    /**
     * Insert player into post_content_summary.
	 *
	 * @param array $matches from callback function
	 * @return string
     * @todo Add post_content_summary config and output functionality
     */
    private function insertPlayerSummary( $matches )
    {
        $this->options = Options::get( self::OPTNAME );
        list( $files, $data ) = $this->getFileData( $matches );
        return NULL;
    }

	/**
	 * Extracts filenames, titles and artists from matched data.
	 * 
	 * @param array $matches
	 * @return array
	 */
    private function getFileData($matches)
    {
        $data = preg_split("/[\|]/", $matches[1]);
        $files = array();

        // Create an array of files to load in player
        foreach ( explode( ",", trim($data[0]) ) as $afile ) {
            $afile = trim($afile);
            // Get absolute URLs for relative ones
            if (!self::isAbsoluteURL($afile)) {
                $afile = $this->options['defaultPath'] . $afile;
            }
            array_push( $files, $afile );
        }
        return array($files, $data);
    }


    /******************** Helper Functions ************************************/

    /**
     * Parses theme stylesheet and pulls out the colours used
     *
	 * @access private
     * @return array of colors from current theme
     */
    private static function getThemeColors()
    {
            $themeCssFile = Themes::get_active()->theme_dir.'style.css';
            $theme_css = implode('', file( $themeCssFile ) );
            preg_match_all('/:[^:,;\{\}].*?#([abcdef1234567890]{3,6})/i', strtoupper($theme_css), $matches);
            return array_unique($matches[1]);
    }

    /**
     * Formats a php associative array into a javascript object
	 *
     * @param $object Object containing the options to format
	 * @return string
     */
    private static function php2js($object)
    {
            $js_options = '{';
            $separator = "";
            $real_separator = ",";
            foreach( $object as $key => $value ) {
                // Format booleans
                if ( is_bool( $value ) ) $value = $value ? 'yes' : 'no';
                $js_options .= $separator . $key . ':"' . rawurlencode( $value ) .'"';
                $separator = $real_separator;
            }
            $js_options .= "}";

            return $js_options;
    }

	/**
	 * Get Player Options from the settings and create an array.
	 *
	 * This is used when creating the setup code for the players.
	 *
	 * @access private
	 * @return array
	 */
    private function getPlayerOptions()
    {
        $playerOptions = array();
        $playerOptions['width'] = $this->options['width'];
        $playerOptions['animation'] = $this->options['enableAnimation'];
        $playerOptions['encode'] = $this->options['encode'];
        $playerOptions['initialvolume'] = $this->options['initVol'];
        $playerOptions['remaining'] = $this->options['showRemaining'];
        $playerOptions['noinfo'] = $this->options['disableTrackInformation'];
        $playerOptions['buffer'] = $this->options['buffer'];
        $playerOptions['checkpolicy'] = $this->options['chkPolicy'];
        $playerOptions['rtl'] = $this->options['rtlMode'];

        return array_merge($playerOptions, $this->options["colorScheme"]);
    }

    /**
     * Returns an array of URLs correlating to each directory found in the
     * default Habari Silo path in the form:
     *
     * URL => "Habari Silo:/path/to/dir/"
     *
     * This is for use in a <select> form element.
     *
	 * @access private
     * @return array
     */
    private static function siloDirs()
    {
        // Get a list of all the directories available in the loaded Habari Silo
        $user_path = HABARI_PATH . '/' . Site::get_path('user') . '/files/'; // Default Habari silo path
        $user_url = Site::get_url('user').'/files/';                         // Default Habari Silo URL
        $dirs = array($user_url => 'Habari Silo:/');
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($user_path), RecursiveIteratorIterator::SELF_FIRST);
        foreach( $objects as $name => $object ){
            if ( $object->isDir() ) {
                if ( $object->getFilename() != '.deriv' ) {                 // Exclude the .deriv dirs created by Habari Silo plugin
                    $newname = str_replace($user_path, '', $name).'/';
                    $newurl = str_replace($user_path, $user_url, $name).'/';
                    $dirs[$newurl] = 'Habari Silo:/'.$newname;
                }
            }
        }
        return $dirs;
    }

    /**
	 * Determine if the path provided is a full URL
	 *
     * @param $path Object
	 * @return true if $path is absolute
     */
    private static function isAbsoluteURL($path)
    {
        if (strpos($path, "http://") === 0) {
            return true;
        }
        if (strpos($path, "https://") === 0) {
            return true;
        }
        if (strpos($path, "ftp://") === 0) {
            return true;
        }
        return false;
    }

    /**
     * Encodes the given string
	 *
     * @param string $string String the string to encode
	 * @return string encoded string
     */
    private static function encodeSource($string)
    {
        $source = utf8_decode($string);
        $ntexto = "";
        $codekey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
        for ($i = 0; $i < strlen($string); $i++) {
            $ntexto .= substr("0000".base_convert(ord($string{$i}), 10, 2), -8);
        }
        $ntexto .= substr("00000", 0, 6-strlen($ntexto)%6);
        $string = "";
        for ($i = 0; $i < strlen($ntexto)-1; $i = $i + 6) {
            $string .= $codekey{intval(substr($ntexto, $i, 6), 2)};
        }
        return $string;
    }
}

/**
 * Formatting class.
 *
 * This class actually calls a filter function in the main plugin code.
 */
class HBAudioPlayerFormat extends Format {
    public function processContentOut( $content )
    {
        return Plugins::filter( 'processContent', $content, 'Out' );
    }
    
    public function processContentAtom( $content )
    {
        return Plugins::filter( 'processContent', $content, 'Atom' );
    }

    public function processContentExcerpt( $content )
    {
        return Plugins::filter( 'processContent', $content, 'Excerpt' );
    }

    public function processContentMore( $content )
    {
        return Plugins::filter( 'processContent', $content, 'More' );
    }

    public function processContentSummary( $content )
    {
        return Plugins::filter( 'processContent', $content, 'Summary' );
    }
}
?>
