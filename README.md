# Simple Photo Feed

_A WordPress plugin to connect to your Instagram account_

**Simple Photo Feed** provides an easy way to connect to your Instagram account and display your photos in your WordPress site.

![Simple Photo Feed - Overview Page](https://ps.w.org/simple-photo-feed/assets/screenshot-1.jpg)

**Simple Photo Feed** is a free WordPress plugin that lets you embed your Instagram photos as a gallery in your website.

### Published at WordPress official repository

* [Download it from WordPress.org](https://wordpress.org/plugins/simple-photo-feed/)
* [Browse source code](https://plugins.trac.wordpress.org/browser/simple-photo-feed/)
* [Revisions & Changelog](https://plugins.trac.wordpress.org/log/simple-photo-feed/)

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

## Access Control

Simple Photo Feed includes configurable access control that allows site administrators to choose which user roles can access and configure the plugin settings.

For detailed documentation, see [Access Control Guide](ACCESS_CONTROL.md).

## Usage

You can use the shortcode below in your Posts/Pages:

`[simple-photo-feed]`

Choose how many images to show (1-100):

`[simple-photo-feed view="12"]`

Show your captions (on/off):

`[simple-photo-feed view="12" text="on"]`

Smaller thumbnails, more columns:

`[simple-photo-feed view="12" text="on" size="small"]`

With a built in lightbox

`[simple-photo-feed view="12" lightbox="on"]`

![Simple Photo Feed - Shortcode Usage](https://ps.w.org/simple-photo-feed/assets/screenshot-2.jpg)

## Changelog

### 1.4.3
* Added configurable access control - admins can now choose which user roles can access the
plugin settings
* Default remains administrators only for security
* Added filter hook `spf_required_capability` for developers to customize access control

### 1.4.2
* Ensure compatibility with WP 6.8
* Improved code maintainability and security
* Ensured full WordPress coding standards compliance (PHPCS)

### 1.4.1
* Compatibility with WordPress 6.7.2
* Add nonce to Ajax calls for improved security

### 1.4.0
* Update to Business API after Basic API deprecation
* Allow Contributors and above to access the menu page
