# Reminders and Notifications

Available in Calendar 3.0.0 and newer.

Both features are switched off after the installation. Enable them on the
Calendar settings page (Manage -> Manage Plugins -> Calendar):

- **Reminders** are e-mails sent before the start of an occurrence to the
  author and the members of the event. An event may carry its own reminders,
  fall back to the personal defaults of each recipient, or have reminders
  switched off; every user manages their defaults and opt-out on the
  "Event calendar" tab of "My Account". Reminders are dispatched by the
  MantisBT cron job (`scripts/cronjob.php`, hook `EVENT_CRONJOB`); without a
  cron job they are still sent from page loads, throttled to once in five
  minutes. The settings page shows when the cron job last ran.
- **Notifications** are e-mails about created, changed and deleted events and
  about membership changes. Who gets them is decided by a recipient matrix
  (author, members, the acting user) that can be overridden per project, like
  the e-mail notification settings of MantisBT itself, and every user can turn
  off each kind of notification on the same "My Account" tab. The user who made
  the change never gets a mail about it.
