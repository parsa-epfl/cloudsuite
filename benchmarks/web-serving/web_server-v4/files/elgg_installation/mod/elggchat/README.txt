Elggchat for Elgg 1.9
Latest Version: 1.9.2
Released: 2014-09-12
Contact: iionly@gmx.de
License: GNU General Public License version 2
Copyright: (c) iionly (for Elgg 1.8 and newer), ColdTrick IT Solutions


This is an updated, bug-fixed and slightly improved version of the Elggchat plugin originally by Coldtrick IT Solutions (http://community.elgg.org/plugins/384910/0.4.5/elggchat). This version of Elggchat is intended for Elgg 1.9.

The Elggchat plugin provides a chat/instant messaging feature based completely on the Elgg platform. Start chatting from the profile icon of community site member, or by selecting a friend from the friendpicker on the chat toolbar. Sessions will be shown on the chat toolbar.

Features:
- Privately chat with other members in the community,
- User option: only your friends, everyone or nobody is able to invite you to chat,
- Special chat toolbar (collapsable),
- Online/offline indication,
- Multiple members / friends in one chatsession,
- Multiple sessions at the same time,
- Smilies,
- Session cleanup by cron,
- Session management from admin backend (listing open chat session, deleting session, posting admin messages to a session).



What "Only my friends can contact me" means within the Elggchat plugin

By default the friend relationship of Elgg is one-directional, i.e. if you add another member as friend the other members does not make you automatically a friends, too. Now you might not want to chat with everyone who made you a friend without you being able to intervene. For privacy reasons the option "Only my friends can contact me" of the user setting "Allow the following to contact me by chat" means that only these members who you made a friend can contact you. From the other way round you might not be able to invite a member for chatting you made a friends because this member did not make you a friend, too.

To avoid the confusion due to the Elgg default one-directional friending (better called "following") I would suggest using the Friend request plugin (http://community.elgg.org/plugins/384965/3.3/friend-request). Using the Friend request plugin will make friending a two-way relationship (a site member can be sure that another member he is a friend with made him a friend, too, and can be invited for chatting). As friending with the Friend request plugin is by permission only the privacy of the members is considered - if you don't want to be friend with someone simply decline the request and the other member.



Server load caused by the Elggchat plugin

The Elggchat plugin can be downloaded for free but offering a chat feature on your site is not for free regarding the server load. The exact load caused by the chatting is difficult to predict as it depends on the number of chat sessions going on in parallel. Still, a few general hints:

- The Elggchat plugin is very likely too much on shared servers. Don't risk getting in trouble with your webhoster. Or in other words: use the Elggchat plugin on shared servers on your own risk!
- The Elggchat plugin most likely suitable for small to medium Elgg community sites only. Depending on your server hardware / hosting plan you might be able to use it also on larger sites with a higher number of concurrent users (i.e. higher number of concurrent chat sessions). I would suggest to monitor the server load closely after installing the Elggchat plugin to make sure that it's not causing too much load. I'm afraid there's not much you can do, if the load is getting too high apart from looking for another solution for offering a chat feature on your site.
- Consider using the No logging plugin (http://community.elgg.org/plugins/1441338/1.8.0/elgg-1819-no-logging). The chat messages are saved as Elgg annotations each. By default Elgg creates for each annotations log entries in its Elgg log. The creation of all the log entries for the chat messages can create some additional server load and also result in an increased size of the Elgg log table. When using the No logging plugin there won't be any log entries created anymore for the Elgg chats. But there won't be any log entries for any other user actions on your site either! So, you have to decide for yourself, if you need the Elgg log capabilities or not.



Installation and configuration:

(0. If you have a previous version of the plugin installed, start with deaktivating the Elggchat plugin, then remove the elggchat plugin folder from the mod directory completely before installing the new version,)
1. Copy the elggchat plugin folder into the mod folder on your server,
2. Enable the plugin in the admin section of your site,
3. Check out the plugin settings and modify the configurations according to your liking.

Additional configuration: for the chat session cleanup to work Elgg's hourly cronjob must be set up on the server.



Changelog (iionly)

1.9.2
- Version 1.8.2 updated for Elgg 1.9.

1.8.2
- New admin option: optionally keep all chat session logs (manual deletion still possible if this option is enabled),
- CSS fixes to prevent layout issues (mainly changes of font style) by a theme plugin installed,
- Layout adjustments of chat toolbar (most noticeable: chat sessions aligned right) for better results on smaller screens (though it might still not work on all mobile devices - especially older ones).

1.9.1
- Version 1.8.1 updated for Elgg 1.9.

1.8.1
- Fix for chat toolbar and chat sessions to correctly work for users with no friends (thanks to Brett for reporting).

1.9.0
- Version 1.8.0 updated for Elgg 1.9.

1.8.0
- Initial release: updated, cleaned-up, bugfixed and slightly improved version of Elggchat to work on Elgg 1.8.

------

Version history (ColdTrick IT Solutions)

0.4.5
- added: sound on new chatsession or on new message in minimized session (only once) (admin configurable)
- added: flashing of new sessions or on new message in minimized session (admin configurable)
- added: count of total members of chatsession
- added: Dutch translation
- added: extendable view ("elggchat/extensions") that allows other plugins to add stuff to the toolbar
- added: admin option for enabling/disabling extensions (all on or all off)
- added: user setting to configure who can contact you (effects only profile icon menu)
- changed: friendspicker now differs between online and offline users
- changed: cleaned up the language files
- fixed: friendspicker not showing more than 50 friends
- fixed: Cleanup cron not running (wrong interval)
- fixed: caching of js/css causing trouble
- fixed: css conflict with 'online' plugin

0.4
- added: admin configurable online/offline detection (e.g. last action < 60 secs is active, 60 - 600 secs is idle and > 600 secs is offline)
- added: sessions will change color if new messages arrived
- added: on refresh open session will be remembered
- added: remembering of chattoolbar presentation
- added: admin option for retention of chatsession data
- added: user leaves all chat session on logoff
- added: user option for disabling the chatbar
- changed: js now in the right place (caching)
- changed: revamped the interface
- changed: chatsessions stick to bar
- changed: polling mechanisme (more efficient, reduced database queries, reduced connections to webserver)
- removed: custom jQuery (lost over 200k code :)
- removed: invited mechanisme (no difference between invites and chatmembers)
- removed: a lot of css (hopefully better browser support)

0.3
- added: online/offline detection based on last_action under 600 sec (elgg default)
- added: modify chatwindow titles dynamicly (on session refresh)
- added: re-introduction of polling interval slowdown (less activity, slower polling, reduced serverload)
- fixed: change order of sessions on toolbar
- fixed: windows stick at their position
- fixed: friendspicker not correctly displayed in IE
- fixed: a lot of css issue's

0.2
- added: chattoolbar (resides at bottom of the window)
- added: friendspicker on toolbar (click a friend to start a session)
- added: shows session info on join (only for the person who joins)
- changed: all js in a separate file
- fixed: emoticons now animate every time
- fixed: invite friends shows already invited friends
- fixed: invite friends shows max of 10 friends

0.1
- first release to the public
