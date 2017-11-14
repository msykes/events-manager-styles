=== Events Manager Example Add-on - Styles ===
Contributors: netweblogic, msykes
Donate link: http://wp-events-plugin.com
Tags: events, events-manager
Requires at least: 3.3
Tested up to: 4.9
Stable tag: 1.1
License: GPLv2

Example add-on for Events Manager demonstrating the various integration points using the actions and filters.

== Description ==

This is an example plugin, for demonstration purposes and a boilerplate for adding new functionality to Events Manager.

See [the full walkthrough tutorial](http://wp-events-plugin.com/tutorials/creating-a-events-manager-add-on-a-complete-walkthrough/) for more details about each function in this plugin. 
 
== Installation ==

This plugin requires Events Manager to be installed in order to use this plugin, and is installed like any standard Wordpress plugin. [See the Codex for installation instructions](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

For updates, see the github repository - 

== Changelog ==
= 1.0.2 (dev) =
* fixed validation issues on first submission of a translation due to recent WPML changes
* added fix for translation editor validation issues (kudos David)
* removed unnecessary taxonomy filters thanks to recent fixes in EM and how data is written to $wp_query globals
* fixed calendar day display issues in recent WPML versions
* fixed category page display issues (fixed in Events Manager 5.8)
* fixed PHP warning on trash pages when viewing all lanaguages
* fixed duplicating events via WPML not copying location information first time around
* special thanks David Garcia Watkins and the rest of the WPML dev team for their assistance with many of these bugs!