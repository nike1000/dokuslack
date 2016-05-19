# DokuSlack

Plugin that allows to send notifications to Slack when an item is modified in DokuWiki by using Slack Webhooks.


Setup
-----

1. Create an INCOMING WEBHOOK on Slack. and under Configurations in DokuWiki paste the webhook URL in field for DokuSlack.

2. Clone repository into your DokuWiku plugins folder, making the target folder name 'dokuslack'

```
$ git clone https://github.com/cristiammercado/dokuslack.git dokuslack
```

3. Or download a zip file from: https://github.com/cristiammercado/dokuslack/archive/master.zip and upload via FTP to /lib/plugins/dokuslack.

4. In your DokuWiki Configuration Settings, paste the webhook URL given by Slack in the correct input, enter channel name, the name you want the notifications to appear from in Slack, border color of messages and thumb image URL if you want.

6. When you save a page, the information about editor and article is sent to configured channel.


Requirements
------------

* DokuWiki