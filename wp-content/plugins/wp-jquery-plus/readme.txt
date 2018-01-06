=== WP jQuery Plus ===
Contributors: zslabs
Tags: jquery,google,cdn,cdnjs,jquery migrate
Requires at least: 3.5
Tested up to: 4.2.3
Stable tag: 1.1.2
License: GPLv2
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EEMPDX7SN4RFW

Loads jQuery from a CDN using the exact version as your current WordPress install

== Description ==

Get the speed benefits of loading jQuery (and jQuery Migrate) from a CDN while providing a fallback to the local version in the event the CDN is down. It also uses the same version as WordPress does - automagically, so you're never out of sync.

### Loading jQuery from cdnjs

By default, jQuery is loaded from [Google](https://developers.google.com/speed/libraries/) with jQuery Migrate being loaded from [cdnjs](https://cdnjs.com/). If you'd like to also load jQuery from [cdnjs](https://cdnjs.com/), add the following to `wp-config.php`

`define('WPJP_USE_CDNJS', true);`

== Installation ==

1. Upload the `wp-jquery-plus` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Can you add `feature-x`? =

Sure! I'm always open to knew ideas. Just create a new forum post and I'll take a gander.

== Changelog ==

= 1.1.2 =
* Restore PHP 5.3 compatability because I'm a nice guy ;)

= 1.1.1 =
* Quick-note about PHP 5.4 requirement

= 1.1.0 =
* Tested in 4.2.3
* Loading jQuery Migrate from [cdnjs](https://cdnjs.com/)
* Added option to load jQuery from [cdnjs](https://cdnjs.com/)

= 1.0.1 =
* Tested in 3.8
* Updated description about fallback use-case.

= 1.0 =
* Make sure jQuery is enqueued
* This puppy should be ready to go! Now considered feature-complete.

= 0.5 =
* Added 3.6 check (since the jQuery handle was changed) and updated accordingly to work in both 3.5 and 3.6.

= 0.4.2 =
* Source credits given for local fallback

= 0.4.1 =
* Updated readme requirements

= 0.4 =
* Added local fallback to bundled jQuery in the event CDN is down

= 0.3 =
* Test push using https://github.com/benbalter/Github-to-WordPress-Plugin-Directory-Deployment-Script

= 0.2 =
* Removed CURL function (if Google is down, the entire Internet is down)

= 0.1 =
* Initial release