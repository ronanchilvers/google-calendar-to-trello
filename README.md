# google-calendar-to-trello

This little script allows you to sync a set of events in google calendar to a Trello list as cards. It preserves the title, description, datetime and attendees for the event.

## Features

- Sync events for a given day from a single google calendar to a specified Trello list
- Set one or more arbitrary labels on the cards
- Choose a different sync date, event card list position (top or bottom) and a different list

## Usage

- Copy the config.yaml.dist file to config.yaml
- Generate a Trello API secret and token by visiting [https://trello.com/1/appKey/generate]. Grab the Trello API key and OAuth v1 token (at the bottom of the page) and enter them in your config
- Find the list id you'd like to use. You can use the `query` command for this:
```bash
php bin/google-calendar-to-trello query
```
- Edit your config to add the list id you want to use
- Run the script for the first time to check it works. It will ask you to authenticate to Google by pasting a URL into your browser. In return you'll get a key which you should paste into the prompt in your terminal.
```bash
php bin/google-calendar-to-trello sync
```
- You should now see some calendar events pop in as trello cards

## Known issues

- Currently the google token expires and isn't correctly refreshed. If this happens just delete the `google.token.json` file and redo the google authentication
- The script doesn't remember events its already synced. If you run it twice for the same day you'll get two sets of cards. This is being worked on.
