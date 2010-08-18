Plugin: HB Audio Player
Version: 1.1r94
URL: http://www.lildude.co.uk/projects/hb-audio-player
Plugin Author: Colin Seymour - http://colinseymour.co.uk
Credit goes to: Martin Laine for the original WP Audio Player plugin from which this plugin has been ported.
Licenses:  HB Audio Player (audio-player.plugin.php) : Apache Software License 2.0
           Audio Player (player.swf): GNU Public License v3
           SWFObject (included in audio-player.js): MIT License
		   ColorPicker JQuery Plugin (cpicker/*): MIT & GPL Licenses

HB Audio Player is a highly configurable but simple Flash basedMP3 player for all
your audio needs. You can customise the player's colour scheme to match your blog
theme, have it automatically show track information from the encoded ID3 tags and more.


INSTALLATION
------------

   1. Download either the zip or tar.bz2 to your server
   2. Extract the contents to a temporary location (not strictly necessary, but just being safe)
   3. Move the hb-audio-player directory to /path/to/your/habari/user/plugins/
   4. Refresh your plugins page, activate the plugin and configure it to suit your needs

That's it. You're ready to start using the audio player on your site.


UPGRADE
-------

The upgrade procedure is as per the installation procedure, but please ensure you
de-activate the plugin first.  This will ensure your current settings are merged
with any new options that may be added with later releases.


BASIC USAGE
-----------

The default mechanism for inserting a player in a post is to use the [audio] syntax:

	[audio:http://www.yourdomain.com/path/to/your_mp3_file.mp3]

This will insert a player and load your_mp3_file.mp3 into it.

Multiple file can be specified by separating their paths/names with commas.

You can configure HB Audio Player with a default audio files location so you don't
have to specify the full URL everytime.  You can set this location via the Configuration
panel. Once set, you can use this syntax:

	[audio:your_mp3_file.mp3]

HB Audio Player will automatically look for the file in your default audio files
location. This can be very handy if you decide to move all your audio files to a
different location in the future.


ADVANCED USAGE
--------------

By default, HB Audio Player gets the track information from the ID3 tags of the mp3
file, however under some circumstances it won't be able to, for example if the file
is on another domain.  This is a restriction of the Flash player, but it can be
over-ridden.

You can however pass the artist and title information when inserting the player using
the following syntax:

	[audio:your_mp3_file.mp3|titles=The title|artists=The artist]

For multiple files:

	[audio:mp3_file_1.mp3,mp3_file_2.mp3|titles=Title 1,Title 2|artists=Artist 1,Artist 2]


FAQ
---

* Does Audio Player support file formats other than mp3?

  No. This is a limitation of the Adobe Flash Player

* Can Audio Player read mp3 streams or playlists?

  No. The player is not designed for streaming audio or playlists.



REVISION HISTORY
----------------

1.1r84		- Switched to using the friendlier and more noticeable Session::notice() to notify users their options have been saved.
1.1		- Resolved misbehaviour caused by incorrect assumption about location of theme stylesheet
1.0     - Initial release

That's it folks. If you encounter any problems, please feel free to leave a comment on the post that relates to the release.
