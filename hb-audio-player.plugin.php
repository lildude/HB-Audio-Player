<?php
/**
 *
 * Copyright 2009 Colin Seymour - http://www.lildude.co.uk/projects/hb-audio-player
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
 * A Habari implementation of the WP Audio Player [http://wpaudioplayer.com/] plugin
 * by Martin Laine.
 *
 * @package HBAudioPlayer
 * @version 1.1r98
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
                'defaultPath'               => Site::get_url( 'user' ).'/files/',
                'customPath'                => '',
                'width'                     => 300,
                'colorScheme'               => self::$defaultColors,
                'enableAnimation'           => TRUE,
                'showRemaining'             => FALSE,
                'disableTrackInformation'   => FALSE,
                'rtlMode'                   => FALSE,
                'feedAlt'                   => 'nothing',
                'feedCustom'                => _t( '[Audio clip: view full post to listen]', 'audio-player' ),
                'initVol'                   => 60,
                'buffer'                    => 5,
                'chkPolicy'                 => FALSE,
                'encode'                    => TRUE,
                'resetColors'               => FALSE
            );

            $options = Options::get( self::OPTNAME );

            if ( empty( $options ) ) {
                Options::set( self::OPTNAME, $defOptions );
            }
            else {
                Session::notice( _t( 'Using previous HB Audio Player options', 'audio-player' ) );
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
		$actions['configure']= _t( 'Configure', 'audio-player' );
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
    public function action_plugin_ui_configure()
    {
        $this->add_template( 'hbap_checkbox', dirname( $this->get_file() ) . '/lib/formcontrols/hbap_checkbox.php' );
        $this->add_template( 'hbap_text', dirname( $this->get_file() ) . '/lib/formcontrols/hbap_text.php' );
        $this->add_template( 'hbap_select', dirname( $this->get_file() ) . '/lib/formcontrols/hbap_select.php' );

		$options = Options::get( self::OPTNAME );
		$ui = new FormUI( strtolower( __CLASS__ ) );
		$ui->append( 'wrapper', 'colourselector', 'formcontrol' );

		// First all the hidden settings
		foreach( $options['colorScheme'] as $opt => $value ) {
			$ui->colourselector->append( 'hidden', "cs_".$opt, 'null:null' );
				$optn = "cs_$opt";
				if ( $optn == 'cs_pagebg' ) continue;
				$ui->colourselector->$optn->value = '#'.$value;
				$ui->colourselector->$optn->id = $optn."color";
		}

		if ( Plugins::is_loaded( 'Habari Media Silo' ) ) {
			// Get a list of all the directories available in the loaded Habari Silo
			$dirs = self::siloDirs();
			$dirs['custom'] = _t( 'Custom', 'audio-player' );
		}

		$ui->append( 'fieldset', 'genfs', _t( 'General', 'audio-player' ) );
			$ui->genfs->append( 'select', 'defaultPath', 'null:null', _t( 'Default Audio Path', 'audio-player' ) );
				$ui->genfs->defaultPath->template = 'hbap_select';
				$ui->genfs->defaultPath->id = 'defaultPath';
				$ui->genfs->defaultPath->pct = 80;
				$ui->genfs->defaultPath->value = $options['defaultPath'];
				$ui->genfs->defaultPath->helptext = _t( 'This is the default location for your audio files. When you use the [audio] syntax and don\'t provide an absolute URL for the mp3 file (the full URL including "http://") Audio Player will automatically look for the file in this location. You can set this to a folder located inside your blog folder structure or, alternatively, if you wish to store your audio files outside your blog (maybe even on a different server), choose "Custom" from the drop down and enter the absolute URL to that location.', 'audio-player' );
				$ui->genfs->defaultPath->options = $dirs;


		   $ui->genfs->append( 'text', 'customPath', 'null:null', _t( 'Custom Audio Path:', 'audio-player' ), 'hbap_text' );
				$ui->genfs->customPath->value = $options['customPath'];
				$ui->genfs->customPath->pct = 80;
				$ui->genfs->customPath->id = 'customPath';
				if ( $options['defaultPath'] != 'custom' ) {
					$ui->genfs->customPath->disabled = TRUE;
				}

		$ui->append( 'fieldset', 'appfs', _t( 'Appearance', 'audio-player' ) );
			$ui->appfs->append( 'text', 'width', 'null:null', _t( 'Player Width', 'audio-player' ), 'hbap_text' );
				$ui->appfs->width->value = $options['width'];
				$ui->appfs->width->helptext = _t( 'You can enter a value in pixels (e.g. 200) or as a percentage (e.g. 100%)', 'audio-player' );

			$ui->appfs->append( 'select', 'fieldsel', 'null:null', _t( 'Colour Scheme Selector', 'audio-player' ) );
				$ui->appfs->fieldsel->id = 'fieldsel';
				$ui->appfs->fieldsel->template = 'hbap_select';
				$ui->appfs->fieldsel->options = array (
										'bg'                => _t( 'Background', 'audio-player' ),
										'leftbg'            => _t( 'Left Background', 'audio-player' ),
										'lefticon'          => _t( 'Left Icon', 'audio-player' ),
										'volslider'         => _t( 'Volume Control Slider', 'audio-player' ),
										'voltrack'          => _t( 'Volume Control Track', 'audio-player' ),
										'rightbg'           => _t( 'Right Background', 'audio-player' ),
										'rightbghover'      => _t( 'Right Background (hover)', 'audio-player' ),
										'righticon'         => _t( 'Right Icon', 'audio-player' ),
										'righticonhover'    => _t( 'Right Icon (hover)', 'audio-player' ),
										'text'              => _t( 'Text', 'audio-player' ),
										'track'             => _t( 'Progress Bar Track', 'audio-player' ),
										'tracker'           => _t( 'Progress Bar', 'audio-player' ),
										'loader'            => _t( 'Loading Bar', 'audio-player' ),
										'border'            => _t( 'Progress Bar Border', 'audio-player' ),
										'skip'              => _t( 'Next/Previous Buttons', 'audio-player' )
										);

				$ui->appfs->fieldsel->helptext = '<input name="colorvalue" type="text" id="colorvalue" size="10" maxlength="7" />
												  <span id="colorsample"></span>';
				// IFF we've managed to find the theme/style.css file and parse it, we'll show the "Theme Colours" selection tool
				$themeColors = self::getThemeColors();
				if ( is_array( $themeColors ) && !empty( $themeColors ) ) {
					$themeColorStr = '';
					foreach(self::getThemeColors() as $themeColor) {
						$themeColorStr .= "<li style='background:#{$themeColor}' title='#{$themeColor}'>#{$themeColor}</li>";
					}
					$ui->appfs->fieldsel->helptext .= '<span id="themecolor-btn">'._t( 'Theme Colours', 'audio-player' ). '</span>
														<div id="themecolor">
														<span>'._t( 'Theme Colours', 'audio-player' ).'</span>
														<ul>'.$themeColorStr.'</ul></div>';
				}
				$ui->appfs->fieldsel->helptext .= '<input type="button" class="submit" id="doresetcolors" value="'._t( 'Reset Color Scheme', 'audio-player' ).'">';
			$ui->appfs->append( 'hidden', 'resetColors', 'null:null');
				$ui->appfs->resetColors->id = 'resetColors';
			$ui->appfs->append( 'wrapper', 'colour_selector_demo', 'formcontrol' );
				$ui->appfs->colour_selector_demo->append( 'static', 'demo', '
					<div id="demoplayer">Audio Player</div>
					<script type="text/javascript">
					AudioPlayer.embed("demoplayer", {demomode:"yes"});
					</script>
				');
			$ui->appfs->append( 'text', 'cs_pagebg', 'null:null', _t( 'Page Background', 'audio-player' ), 'hbap_text' );
				$ui->appfs->cs_pagebg->value = '#'.$options['colorScheme']['pagebg'];
				$ui->appfs->cs_pagebg->id = 'cs_pagebg';
				if ($options['colorScheme']['transparentpagebg']) {
					$ui->appfs->cs_pagebg->disabled = TRUE;
				}
				$ui->appfs->cs_pagebg->helptext =  _t( 'In most cases, simply select "transparent" and it will match the background of your page. In some rare cases, the player will stop working in Firefox if you use the transparent option. If this happens, untick the transparent box and enter the color of your page background in the box below (in the vast majority of cases, it will be white: #FFFFFF).', 'audio-player' );
			$ui->appfs->append( 'checkbox', 'cs_transparentpagebg', 'null:null', _t( 'Transparent Page Background', 'audio-player' ) );
				$ui->appfs->cs_transparentpagebg->value = $options['colorScheme']['transparentpagebg'];
			$ui->appfs->append( 'checkbox', 'enableAnimation', 'null:null', _t( 'Enable Animation', 'audio-player' ), 'hbap_checkbox' );
				$ui->appfs->enableAnimation->value = $options['enableAnimation'];
				$ui->appfs->enableAnimation->helptext = _t('If you don\'t like the open/close animation, you can disable it here.', 'audio-player' );
			$ui->appfs->append( 'checkbox', 'showRemaining', 'null:null', _t( 'Show Remaining', 'audio-player' ), 'hbap_checkbox' );
				$ui->appfs->showRemaining->value = $options['showRemaining'];
				$ui->appfs->showRemaining->helptext = _t( 'This will make the time display count down rather than up.', 'audio-player' );
			$ui->appfs->append( 'checkbox', 'disableTrackInformation', 'null:null', _t( 'Disable Track Information', 'audio-player' ), 'hbap_checkbox' );
				$ui->appfs->disableTrackInformation->value = $options['disableTrackInformation'];
				$ui->appfs->disableTrackInformation->helptext = _t( 'Select this if you wish to disable track information display (the player won\'t show titles or artist names even if they are available.)', 'audio-player' );
			$ui->appfs->append( 'checkbox', 'rtlMode', 'null:null', _t( 'Switch to RTL Layout', 'audio-player' ), 'hbap_checkbox' );
				$ui->appfs->rtlMode->value = $options['rtlMode'];
				$ui->appfs->rtlMode->helptext = _t( 'Select this to switch the player layout to RTL mode (right to left) for Arabic and Hebrew language blogs.', 'audio-player' );

		$ui->append( 'fieldset', 'feedfs', _t( 'Feed', 'audio-player' ) );
			$ui->feedfs->append( 'select', 'feedAlt', 'null:null', _t( 'Alternate Content', 'audio-player' ) );
				$ui->feedfs->feedAlt->id = 'feedAlt';
				$ui->feedfs->feedAlt->template = 'hbap_select';
				$ui->feedfs->feedAlt->value = $options['feedAlt'];
				$ui->feedfs->feedAlt->options = array( 'download' => _t( 'Download Link', 'audio-player' ), 'nothing' => _t( 'Nothing', 'audio-player' ), 'custom' => _t( 'Custom', 'audio-player' ) );
				$ui->feedfs->feedAlt->helptext = _t( 'The following options determine what is included in your feeds. The plugin doesn\'t place a player instance in the feed. Instead, you can choose what the plugin inserts. You have three choices:<br /><br />
					<strong>Download link</strong>: Choose this if you are OK with subscribers downloading the file.<br />
					<strong>Nothing</strong>: Choose this if you feel that your feed shouldn\'t contain any reference to the audio file.<br />
					<strong>Custom</strong>: Choose this to use your own alternative content for all player instances. You can use this option to tell subscribers that they can listen to the audio file if they read the post on your blog.', 'audio-player' );
			$ui->feedfs->append( 'text', 'feedCustom', 'null:null', _t( 'Custom alternate content', 'audio-player' ), 'hbap_text' );
				$ui->feedfs->feedCustom->value = $options['feedCustom'];
				$ui->feedfs->feedCustom->pct = 80;
				$ui->feedfs->feedCustom->id = 'feedCustom';
				if ( $options['feedAlt'] != 'custom' ) {
					$ui->feedfs->feedCustom->disabled = TRUE;
				}

		$ui->append( 'fieldset', 'advfs', _t( 'Advanced', 'audio-player' ) );
			$ui->advfs->append( 'text', 'initVol', 'null:null', _t( 'Initial Volume', 'audio-player' ), 'hbap_text' );
				$ui->advfs->initVol->value = $options['initVol'];
				$ui->advfs->initVol->helptext = _t( 'This is the volume at which the player defaults to (0 is off, 100 is full volume)', 'audio-player' );
			$ui->advfs->append( 'text', 'buffer', 'null:null', _t( 'Buffer time (in seconds)', 'audio-player' ), 'hbap_text' );
				$ui->advfs->buffer->value = $options['buffer'];
				$ui->advfs->buffer->helptext = _t( 'If you think your target audience is likely to have a slow internet connection, you can increase the player\'s buffering time (for standard broadband connections, 5 seconds is enough)', 'audio-player' );
			$ui->advfs->append( 'checkbox', 'chkPolicy', 'null:null', _t( 'Check for policy file', 'audio-player' ), 'hbap_checkbox' );
				$ui->advfs->chkPolicy->value = $options['chkPolicy'];
				$ui->advfs->chkPolicy->helptext = _t( 'Enable this to tell Audio Player to check for a policy file on the server. This allows Flash to read ID3 tags on remote servers. Only enable this if all your mp3 files are located on a server with a policy file.', 'audio-player' );
			$ui->advfs->append( 'checkbox', 'encode', 'null:null', _t( 'Encode MP3 URLs', 'audio-player' ), 'hbap_checkbox' );
				$ui->advfs->encode->value = $options['encode'];
				$ui->advfs->encode->helptext = _t( 'Enable this to encode the URLs to your mp3 files. This is the only protection possible against people downloading the mp3 file to their computers.', 'audio-player' );

		$ui->append( 'submit', 'save', _t( 'Save', 'audio-player' ) );
		$ui->on_success ( array( $this, 'storeOpts' ) );
		//$ui->set_option( 'success_message', _t( 'Options successfully saved.' ) );
		$form_output = $ui->get();
		echo '<script type="text/javascript">AudioPlayer.setup("'.URL::get_from_filesystem( __FILE__ ).'/lib/player.swf",'.self::php2js(self::getPlayerOptions()).');</script>';
		echo $form_output;
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
        foreach ( $ui->controls as $fieldset ) {
            if ( is_array( $fieldset->controls )  ) {
                foreach ( $fieldset->controls as $option){
                    if ( $option->name == 'save' || $option->name == 'fieldsel' || $option->name == 'colour_selector_demo' ) continue;
                    if ( strstr( $option->name, 'cs_' ) ) {
                        list( $a, $name ) = explode( "_", $option->name );
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
		Session::notice( _t( 'Options saved.', 'audio-player' ) );
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
            $options = Options::get( self::OPTNAME );
            Stack::add( 'admin_stylesheet', array( URL::get_from_filesystem( __FILE__ ) . '/lib/css/admin.css', 'screen' ), 'admin-css' );
            Stack::add( 'admin_stylesheet', array( URL::get_from_filesystem( __FILE__ ) . '/lib/js/cpicker/colorpicker.css', 'screen' ), 'colorpicker-css' );
            Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/cpicker/colorpicker.min.js', 'jquery.colorpicker', 'jquery' );
            Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player-admin.min.js', 'audioplayer-admin', 'jquery.colorpicker' );
            Stack::add( 'admin_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player.js', 'audioplayer', 'jquery' );
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
			$options = Options::get( self::OPTNAME );
            $output = '<style type="text/css">';
            if (!$options["colorScheme"]["transparentpagebg"]) {
                $output .= '#colour_selector_demo {background-color: #'.$options["colorScheme"]["pagebg"].'; }';
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
        //Stack::add( 'template_header_javascript', URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player.js', 'audioplayer', 'jquery' );
        //Stack::add( 'template_header_javascript', "AudioPlayer.setup('".URL::get_from_filesystem( __FILE__ )."/lib/player.swf',".self::php2js(self::getPlayerOptions()).");" ,'audioplayer-init', 'audioplayer');
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
		$this->load_text_domain( 'audio-player' );
        Format::apply( 'processContentOut', 'post_content_out' );
        Format::apply( 'processContentSummary', 'post_content_summary' );
        Format::apply( 'processContentMore', 'post_content_more' );
        Format::apply( 'processContentExcerpt', 'post_content_excerpt' );
        Format::apply( 'processContentAtom', 'post_content_atom' );
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
        return preg_replace_callback( '#\[audio:(([^]]+))\]#', array( 'self', 'insertPlayer'.$function ), $content );
    }

    /**
     * Insert player into post_content_out.
	 *
	 * @param array $matches from callback function
	 * @return string
     */
    private static function insertPlayerOut( $matches )
    {
        $options = Options::get( self::OPTNAME );
        static $playerID = 0;
        list( $files, $data ) = self::getfileData( $matches );

        $playerOptions = array();
        $playerOptions['soundFile'] = ( $options['encode'] ) ? self::encodeSource( implode( ",", $files ) ): implode( ",", $files );

        for ( $i = 1; $i < count( $data ); $i++) {
            $pair = explode( "=", $data[$i] );
            $playerOptions[trim( $pair[0] )] = trim( $pair[1] );
        }

        $playerElementID = "audioplayer_$playerID";
		$output = '';
		// Load the Javascript only on the pages/posts it's actually needed - some will argue about the problems with adding JS to the body of a doc like this, but we're using Flash already, so we can't be too pedantic :-)
		if ( $playerID == 0 ) {
			$output .= '<script type="text/javascript" src="' . URL::get_from_filesystem( __FILE__ ) . '/lib/js/audio-player.js"></script>';
			$output .= '<script type="text/javascript">AudioPlayer.setup("'.URL::get_from_filesystem( __FILE__ ).'/lib/player.swf",'.self::php2js(self::getPlayerOptions()).');</script>';
		}
        $output .= '<p class="audioplayer_container"><span style="display:block;padding:5px;border:1px solid #dddddd;background:#f8f8f8" id="' . $playerElementID . '">' . _t( 'Audio clip: Adobe Flash Player (version 9 or above) is required to play this audio clip. Download the latest version' ) . '<a href="http://get.adobe.com/flashplayer/" title="' . _t( 'Download Adobe Flash Player') .'"> ' . _t( 'here' ) . '</a>.' . _t( ' You also need to have JavaScript enabled in your browser.' ).'</span>';
        $output .= '<script type="text/javascript">';
        $output .= 'AudioPlayer.embed("' . $playerElementID . '", '.self::php2js( $playerOptions ).' );';
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
    private static function insertPlayerAtom( $matches )
    {
        $options = Options::get( self::OPTNAME );
        list( $files, $data ) = self::getfileData( $matches );

        switch ( $options['feedAlt'] ) {
            case "nothing":
                $output = '';
                break;
            case "download":
                $output = '';
                for ( $i = 0; $i < count( $files ); $i++ ) {
                    $fileparts = explode( "/", $files[$i] );
                    $fileName = $fileparts[count( $fileparts )-1];
                    $output .= '<a href="' . $files[$i] . '">' . _t('Download audio file') . ' (' . $fileName . ')</a><br />';
                }
                break;
            case "custom":
                $output = $options['feedCustom'];
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
    private static function insertPlayerExcerpt( $matches )
    {
        $options = Options::get( self::OPTNAME );
        list( $files, $data ) = self::getfileData( $matches );
        return NULL;
    }

    /**
     * Insert player into post_content_more.
	 *
	 * @param array $matches from callback function
	 * @return string
     * @todo Add post_content_more config and output functionality
     */
    private static function insertPlayerMore( $matches )
    {
        $options = Options::get( self::OPTNAME );
        list( $files, $data ) = self::getfileData( $matches );
        return NULL;
    }

    /**
     * Insert player into post_content_summary.
	 *
	 * @param array $matches from callback function
	 * @return string
     * @todo Add post_content_summary config and output functionality
     */
    private static function insertPlayerSummary( $matches )
    {
        $options = Options::get( self::OPTNAME );
        list( $files, $data ) = self::getfileData( $matches );
        return NULL;
    }

	/**
	 * Extracts filenames, titles and artists from matched data.
	 * 
	 * @param array $matches
	 * @return array
	 */
    private static function getFileData( $matches )
    {
		$options = Options::get( self::OPTNAME );
        $data = preg_split( "/[\|]/", $matches[1] );
        $files = array();

        // Create an array of files to load in player
        foreach ( explode( ",", trim( $data[0] ) ) as $afile ) {
            $afile = trim($afile);
            // Get absolute URLs for relative ones
            if (!self::isAbsoluteURL($afile)) {
                $afile = $options['defaultPath'] . $afile;
            }
            array_push( $files, $afile );
        }
        return array( $files, $data );
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
		$themeFile = Themes::get_active()->theme_dir . 'style.css';
		if ( is_file( $themeFile ) ) {
            $theme_css = implode('', file( $themeFile ) );
            preg_match_all( '/:[^:,;\{\}].*?#([abcdef1234567890]{3,6})/i', strtoupper( $theme_css ), $matches );
            return array_unique($matches[1]);
		} else {
			return FALSE;
		}
    }

    /**
     * Formats a php associative array into a javascript object
	 *
     * @param $object Object containing the options to format
	 * @return string
     */
    private static function php2js( $object )
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
    private static function getPlayerOptions()
    {
		$options = Options::get( self::OPTNAME );
        $playerOptions = array();
        $playerOptions['width'] = $options['width'];
        $playerOptions['animation'] = $options['enableAnimation'];
        $playerOptions['encode'] = $options['encode'];
        $playerOptions['initialvolume'] = $options['initVol'];
        $playerOptions['remaining'] = $options['showRemaining'];
        $playerOptions['noinfo'] = $options['disableTrackInformation'];
        $playerOptions['buffer'] = $options['buffer'];
        $playerOptions['checkpolicy'] = $options['chkPolicy'];
        $playerOptions['rtl'] = $options['rtlMode'];

        return array_merge( $playerOptions, $options["colorScheme"] );
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
        $user_path = HABARI_PATH . '/' . Site::get_path( 'user' ) . '/files/'; // Default Habari silo path
        $user_url = Site::get_url('user').'/files/';                         // Default Habari Silo URL
        $dirs = array( $user_url => 'Habari Silo:/ ');
        $objects = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $user_path ), RecursiveIteratorIterator::SELF_FIRST );
        foreach( $objects as $name => $object ){
            if ( $object->isDir() ) {
                if ( $object->getFilename() != '.deriv' ) {                 // Exclude the .deriv dirs created by Habari Silo plugin
                    $newname = str_replace( $user_path, '', $name ).'/';
                    $newurl = str_replace( $user_path, $user_url, $name ).'/';
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
    private static function isAbsoluteURL( $path )
    {
        if ( strpos( $path, "http://" ) === 0 ) {
            return true;
        }
        if ( strpos( $path, "https://" ) === 0 ) {
            return true;
        }
        if ( strpos( $path, "ftp://" ) === 0 ) {
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
    private static function encodeSource( $string )
    {
        $source = utf8_decode( $string );
        $ntexto = "";
        $codekey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
        for ($i = 0; $i < strlen($string); $i++) {
            $ntexto .= substr( "0000".base_convert( ord( $string{$i} ), 10, 2 ), -8 );
        }
        $ntexto .= substr( "00000", 0, 6-strlen( $ntexto )%6 );
        $string = "";
        for ( $i = 0; $i < strlen( $ntexto )-1; $i = $i + 6 ) {
            $string .= $codekey{intval( substr( $ntexto, $i, 6 ), 2 )};
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
