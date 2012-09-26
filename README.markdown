Kanban Board for Codebase. 
======

This is a small project to display your tickets in Codebase ( www.codebasehq.com ) in Kanban style.
![Screenshot](https://img.skitch.com/20110421-nyyty951c1qr7ttqj2623wujdy.png)

Installation
------------

After checking out the code repsitory, you need to copy the settings.example.php to settings.php and edit that file with the appropriate config data. You currently need to specify 3 configuration options:

* /$codebaseAccount/ This is the Codebase account you use. Not your username, by the way.
* /$codebaseUser/ This is the username, used for authentication with the Codebase API. The user must have adminitration privileges, otherwise the kanban board will not work.
* /$codebaseMainProject/ The simple name of the project for which you want to show the kanban board

There is a sqlite database (user.sqlite) that has the user/password/api_key. 
The default user/pass is admin/admin, with no api_key.

Note :: The user.sqlite.example should be renamed to user.sqlite. I did
this because I don't want to upload my user.sqlite file with my users.

To create a new user you have to access the db. There you have to insert
your username, your codebase.api_key and a password-hash.

To create a password-hash for your new user, you have to go to http://localhost/generate_password.php?password=<your.password.here> 
That page will generate a hash-password for you that you have to insert into the db.

Kanban is just plain PHP and Javascript. Just upload the files to your server and you're done!

Usage
-----

You can control the columns through Codebase. For every possible status a column will be created. You can control the ordering of the columns also via Codebase. If a status is treated as if the ticket was closed, then the column will use the full width of the page, otherwise the columns will have a fixed width.

Currently, the kanban board will use the first active milestone it can find.

Contact
-------

If you want to know more, have questions etc. please contact me at birgir@transmit.is

Project Status
-------
I am working in my spare time on a fork of this project from springest so it isn't entirely unmaintained. (at least not my fork).
