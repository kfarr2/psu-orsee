# Utils

Files with custom functionality

#### connections.php

Code that pulls settings from the config.ini file
and specifies global variables relating to database settings.

#### general.php

General purpose functions used frequently throughout the site are placed here. E.g. `cas_authenticate()`

`cas_authenticate()` implements the following algorithm:

- Redirect user to PSU CAS login page.
- Get a ticket back from PSU CAS if login credentials were valid.
- Pass the ticket back to PSU CAS for validation.
- Get user info back from PSU CAS on redirect.
- Use info from CAS to check database for existing user.
- If user doesn't exist, add the user to the database.
- Set the cookie to show the user is logged in.
- Return the row from the database with user info.
