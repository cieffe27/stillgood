=== StillGood ===
Contributors: cieffe27
Tags: evergreen, notice, badge, old posts, content
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show a clean, lightweight badge before evergreen posts older than a chosen number of years, with per-post exclusion.

== Description ==

**StillGood** helps readers trust your evergreen archive by displaying a minimal notice at the top of older posts that are still accurate and relevant.

Key features:

* **Automatic age check** – Show the badge only on posts older than X years (default: 5 years).
* **Custom message** – Edit the badge copy from Settings and optionally use the `{years}` placeholder to display the exact age of each post.
* **Per-post exclusion** – Hide the badge on specific posts with a single checkbox in the post editor.
* **Custom colors** – Pick background, accent border, and text colors right from the WordPress dashboard.
* **Zero bloat** – No external libraries, no extra database queries, and inline CSS loaded only on eligible posts.

== Installation ==

1. Upload the `stillgood` folder to the `/wp-content/plugins/` directory, or install the plugin ZIP directly through the WordPress Plugins screen.
2. Activate **StillGood** through the **Plugins** menu in WordPress.
3. Go to **Settings → StillGood** to customize the minimum age, message, and colors.

== Frequently Asked Questions ==

= Can I display the real age of the article? =

Yes. Include `{years}` anywhere in the badge text under **Settings → StillGood**, and StillGood will automatically replace it with the number of full years since publication.

= Can I hide the badge on a specific post? =

Yes. Open the post in the editor, find the **StillGood** box in the sidebar, and check the exclusion option.

= Does it slow down my site? =

No. StillGood loads no JavaScript and injects a tiny inline CSS rule only on single posts where the badge is actually displayed.

= Is StillGood translation-ready? =

Yes. StillGood includes English and Italian out of the box and supports WordPress translations, so you can easily add your own language.

== Screenshots ==

1. The StillGood badge displayed cleanly above an older evergreen article.
2. Simple settings screen under Settings → StillGood to customize age threshold, text, and colors.

== Changelog ==

= 1.0.0 =
* Initial release.

= 1.1.0 =
* Added Italian translation via .po (works with WordPress versions 5.8 and later).

= 1.2.0 =
* Updated the translation using the it_IT.l10n.php method; now requires WordPress 6.5

= 1.2.1 =
* Add the spanish translation
