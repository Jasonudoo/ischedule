=== BirchPress Scheduler - Appointment Booking Calendar === 
Contributors: birchpress
Tags: appointment, appointment booking, appointment booking calendar, appointment calendar, appointment plugin, appointment scheduling calendar, appointments, Booking, Booking calendar, calendar, scheduling, scheduling plugin, wordpress appointment plugin, wordpress booking, scheduler
Requires at least: 3.4
Tested up to: 3.5.1
Stable tag: 1.4.3

BirchPress Scheduler is an appointment booking calendar that allows service businesses to take online bookings.

== Description ==

BirchPress Scheduler is an appointment booking calendar that allows service businesses such as spas, yoga studios, contractors and photographers to take online bookings. Merchants can embed a booking form in a page or post on the website, and customers can see service availability and book an appointment online directly. It is an effective tool to manage appointments and staffing schedules.

= Feature: =
* Customer booking for specific time in a day
* Support multiple locations
* Support multiple staff and services
* Config service length, padding time and price
* Assign your employees to different services
* Powerful Admin Panel for booking management
* Easily embed booking form into a webpage with shortcode
* Show appointments in the daily, weekly or monthly view
* Client Management
* Multiple-currency support
* Config date and time format
* Set the first day of the week
* Supported languages: English, Dutch(by Edwin ten Brink), German (by Edwin ten Brink), Spanish (by Juan Viñas)

= Desired Businesses =
* Client scheduling (Beauty salon, Spa management, Hairdresser, Massage therapist, Acupuncture, Photographers,Personal Trainers, Wellness, Training Institutes, Sightseeing Services, Home Repair, Auto Repair, Tuition, Financial Services)
* Meeting scheduling (Coaching, Phone advice, Consulting, Law Firms, Education)
* Patient scheduling (Doctor, Clinic, Medical)


= Extra features supported in BirchPress Scheduler Pro = 
* Autofill info for existing customers in admin
* Automated Email notifications to staff and clients
* Custom email messages 
* Display staff appointments in different colors 
* Custom booking form based on business needs(add custom fields, visible/invisible to admin and customers, required/not required)
* Block busy time and holidays
* Page redirection after booking
* Returning user booking with email and password
* WP user integration
* Support minimum time requirement prior to booking
* Set how far in advance an appointment can be booked
* Set the length of time slot
* Set booking availability for a specific time period
* and much more comming soon

Please visit [our website](http://www.birchpress.com "WordPress Appointment Booking Calendar") for more info, or try our online [demo](http://www.birchpress.com/demo/ "BirchPress Scheduler Pro Demo") with all features.

== Installation ==

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Screenshots ==
1. Frontend booking form
2. Admin calendar view
3. New appointment from admin
4. Multiple locations
5. Staff info
6. Staff work schedule
7. Service info
8. Service settings
9. Client info
10. Currency and other settings

== Frequently Asked Questions ==

= How can I embed the booking form into a post/page? =

To embed the booking form into a post/page, just insert the following shortcode:
[bp-scheduler-bookingform]

== Changelog ==

= 1.4.3 =
* Update translation files
* Bug Fix: translation related bugs
* Bug Fix: services can not be assigned to staff if services are too many
* Enhancement: try to resolve 'open_basedir restriction in effect' problem

= 1.4.1 =
* Add Hungry support
* Update Spanish and Spanish Chile
* Bug Fix: Fix confliction with other plugins.

= 1.4.0.3 =
* Bug Fix: javascript errors happen when staff select box is empty
* Bug Fix: time options is empty if the end time of work schedule is 11:45pm

= 1.4.0.1 =
* remove some warnings

= 1.4.0 =
* More flexible work schedule settings
* Use select2 library to improve user experiences
* Set first day of the week
* Fix some translation bugs
* Sort services by alphabetical order
* Remove hyphens if the service price type is "don't show"
* Add Turkish lira and South Africa rand support

= 1.3.7 =
* Users with editor role can change business settings
* Fix some translation bugs

= 1.3.6 =
* IMPORTANT: remove unnessary time availability check in the admin calendar

= 1.3.5 =
* CRITICAL: Version 1.3.4 is a bad build. Please update to 1.3.5

= 1.3.4 =
* Show update notices
* Fix a display bug of showing time options in the frontend
* Validate email when saving in the client editing view
* Add waiting message when client booking

= 1.3.3.1 =
* Support date and time format settings
* Bug Fix: confirmation datetime is incorrect
* Support Glider-like themes

= 1.3.2.2 =
* change css rules to be compatible with more themes.

= 1.3.2.1 =
* change the booking form design

= 1.3.2 =
* Improve usability of the booking form with calendar view
* Blocking to select date in the past

= 1.3.1 =
* Compatible with WordPress 3.5 now
* change several css class names in the booking form to avoid conflicting with some themes.

= 1.3.0 =
* change permission level

= 1.2.1 =
* Dutch support (Thanks to Edwin ten Brink)
* Fix the admin menu disappeared bug
* Fix some other bugs

= 1.2.0 =
* BirchSchedule is now BirchPress Scheduler
* Fix a shortcode rendering bug
* Shortcode [birchschedule_bookingform] is deprecated and replaced by [bp-scheduler-bookingform]

= 1.1.1 =
* clean some notices and warnings.

= 1.1.0 =
* Multi-currency support
* Timezone support
* Add translation files to support i18n
* Fix the padding time bug
* Filter staff by locations

= 1.0.3 =
* Fix a deletion bug.

= 1.0.2 =
* Fix the bug that only five staff are shown in the staff list.

= 1.0.1 =
* Fix the bug that pages containing escape booking form shortcode render unneeded scripts.

= 1.0.0 =
* Init release.
