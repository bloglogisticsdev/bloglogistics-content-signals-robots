=== BlogLogistics Content Signals for Robots.txt ===
Contributors: bloglogistics
Tags: robots.txt, content signal, ai, search, crawlers
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 1.0.0
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Safely manages website-use preference signals in a physical robots.txt file.

== Description ==

BlogLogistics Content Signals for Robots.txt safely manages the website-use preference line in a physical robots.txt file.

This plugin is intended for websites that already use a real robots.txt file on the server. It does not rewrite the whole file. It only manages the Content-Signal line placed directly under User-agent: *.

The default setting is:

* Search results: On
* AI answers: On
* AI training: Off

This creates:

Content-Signal: search=yes, ai-input=yes, ai-train=no

These settings publish your website’s preferences in robots.txt. They do not guarantee that every crawler, search engine, or AI system will follow them.

== Features ==

* Adds or updates the Content-Signal line directly under User-agent: *.
* Restores the original Content-Signal line when management is turned off.
* Removes the managed line when management is turned off and there was no original line.
* Performs surgical edits only and leaves all other robots.txt lines alone.
* Saves a backup before changing robots.txt.
* Keeps only the latest 5 backups to avoid filling the server with old files.
* Adds settings under BlogLogistics > Robots.txt Content Preferences.
* Uses BlogLogistics manifest-based updates.

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/.
2. Activate the plugin in WordPress.
3. Go to BlogLogistics > Robots.txt Content Preferences.
4. Confirm the physical robots.txt file is found and writable.
5. Choose the website-use preferences you want to publish.

== Frequently Asked Questions ==

= Does this plugin rewrite my whole robots.txt file? =

No. It only manages the Content-Signal line under User-agent: * and leaves the rest of the file alone.

= Does this plugin work without a physical robots.txt file? =

This plugin is intended for sites that already use a physical robots.txt file. If no physical robots.txt file is found, the settings page explains that the plugin cannot manage it automatically.

= What happens if I turn management off? =

If there was an original Content-Signal line, the plugin restores it. If there was no original Content-Signal line, the managed line is removed.

= Why does the plugin save backups? =

Before changing robots.txt, this plugin saves a backup copy. The plugin keeps the latest 5 backups so you can recover from mistakes without filling your server with old files.

== Changelog ==

= 1.0.0 =
* Initial release.
* Add physical robots.txt Content-Signal management.
* Add plain-language settings for search results, AI answers, and AI training.
* Add original-line restore behaviour when management is turned off.
* Add timestamped backups and keep only the latest 5 backups.
* Add BlogLogistics admin menu integration.
* Add BlogLogistics manifest-based updates and automated release workflow.
