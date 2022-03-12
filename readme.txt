=== Stop Confusion ===
Contributors: idandev
Tags: security, theme-check
Tested up to: 5.9
Stable tag: 0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to check your themes' presence in WordPress remote repository from the admin panel, and block unwanted theme updates to prevent security breach.

== Description ==

This plugin allows you to check your themes' presence in WordPress remote repository.

Its aim is to give a simple solution to prevent severe security issues based on an attacker faking a theme update for your theme and taking over your WordPress installation and server.

Indeed, (WordPress 5.8 security fix)[https://make.wordpress.org/core/2021/06/29/introducing-update-uri-plugin-header-in-wordpress-5-8/] gave us a new Plugin header to help plugin developers prevent that kind of issue. However, the same solution cannot be applied to theme development at the time of developing this plugin, as there is no "Update URI" header for themes.

"Stop Confusion" checks your theme's presence in WordPress SVN.

If your theme is not available at the time of the scan, the plugin prevents you from updating the theme from the WordPress admin panel.