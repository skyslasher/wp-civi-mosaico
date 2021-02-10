# WordPress integration for CiviMail with Mosaico

This plugin integrates WordPress with the [CiviCRM mail editor replacement Mosaico](https://civicrm.org/extensions/email-template-builder).
It contains an enhanced Versafix template with a new template block. This block comes with a
property editor that shows all available WordPress posts. It applies the post title, excerpt with
adjustable length an a read more button with just one click. In addition, section margins can be
applied and href anchors can be set to allow an easy in-mail navigation via a TOC.
The gallery section uses the WordPress media library as a source.

The plugin is one of the successors of the
[Wordpress Integration for CiviMail with Mosaico plugin](https://github.com/skyslasher/de.ergomation.wp-civi-mosaico)
that is now split into three separate plugins:
* [CiviCRM Mosaico Plugin Interface](https://github.com/skyslasher/de.ergomation.civi-mosaico-plugininterface)
* [CiviCRM Flexmailer Embed Images](https://github.com/skyslasher/de.ergomation.civi-flexmailer-embedimages)
* WordPress CivCRM Mosaico Integration (this plugin)

![Screenshot](/images/screenshot.png)
WordPress posts view, visible in Mosaico section plugin control

![Screenshot](/images/screenshot_4.png)
Corresponding WordPress posts

![Screenshot](/images/screenshot_2.png)
Gallery reflecting WordPress media in Mosaico

![Screenshot](/images/screenshot_3.png)
Corresponding WordPress media gallery


The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* WordPress v5.0+
* CiviCRM 5.x
* CiviCRM FlexMailer plugin
* CiviCRM Mosaico plugin
* [CiviCRM Mosaico Plugin Interface](https://github.com/skyslasher/de.ergomation.civi-mosaico-plugininterface)

## Optional WordPress plugins

This plugin can use functionality of these WordPress plugins:
* [Polylang (translations)](https://wordpress.org/plugins/polylang/)
* [Co-Authors Plus (multiple post authors)](http://wordpress.org/extend/plugins/co-authors-plus/)
* [Reading Time WP (estimated reading time for the full post)](https://wordpress.org/plugins/reading-time-wp/)

## Installation (Web UI)

Download the ZIP from GitHub and install it in the admin plugin section.

## Usage

Open the gallery. It shows the WordPress media library. You can also use the enhanced Versafix template with the WordPress posting block if needed.

## Known issues

If images fail to display correctly in the mail editor, there may be corrupted cached image files. In the backend, go to Tools -> Flush WP CiviCRM Mosaico image cache and press the button "Flush image cache". Afterwards, insert the image again.
