=== Frontend Dashboard Notification ===
Contributors: vinoth06, buffercode
Tags: dashboard, frontend dashboard, notification, notices, alerts, announcements, banner, broadcast
Donate link: https://www.paypal.com/paypalme2/buffercode
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend Dashboard Notification is a free add-on for Frontend Dashboard which allows administrators to create and manage rich alerts and notifications across dashboard pages.

== Description ==

> #### Notice
> This is a free add-on plugin for [Frontend Dashboard](https://buffercode.com/plugin/frontend-dashboard). Please install and activate Frontend Dashboard (v3.0.0+) to use this plugin.

**Frontend Dashboard Notification** allows administrators to broadcast announcements, maintenance alerts, onboarding tips, or promotions directly within user dashboards.

### Features
* **Custom Admin Management**: Manage all notifications with clean custom form fields right inside the Frontend Dashboard Settings panel.
* **Alert Themes & Types**: Choose between Info (Blue), Success (Green), Warning (Amber), Danger (Red), and Neutral/Brand (Indigo) visual presets.
* **Flexible Slot Positioning**: Display notifications at 6 slot locations:
  - Content Outside Top
  - Content Top
  - Panel Inside Top
  - Panel Inside Bottom
  - Content Bottom
  - Content Outside Bottom
* **Menu Page Targeting**: Restrict alerts to specific dashboard menu tabs (e.g. Dashboard, Posts, Profile) or broadcast universally to all menus.
* **User Role Targeting**: Selectively display notifications to specific user roles or all users.
* **Dismissible Banners**: Enable close buttons with options for permanent dismissal (per-user preference) or session-based dismissal.
* **Quick Status Toggle**: Instantly enable or pause alerts with a single click.

== Installation ==

1. Upload the `frontend-dashboard-notification` directory to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure **Frontend Dashboard** is also installed and activated.
4. Navigate to **Frontend Dashboard > Settings > Notification** to create and manage notifications.

== Changelog ==

= 3.0.0 =
* Complete modernization: Replaced legacy post meta editor with dedicated form fields inside Frontend Dashboard Settings.
* Added rich WYSIWYG editor support (wp_editor) with image and media upload capabilities for notification details.
* Added modern Alert Styles (Info, Success, Warning, Danger, Neutral) and custom live color customizer.
* Added instant AJAX status toggle and SweetAlert confirmation modals.
* Added responsive slot and menu targeting.
* Refactored frontend rendering engine for Frontend Dashboard 3.0+ App Shell compatibility.

== Upgrade Notice ==

= 3.0.0 =
Major release: Modernized form interface, rich WYSIWYG editor support, live theme styling, and full compatibility with Frontend Dashboard 3.0.0.
