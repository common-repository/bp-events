=== BuddyPress Events Calendar ===
Contributors: erwingerrits
Donate link: http://erwingerrits.com/
Tags: BuddyPress,events
Requires at least: BP v. 1.1.3
Tested up to: Wordpress mu 2.8.6
Stable tag: 1.1.3

Adds an events calendar to the WordPress MU/BuddyPress platform. Trunk version. Compatible with BP 1.1.3 and newer ONLY. For older versions of BuddyPress use bp-events 0.61.



== Description ==

 the bp-events plugin will add an option to the user menu called "Events". From this menu users can create events and invite friends to join.
- the plugin will add an "Events" option to tne main menu system for the events directory view
- add the "Events" widget to list the upcoming/newest/active/popular events on your front page


== Installation ==

- Unzip package
- upload "bp-events" folder into "plugins" directory along side buddypress
- move "events" folder to your active theme (if using default theme, use "bp-default")
- activate and you're done!


== Contact ==



For suggestions, bugs, hugs and love can be donated at the following locations.



1. [Authors page](http://www.erwingerrits.com/)

2. [Project webpage](http://www.erwingerrits.com/?p=799)


== Frequently Asked Questions ==

= Why are certain previous features missing? =

Features like categories, tags, map location, birthday events, deleting of old events, calender view are not (yet) included. As some of these features are not needed for core functionality of the events component, they have been left out for upcoming version. Some features will be implemented by other plugins, while others might not return at all.

= Will there be a calendar view? =

Yes there will. (notince my vagueness in setting a timeframe). There will be calendars galore: big ones, small ones, widgets, what have you. Be patient!


== Version History ==

version 1.1.3;

- works with bp 1.1.3

version 1.1:

- re-factored code to support bp 1.1
- added only the necessary functions that make up the core events component
- added events group template tags for easy group event theming
- added css support for bp_event_date
- added validation so events start is before end date and are future dates
- added support for forum topics
- changed filter from all to upcoming events
- added an "archived" filter
- removed extranous and broken code
- general code cleanup 
- bp-events has the same api as bp-groups which makes it easy for developers to add on to the events functionality. See buddypress codex for details.



version 0.6:

- moved directory view to member theme instead of home theme to keep in line with rest of BuddyPress components
- fixed numerous bugs
- added calendar views for directory and widgets
- added listing of HomePage link on Event Page under "Description" - added "Homepage" to translatables
- added 'addslashes()' and 'stripslashes()' to homepage field storage in events_classes.php



version 0.59:

- fixed December 1969 birthday event bug
- fixed theme errors due to bp-core template path change
- fixed debugging-leftover echo "*****";
- made compatible with BP 1.0.1 by adding "session_start();"
- added option in back-end to disable event forum creation
- added option in back-end to disable event wire creation
- fixed some missed translatables (event creation months/ampm drop-downs, time to-go calculations, "Uncategorized", event_date  and event_type functions)
- fixed two "forth" to "fourth" and removed space between "for" and "one" in the groups drop down
- added missing <strong> tag to event_settings.php
- fixed Event directory Search bug from "post" to "get"
- now properly hides 'old' events if set in back end
- added Swedish language file


version 0.58:

- added translation and language files for russian (thanks to slaFFik!!!)
- fixed some bugs (as found/fixed by slaFFik)
- fixed event directory search bug (as spotted and fixed by Damian Hartner in the bp-events download page comments) eventhough it there still is some work to be done on this page -- next version -- I promise!
- fixed group-event erroneous group link (try saying that three times in a row)


version 0.57:

- descriptions will now be saved when creation events (a fairly imporant function)
- icons work again (broken since moving to plugins/buddypress)
- fixed birthday event creation bug that created a birthday event even though it was switched off in the backend
- fixed function invited_user_notification error that popped up on last event creation screen
- fixed event invite email bugs


version 0.56:

- fixes bug that gave errors when going to event home and event directory
- fixes icons that got lost moving to plugins from mu-plugins

version 0.55:

- group events
- birthday events/profile field integration
- fixed various CSS errors
- works with latest trrunk of BuddyPress

version 0.54:

- added icons for user menu and nav bar
- took out twitter account/password and put it in separate plugin (eg-twitter, included) to make it more globally accessible, and less cluttered event creation
- upgraded javascript on "All Day" events so options not needed will be hidden properly
- now hides "My Events" box from user profile if user has not signed up for any events (used to say "My Events(0) - User hasn't joined any events yet")
- fixed bug in BP_Events::get_all() function (thanks Simon Pritchard for his debugging skills!)
- started to add some hooks to event creation/editing for plugins to extend events plugin
- Today's Events widget, lists all events for today (starting today, or started in the past & lasting through today). Hides if there are no events.
- Upcoming Events widget 
- added more options to backend for: event categories, birthdays and old event management 
- fixed bug that showed “No Events” when going to Events page. Now lists All Events 
- added icons for user menu and nav menu 
- fixed bug in Event Creation: when going back to Event Times screen while creating events, the dates were recorded as “December 1969?. This is now fixed! Yay! 
- fixed bug in default time (today) with minutes showing up as “min” if current minute was not 00,15,30 or 45 minutes. Now rounds current time to next interval of 15 minutes 
- fixed as much as I could on the forum portion of Events… My forum connection with  BuddyPress is broken, so I can’t test it anymore  Please test the forum setup and creation of topics, and let me know. 

version 0.53:

- edit event is now working as expected
- added Google Maps/Yahoo Maps maps to event locations through a slightly modified version of the excellent GeoPress plugin by Andrew Turner & Mikel Maron (http://georss.org/geopress)
- twitter hook up to send out tweets to user's account upon event creation
- added template tags listed above under "TEMPLATE TAGS"
- started work on tagging events, you can enter them, but anything beyond that will have to wait until the next version
- started work on "Maybe Attend" in addition to "Will Attend" and "Will Not Attend". Sort of half working right now (buttons show up in the correct place - hey, it's a start)

version 0.52:

- events widget listing upcoming (default), popular, active & newest events
- create event with start date/time and end date/time works (except avatar upload)
- added "all day" event type
- added My Events box to user profile
- cleaned up blank lines at end of file messing some folk's installations up
- fixed minor bugs displaying some text throughout create events screens

version 0.51:

- added recurring events
- added reminder functionality

version 0.5:

- basic functionally (believe me, it's hard enough to get here!)
- wire & forums work
- integration into main menu (adds "Events" to main nav menu and user menus)


`<?php code(); // goes in backticks ?>`