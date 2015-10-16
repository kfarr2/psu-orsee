# Public

Files with custom functionality

#### header.php

Lines 7 - 8: Added two `require_once()` function calls that point the header to the utils files.

#### participant_create.php

Lines 208 - 215: Added case for student trying to sign up. 
Calls `cas_authenticate()` to log the user in using the PSU CAS backend.
If the user is found in the PSU database, 
they are added to the local database if needed, and sent a confirmation email.
