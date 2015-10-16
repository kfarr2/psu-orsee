# ORSEE

ORSEE: Online Recruitment System for Economic Experiments

This project contains the custom ORSEE build ARC has set up for a client.

## Install

Download the project into its own directory
Copy the config file and edit the connection settings.

    cp config.ini.example config.ini
    vim config.ini

## Changes

In order to run this site using PSU authentication, some changes had to be made.
They are listed here, and detailed in their respective directories.

- Changes in the way authentication is done are in implemented in `public/`
- Utility functions were added in `utils/`
