# MantisBT Calendar Plugin

[![Join the chat at https://gitter.im/mantisbt-plugins/Calendar](https://badges.gitter.im/mantisbt-plugins/Calendar.svg)](https://gitter.im/mantisbt-plugins/Calendar?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Overview
--------
Adds the task scheduling function in MantisBT based on the calendar of events with the possibility of one-way synchronization with Google Calendar.

Screenshots
-----------

![alt text](doc/main_view_with_filter_list.png)

<!-- SCREENSHOT PLACEHOLDER (3.0.0): month view.
     Calendar page with ?view=month, a month with events on several days,
     including a day that shows the "+N more" indicator.
     Save as doc/month_view.png and uncomment the line below.
![alt text](doc/month_view.png)
-->

<!-- SCREENSHOT PLACEHOLDER (3.0.0): day modal of the month view.
     The modal opened from a day header, listing the events of that day.
     Save as doc/month_view_day_modal.png and uncomment the line below.
![alt text](doc/month_view_day_modal.png)
-->

<!-- SCREENSHOT PLACEHOLDER (3.0.0): time range selection in the week view.
     A day column with a period selected by dragging, showing the chosen range.
     Save as doc/week_view_time_range_selection.png and uncomment the line below.
![alt text](doc/week_view_time_range_selection.png)
-->

<!-- SCREENSHOT PLACEHOLDER (3.0.0): multi-day events in the week view.
     The week grid with two or three multi-day bands above the hourly rows,
     one of them cut at the edge of the week; events of several projects in
     their colours and the project legend in the bottom toolbar.
     Save as doc/week_view_multiday_bands.png and uncomment the line below.
![alt text](doc/week_view_multiday_bands.png)
-->

![alt text](doc/view_event_layers_in_bug_view.png)
![alt text](doc/add_event_view.png)

<!-- SCREENSHOT PLACEHOLDER (3.0.0): time zone selector on the event form.
     The event create or edit page with the time zone select expanded.
     Save as doc/add_event_timezone_select.png and uncomment the line below.
![alt text](doc/add_event_timezone_select.png)
-->

<!-- SCREENSHOT PLACEHOLDER (3.0.0): event view page of a recurring event.
     The view page showing the recurrence row and the "Created in timezone" row.
     Save as doc/view_event_with_timezone.png and uncomment the line below.
![alt text](doc/view_event_with_timezone.png)
-->

![alt text](doc/plugin_config_view.png)
![alt text](doc/workflow_thresholds_page.png)

<!-- SCREENSHOT PLACEHOLDER (3.0.0): reminders and notifications of a user.
     My Account -> "Event calendar" tab with the default reminders and the
     notification switches.
     Save as doc/account_event_calendar_tab.png and uncomment the line below.
![alt text](doc/account_event_calendar_tab.png)
-->

<!-- SCREENSHOT PLACEHOLDER (3.0.0): notification recipient matrix.
     Manage -> Manage Plugins -> Calendar -> notification settings page with
     the per-project matrix.
     Save as doc/notify_config_page.png and uncomment the line below.
![alt text](doc/notify_config_page.png)
-->

<!-- SCREENSHOT PLACEHOLDER (3.0.0): event view page with description,
     reminders and history.
     Save as doc/view_event_page.png and uncomment the line below.
![alt text](doc/view_event_page.png)
-->

Features
--------
- The ability to create event.
- Binding any number of bugs to event.
- Bug can be related to any number of events.
- Visual display of events in bugs view page.
- One-way synchronization with Google Calendar (v. >= 2.3.0)
- Support for different time zones.
- Recurring events (v. >= 2.4.0).
- Month view (v. >= 3.0.0).
- Creating an event by selecting a time range in the week view — drag with the mouse or use two taps on a touch screen (v. >= 3.0.0).
- Per-event time zone: an event remembers the time zone it was scheduled in, and recurring events keep their local time across DST transitions; the time zone selector shows the UTC offset of every zone (v. >= 3.0.0).
- Events that span several days, shown as bands above the week grid (v. >= 3.0.0).
- Events are coloured by project, and a project legend below the calendar switches the current project with one click (v. >= 3.0.0).
- Event description (v. >= 3.0.0).
- E-mail reminders about upcoming events: per-event reminders or personal defaults, with a per-user opt-out (v. >= 3.0.0).
- E-mail notifications about created, changed and deleted events and about membership changes, with a per-project recipient matrix like the one of MantisBT itself (v. >= 3.0.0).
- Event history, with records written by other plugins shown next to the native ones (v. >= 3.0.0).
- Public API for other plugins: create events from your own plugin, write to the history of an event, ask who would be notified and subscribe to calendar changes (v. >= 3.0.0).

Supported Versions
------------------
- MantisBT 2.14 to 2.25.x - supported in release up to 2.6.x (fixes only)
- MantisBT 2.26.0 and higher - supported in release 2.7.0 and higher

Download
--------
Please download the stable version.
(https://github.com/mantisbt-plugins/Calendar/releases/latest)


How to install
--------------

1. Copy Calendar folder into plugins folder.
2. Open Mantis with browser.
3. Log in as administrator.
4. Go to Manage -> Manage Plugins.
5. Find Calendar in the list.
6. Click Install.


How to enabled Google Calendar Sync (for Calendar version >= 2.3.0 )
----------------------------------------------------------------

1. Go to [Google Developers Console](https://console.developers.google.com/) and create the new project.
2. Download JSON file.
3. Upload the JSON file on the Calendar settings page.
4. Click the save button.
5. Go to the calendar settings for a specific user and click "Enable sync with Google Calendar"
6. Give permission to manage your calendars Google.
7. Select a calendar for one-way synchronization with Google Calendar.

Detailed instructions are provided in the project wiki.
https://github.com/mantisbt-plugins/Calendar/wiki#how-to-enabled-google-calendar-sync


Reminders and notifications (for Calendar version >= 3.0.0)
------------------------------------------------------------

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

Public API for other plugins (for Calendar version >= 3.0.0)
------------------------------------------------------------

Other MantisBT plugins can create calendar events without declaring a hard
dependency on Calendar. The contract is the request class
`\CalendarPluginApi\EventCreateRequest`: checking that it exists at runtime is
enough to know whether Calendar is installed and loaded.

```php
if( class_exists( 'CalendarPluginApi\\EventCreateRequest' ) ) {
    $t_request = new \CalendarPluginApi\EventCreateRequest();

    $t_request->project_id = $t_project_id;   // required
    $t_request->name       = 'Sprint review'; // required
    $t_request->user_id    = $t_user_id;      // required, the event author
    $t_request->date_from  = $t_from;         // required, Unix timestamp
    $t_request->date_to    = $t_to;           // required, Unix timestamp; a later
                                              // day makes a multi-day event

    $t_request->description        = 'Agenda: ...';      // optional
    $t_request->duration           = 3600;               // seconds; optional for a single
                                                         // event, required for a recurring one
    $t_request->bug_ids            = array( $t_bug_id ); // optional, attach issues
    $t_request->members            = array( 15, 22 );    // optional, defaults to the author
    $t_request->recurrence_pattern = 'RRULE:...';        // optional, RFC 5545
    $t_request->timezone           = 'Europe/Moscow';    // optional
    $t_request->reminders          = array( 900, 86400 ); // optional, see below

    $t_event_id = calendar_api_event_create( $t_request );
}
```

For a recurring event `date_to` is the end of the last occurrence and
`duration` is the length of one occurrence. `reminders` mirrors the three
states of the event form: `null` (the default) leaves every recipient with
their personal default reminders, a list of offsets in seconds before the start
of an occurrence (each at least 60) applies to every recipient instead, and an
empty array switches reminders off for this event.

The facade owns all of the calendar rules, so the caller may hand over raw
input: it verifies that everything referenced exists, that the author passes
`report_event_threshold` and may view every attached issue, and that every
member is eligible for the project — all before the event is written, so a
rejected request never leaves a partial event behind. Wrong types fail with a
`TypeError` at the assignment, missing required fields and ineligible values
raise the usual MantisBT errors.

`calendar_api_candidate_members( $p_project_id, $p_user_id )` returns the user
ids the given user may sign up as members — use it to build a member picker;
any subset of the returned list is guaranteed to be accepted.

`calendar_api_candidate_issues( $p_project_id, $p_user_id, $p_page, $p_per_page )`
returns the issues the given user may attach the event to, page by page, from
the same source as the issue selector of the event form — use it to build an
issue picker; any subset of the returned ids is guaranteed to be accepted.

`calendar_api_event_history_log( $p_event_id, $p_field_name, $p_old_value, $p_new_value, $p_user_id = null, $p_basename = null )`
writes a record to the history of an event, the way `plugin_history_log()` of
the core does for an issue. The field name is prefixed with the basename of the
calling plugin, and that prefixed name doubles as the language key of the
label: define `$s_plugin_<Basename>_<field_name>` in your language files, or
the raw field name is shown. Values are stored and displayed as given.

`calendar_api_event_notify_recipients( $p_event_id, $p_action, $p_actor_id = null )`
returns the user ids the calendar itself would notify about the given action —
one of `created`, `updated`, `deleted`, `member_added`, `member_removed` —
after the recipient matrix, the personal settings and the access checks have
been applied. Use it to deliver the same notification through your own channel;
the master switch of the calendar mails is deliberately not consulted.

Calendar also declares events other plugins can hook. The first three receive
the event id as their only parameter; `EVENT_CALENDAR_EVENT_CREATED` is
signalled only after the members, issues and reminders of the event have been
written, and `EVENT_CALENDAR_EVENT_DELETED` before anything is deleted, so the
handler still finds the event and its members:

- `EVENT_CALENDAR_EVENT_CREATED` (`EVENT_TYPE_EXECUTE`)
- `EVENT_CALENDAR_EVENT_UPDATED` (`EVENT_TYPE_EXECUTE`)
- `EVENT_CALENDAR_EVENT_DELETED` (`EVENT_TYPE_EXECUTE`)
- `EVENT_CALENDAR_EVENT_REMINDER` (`EVENT_TYPE_EXECUTE`) — once per due
  reminder with `array( $p_event_id, $p_occurrence_timestamp, $p_user_id,
  $p_offset_seconds )`, raised even when no mail is sent, so a subscriber can
  deliver the reminder through its own channel.
- `EVENT_CALENDAR_NOTIFY_USER_INCLUDE( $p_event_id, $p_action )` and
  `EVENT_CALENDAR_NOTIFY_USER_EXCLUDE( $p_event_id, $p_action, $p_user_id )`
  (`EVENT_TYPE_DEFAULT`) — take part in the choice of the recipients of a
  notification, like `EVENT_NOTIFY_USER_INCLUDE` / `EVENT_NOTIFY_USER_EXCLUDE`
  of the core: the first returns extra candidate user ids (they still pass all
  the usual checks), a truthy answer to the second drops the candidate.

### Subscribing without a hard dependency

MantisBT initializes plugins one at a time, so when your plugin's `hooks()`
runs, Calendar may not be initialized yet: its classes do not exist and its
events are not declared. Hooking an undeclared event is silently dropped.
Two reliable patterns:

1. Pre-declare the event in your `hooks()`. All plugins are *registered*
   before any of them is initialized, so `plugin_is_registered( 'Calendar' )`
   is dependable there. Declare the event with the exact type listed above —
   `event_declare()` is a no-op for an already declared event, and the first
   declaration wins the type used by every later signal:

   ```php
   function hooks() {
       $t_hooks = array( /* your other hooks */ );
       if( plugin_is_registered( 'Calendar' ) ) {
           event_declare( 'EVENT_CALENDAR_EVENT_CREATED', EVENT_TYPE_EXECUTE );
           $t_hooks['EVENT_CALENDAR_EVENT_CREATED'] = 'on_calendar_event';
       }
       return $t_hooks;
   }
   ```

2. Or subscribe late: hook the core `EVENT_PLUGIN_INIT` event, which is
   signalled after every plugin has been initialized, and call `event_hook()`
   from its handler — at that point `class_exists` is reliable and the
   Calendar events are declared.

Do not use `$this->uses` for this: a soft dependency keeps your plugin
uninitialized while Calendar is registered but waiting for a schema upgrade.

Donate
--------------
All work on this plugin consists of many hours of coding during our free time, to provide you with a Calendar 
that is easy to use. If you enjoy using this plugin and would like to say thank you, donations are a great 
way to show your support.

Donations are invested back into the project 👍

Thank you for keeping this project alive 🙏

Available methods:
1. TGFFBC28Wo27aQ24L4ku6y3Egbe12Jhv1k (USDT TRC20)
2. 1PxyVPeYhRUtt5Mg1t3xSmFtHSYf2CabLR (BTC)
