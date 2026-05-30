=== BlogLogistics Content Signals for Robots.txt ===
Contributors: bloglogistics
Tags: robots.txt, content signal, ai, search, crawlers
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 1.0.6
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
* Shows the full robots.txt file for review and manual editing.
* Syncs the plain-language toggles when the Content-Signal line is manually edited.
* Shows when the file was last changed by this plugin.
* Lists available backups and allows restoring a selected backup.
* Removes plugin settings and plugin-created backup files when the plugin is deleted, while leaving the current robots.txt file as-is.
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

= Can I manually edit robots.txt? =

Yes. The settings page includes a full robots.txt editor. If you manually change the Content-Signal line under User-agent: *, the toggle boxes update to match after saving.

= Can I restore a backup? =

Yes. The settings page lists the latest available backups with restore buttons. Restoring a backup replaces the current robots.txt file with the selected backup. No new backup is created during restore.

= What happens if I delete the plugin? =

The plugin removes its saved settings and plugin-created backup files. Your current robots.txt file is left as-is.

== Changelog ==

= 1.0.6 =
* Add BlogLogistics plugin icon assets.
* Add icon metadata to the generated update manifest for WordPress update screens and plugin details.

= 1.0.5 =
* Disable Save Preferences until website-use preferences actually change.
* Disable Save full robots.txt until the editor contents actually change.
* Disable Restore recommended defaults when the saved settings already match the recommended defaults.
* Prevent no-change saves from creating backups on the server side.
* Show clear no-change and unsaved-change messages.

= 1.0.4 =
* Replace page-reload restore confirmation with inline row-level confirmation in the backups table.
* Keep users in place when confirming a backup restore.

= 1.0.3 =
* Replace the browser restore pop-up with an in-page backup restore confirmation.
* Show the selected backup date and time in the restore confirmation.
* Change the confirmation button to say Yes, restore this backup.

= 1.0.2 =
* Do not create a new backup during backup restore.
* Show backup timestamps with seconds so backups created close together are easier to tell apart.

= 1.0.1 =
* Add full robots.txt editor for review and manual editing.
* Sync settings toggles when the Content-Signal line is manually edited.
* Remove the server file path from the status display.
* Show when robots.txt was last changed by this plugin.
* List available backups and add restore buttons.
* Add uninstall cleanup for plugin settings and plugin-created backup files while leaving robots.txt as-is.

= 1.0.0 =
* Initial release.
* Add physical robots.txt Content-Signal management.
* Add plain-language settings for search results, AI answers, and AI training.
* Add original-line restore behaviour when management is turned off.
* Add timestamped backups and keep only the latest 5 backups.
* Add BlogLogistics admin menu integration.
* Add BlogLogistics manifest-based updates and automated release workflow.
