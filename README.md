# Trello Export

Export EVERYTHING from Trello to local JSON files

## Installation

Just clone this repo someplace clever

    git clone ${REPO} /opt/edoceo/trello-export

## Execution

You'll need to set two environment vars for this to work, your Trello API keys

Visit https://trello.com/app-key and then generate a token.
Then export these values into TRELLO_KEY and TRELLO_TOKEN environment variables.
Then run `./export.php`.

## Exported Data

This will create a local directory `./trello-export-data` which will contain JSON files of all your stuff.
All Boards, Lists, Cards and Attachments are exported.
The data is exported as JSON, except for the attachments which are exported as their native data.

The directories are named after their Trello objects.
The attachments are stored under their cards, in a directory with the same name as the card.
There are two files per attachment: a JSON file describing the attachment and the attachment itself.

We've observed that the MIME types for attachments in Trello is not alwasy accurate and in those cases we try to guess.
It doesn't always work.
