Crowdmap to HipChat
===================
This script pulls posts from the [Crowdmap API](https://developers.crowdmap.com) and sends a notification to a HipChat room with those post messages.

Requirements & Installation
===========================
This is a PHP application, developed and tested on version 5.4.30.

This application uses [Hipchat v2 Api Client](https://github.com/gorkalaucirica/HipchatAPIv2Client), installed via composer.

Open config.php and set your public and private keys for your Crowdmap application, along with the notification token for the room you wish to send notifications to in HipChat.

Ensure that the "timestamp" file has proper write permissions. This is where a timestamp will be saved so the API call will only find the latest posts on each successvie run.

Running
=======
This script is intended to be run on a cron, at any interval of your choosing. Keep in mind the API call to Crowdmap will only pull the last 100 posts.