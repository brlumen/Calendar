# Public API for other plugins

Available in Calendar 3.0.0 and newer.

Other MantisBT plugins can create calendar events without declaring a hard
dependency on Calendar. The contract is the request class
`\CalendarPluginApi\EventCreateRequest`: checking that it exists at runtime is
enough to know whether Calendar is installed and loaded.

The [TelegramBot](https://github.com/mantisbt-plugins/TelegramBot) plugin uses this
API since its version 2.0: it creates calendar events from Telegram and
subscribes to the calendar signals to announce them, so it doubles as a
worked example of the integration.

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

## Subscribing without a hard dependency

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
