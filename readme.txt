
Enthusiast
-----------------------------
Copyright (c) Angela Sabas
http://scripts.indisguise.org
=============================



Enthusiast is a tool for (fan)listing collective owners to easily
maintain their listing collectives and listings under that collective.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.



Collective Features
-------------------
. Setup/install individual listings within the same single interface
. Customized, multi-level categories
. Insert/edit/delete joined listings, has fields for
   . Subject
   . URL
   . Multiple categories
   . Description
   . Comments (admin-viewable only)
   . Image
   . Status (Pending membership/Approved membership)
. Insert/edit/delete owned listings, has fields for
   . Title
   . Subject
   . Multiple categories
   . Description
   . Image
   . Status (Pending/Upcoming/Current)
. Collective statistics
. Searching for joined/owned listings in admin interface
. Customizable joined/owned list templates
. Integrated affiliates system, has fields for
   . URL
   . Title
   . Affiliate email
   . Image
. Email all or individual affiliates
. Optional affiliate emailing upon addition to collective
. Unlimited email templates for collective-wide (and listings) use
. Settings stored in database and updatable in admin interface
. Database error logging
. Neglected listings notification


Listing Features
----------------
. Includes The Fanlistings Network required fields Name, Email, and Country,
  and additional fields such as:
   . Password (MD5-encrypted in database, optional when joining)
   . Website URL
   . Show/Hide Email
   . Show/Hide Website URL (admin-editable only)
   . Comments (not stored in database)
   . Multiple Customizable Fields (toggable on/off, as many as needed)
. Single admin interface for all listings
. Change listing type (fan-, hate-, clique, etc) in the admin interface easily
. Turn off country field if needed
. Optional integrated affiliates system, of the same specifics as the
  collective affiliates
. Sort members list in any way and in multiple levels using the additional
  customized fields (default by country)
. Customize emails sent out from fanlisting via templates and special variables
  editable in the admin interface
. Put member updates on hold (will place members who update their information
  back to 'pending' status)
. Turn off auto-emailing of owner upon member addition or member update
. Fully customizable members list, affiliates list, and listing statistics
. Collective email templates can be used for listings as well
. Single-click membership approval (single or multiple)
. Member search in admin system
. Emailing of members upon joining (optional), upon approval, and upon
  their changing of information (automated)
. Ability to mass email/email members individually via the admin system
. Owner-only capability of "hiding" website urls of members but retaining
  the URL recorded in the database
. Supports resetting of passwords in case a member forgets his/her password
. Auto-capitalization of names
. Checks if a valid email address is used to join
. Automatically appends 'http://' to the front of a website URL if it is not
  present
. Spam-protected member emails if shown on member list
. XHTML 1.0 Transitional-friendly



Requirements
------------
PHP 5.2.14 or higher
MySQL 4.0.15
* These are what I used to develop the script; if you have tested it with
lower versions of PHP and MySQL and it works, please let me know. :)



Installation and configuration
------------------------------
See http://scripts.indisguise.org/enthusiast/documentation/

IMPORTANT! Please read the installation instructions given in its ENTIRETY.
A copy of install.txt has been included with this archive, but you may
wish to view the online documentation instead.



Troubleshooting and Help
------------------------
For troubleshooting and help, please browse/ask the Enthusiast Support Forum
at http://www.codegrrl.com/forums/index.php?showforum=26 -- please DO NOT
email me for troubleshooting help on ANY email address or ANY other contact
form. I will NOT answer support requests emailed to my email addresses --
no exceptions.