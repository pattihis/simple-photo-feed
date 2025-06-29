=== Simple Photo Feed ===
Contributors: pattihis
Donate link: https://profiles.wordpress.org/pattihis/
Tags: photo gallery, instagram, feed, social, embed
Requires at least: 5.3.0
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Photo Feed provides an easy way to connect to your Instagram account and display your photos in your WordPress site.

== Description ==

**Simple Photo Feed** is a free WordPress plugin that lets you embed your Instagram photos as a gallery in your website.

### Features

* Easy embed feature to display Instagram posts from your account.
* Super simple to set up - No coding or editing files!
* Completely responsive and mobile ready – layout looks great on any screen size and in any container width
* Customizable – Customize the number of photos, number of columns, image size and captions display!
* Use the built-in shortcode options to completely customize your Instagram feed
* Built in optional lightbox to view larger images and scroll through gallery without leaving the current site

### Benefits

* Increase Social Engagement – Increase engagement between you and your Instagram followers. Increase your number of followers by displaying your Instagram content directly on your site.
* Save Time – Don't have time to update your photos on your site? Save time and increase efficiency by only posting your photos to Instagram and automatically displaying them on your website
* Keep Your Site Looking Fresh – Automatically push your new Instagram content straight to your site to keep it looking fresh and keeping your audience engaged.
* Super simple to set up – Once installed, you can be displaying your Instagram photos within 30 seconds! No coding required, no complex steps or Instagram Developer account needed.


== Installation ==

### Manual

* Upload the Simple Photo Feed folder to the plugins directory in your WordPress installation
* Activate the plugin.
* Navigate to the "Simple Photo Feed" Menu.

### Admin Installer via Zip

* Visit the Add New plugin screen and click the "Upload Plugin" button.
* Click the "Browse…" button and select zip file from your computer.
* Click the "Install Now" button.
* Once done uploading, activate Simple Photo Feed.
* Navigate to the "Simple Photo Feed" Menu.

That's it! Now you can easily display Instagram posts in your WordPress from your Instagram account.

== Frequently Asked Questions ==

= When is "Simple Photo Feed" needed?  =

Whenever you want your Instagram posts to be shown as a gallery in your Wordpress website. Increase social engagement, save time by only posting your photos to Instagram and automatically displaying them on your website and keep your site looking fresh.

= Do I need Instagram Access Tokens or Developer Accounts to use this?  =

We've made it super easy for you. Just click the "Connect Account" button, login to your Instagram and accept the plugin. That's it!

= Does this work with Personal Accounts?  =

Unfortunately, not anymore. Meta has decided to deprecate the Instagram Basic API and [turn it off on December 4th, 2024](https://developers.facebook.com/blog/post/2024/09/04/update-on-instagram-basic-display-api/). That was the API that regular, personal accounts could use to have access to their media posts. The only solution to embed your instagram images in your WordPress site now is to use [their Business APIs](https://developers.facebook.com/docs/instagram-platform). That means you must change your account to [a Creator or Business type](https://help.instagram.com/502981923235522#how-to-switch-your-creator-account-to-a-business-account-on-instagram). This is a very easy process and you can [always switch back](https://help.instagram.com/1717693135113805/).

= My Instagram feed isn't displaying. Why not!? =

There are a few common reasons for this:

* Your Access Token may not be valid anymore. Click "Disconnect Account" and re-connect to refresh the connection with Instagram's API.
* The cached feed may be invalid. Just click the "Clear Cache" button in Settings and the plugin will fetch a fresh feed of media from your Instagram account.
* You haven't added the shortcode. Add [simple-photo-feed] in a page/post, where you want your instagram feed to be displayed.
* You have a Personal account. Please switch to Creator or Business account.

If you're still having an issue displaying your feed then please open a ticket in the [Support forum](https://wordpress.org/support/plugin/simple-photo-feed) with a link to the page where you're trying to display the Instagram feed and, if possible, a link to your Instagram account.

= Does "Simple Photo Feed" require manual coding or file editing? =

Absolutely not. No technical knowledge is needed at all. With a few clicks your Instagram posts will be synced with your website.

= How do I embed my Instagram Feed directly into a WordPress page template? =

You can embed your Instagram feed directly into a template file by using the WordPress do_shortcode function: `<?php echo do_shortcode('[simple-photo-feed]'); ?>`

= Is "Simple Photo Feed" compatible with my theme/plugins? =

Of course! "Simple Photo Feed" is compatible with any theme and plugin that follows WordPress coding standards.

== Screenshots ==

1. Plugin Settings Page
2. Shortcode Usage

== Changelog ==

= 1.4.2 =
* Ensure compatibility with WP 6.8
* Improved code maintainability and security
* Ensured full WordPress coding standards compliance (PHPCS)

= 1.4.1 =
* Compatibility with WordPress 6.7.2
* Add nonce to Ajax calls for improved security

= 1.4.0 =
* Update to Business API after Basic API deprecation
* Allow Contributors and above to access the menu page

= 1.3.2 =
* Compatibility with WordPress 6.7

= 1.3.1 =
* Remove the phrase "for Instagram" from plugin's name
* Compatibility with WordPress 6.6

= 1.3.0 =
* Added Lightbox Feature - Credits: @F13Dev
* Fix images aspect ratio in small view
* Fix empty options on first activation

= 1.2.0 =
* Compatibility with WordPress 6.5
* Fix feed cache when empty transient

= 1.1.0 =
* Compatibility with WordPress 6.4
* WP Coding Standards compliant
* Fix CSS styling
* Fix bug regarding Instagram redirect
* Fix notice when caption was empty

= 1.0.2 =
* CSS fix

= 1.0.1 =
* Update to support latest WP version

= 1.0.0 =
* Initial release

== Upgrade Notice ==
