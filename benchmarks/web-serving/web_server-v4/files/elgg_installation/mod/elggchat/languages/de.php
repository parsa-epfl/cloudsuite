<?php
/**
 * ElggChat - Pure Elgg-based chat/IM
 *
 * German language file
 *
 * @package elggchat
 * @author ColdTrick IT Solutions
 * @copyright Coldtrick IT Solutions 2009-2014
 * @link http://www.coldtrick.com/
 *
 * for Elgg 1.8 and newer by iionly (iionly@gmx.de)
 * @copyright iionly 2014
 * @link https://github.com/iionly
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

return array(
	'admin:administer_utilities:elggchat' => 'ElggChat',
	'elggchat' => "ElggChat",
	'elggchat:title' => "ElggChat",
	'elggchat:chat:profile:invite' => "Zum Chatten einladen",
	'elggchat:chat:send' => "Senden",

	'elggchat:friendspicker:info' => "Freunde online",
	'elggchat:friendspicker:online' => "Online",
	'elggchat:friendspicker:offline' => "Offline",
	'elggchat:friendspicker:nofriends' => "derzeit keine",

	'elggchat:chat:invite' => "Einladen",
	'elggchat:chat:leave' => "Verlassen",
	'elggchat:chat:leave:confirm' => "Bist Du sicher, dass Du diese Chat-Session verlassen willst?",

	'elggchat:action:invite' => "<b>%s</b> hat <b>%s</b> eingeladen",
	'elggchat:action:leave' => "<b>%s</b> hat die Chat-Session verlassen",
	'elggchat:action:join' => "<b>%s</b> ist zur Chat-Session dazu gekommen",

	'elggchat:session:name:default' => "Chat-Session (%s)",
	'elggchat:session:onlinestatus' => "Letzte Aktion: %s",

	'elggchat:session_list_introduction' => 'Auf dieser Seite siehst Du eine Liste aller derzeit auf Deiner Community-Seite laufenden Chat-Sessions. Du kannst Dir von einer bestimmten Chat-Session mehr Details anzeigen lassen, wenn Du auf den \'Details\'-Knopf klickst, oder Du kannst eine Chat-Session durch einen Klick auf den \'Session löschen\'-Knopf löschen.',
	'elggchat:session_details_introduction' => 'Auf dieser Seite siehst Du die Details der Session, die Du aus der Liste der laufenden Chat-Sessions ausgewählt hast. Du kannst die Mitglieder sehen, die derzeit in dieser Session eingeloggt sind und die Nachrichten, die in der Session gepostet wurden. Du kannst auch eine Nachricht zu dieser Chat-Session hinzufügen. Die Nachricht wird dann als Systemnachricht mit dem Präfix \'[Nachricht vom Admin]\' in der Chat-Session erscheinen.',
	'elggchat:admin_message' => '[Nachricht vom Admin]: ',
	'elggchat:post_admin_message' => 'Nachricht zu dieser Chat-Session hinzufügen: ',
	'elggchat:post_admin_message_success' => 'Die Nachricht wurde in der Chat-Session geposted.',
	'elggchat:post_admin_message_error' => 'Beim Posten der Nachricht in der Chat-Session ist ein Fehler aufgetreten.',
	'elggchat:chatsession_delete' => 'Session löschen',
	'elggchat:session_delete_success' => 'Die Chat-Session wurde gelöscht.',
	'elggchat:session_delete_error' => 'Beim Löschen der Chat-Session ist ein Fehler aufgetreten.',
	'elggchat:sessions_backbutton' => 'Zurück zur Liste der Chat-Sessions',
	'elggchat:session:no_session_details' => 'Die Chat-Session existiert entweder nicht oder es ist beim Abrufen der Session-Details ein Fehler aufgetreten.',
	'elggchat:session:no_sessions' => "Es gibt derzeit keine laufenden Chat-Sessions.",
	'elggchat:chatsession_deleteconfirm' => 'Möchtest Du diese Chat-Session wirklich sofort und ohne Vorwarnung für die Mitglieder in diesem Chat löschen?',
	'elggchat:chatsession_details' => 'Details',

	'elggchat:session:guid' => "Chat-Session GUID: %s",
	'elggchat:session_details:guid' => "Details der Chat-Session mit GUID %s",
	'elggchat:session:last_updated' => "Letztes Update: ",
	'elggchat:session:chat_participants' => "Mitglieder in dieser Chat-Session: ",
	'elggchat:session:no_participants' => "Derzeit nehmen keine Mitglieder in dieser Chat-Session teil.",
	'elggchat:session:number_chat_participants' => "Anzahl der Chat-Teilnehmer: ",
	'elggchat:session:session_messages' => "Nachrichten, die in dieser Chat-Session geposted wurden: ",
	'elggchat:session:no_messages' => "In dieser Chat-Session wurden noch keine Nachrichten gepostet.",
	'elggchat:session:number_session_messages' => "Anzahl der Chat-Nachrichten: ",

	'elggchat:crondone' => "Chat-Sessions gesäubert\n",

	// Plugin settings
	'elggchat:admin:settings:hour' => "%s Stunde",
	'elggchat:admin:settings:hours' => "%s Stunden",
	'elggchat:admin:settings:days' => "%s Tage",

	'elggchat:admin:settings:maxsessionage' => "Max. Zeit, die eine Session im Leerlauf sein darf bevor sie automatisch geschlossen und gelöscht wird",
	'elggchat:admin:settings:keepsessions' => "Behalte alle Sessions und lösche sie nicht automatisch (Admins können sie weiterhin manuell löschen)",

	'elggchat:admin:settings:chatupdateinterval' => "Aktualisierungsintervall (in Sekunden) des Chat-Fensters",
	'elggchat:admin:settings:maxchatupdateinterval' => "Nach 10 Aktualisierungsintervallen ohne neue Daten (\"idle\"), wird das Aktualisierungsintervall verdoppelt bis dieses Maximum (in Sekunden) erreicht ist",
	'elggchat:admin:settings:enable_sounds' => "Soundausgabe bei neuen Nachrichten aktivieren (nur wenn Chat-Fenster minimiert ist)",
	'elggchat:admin:settings:enable_flashing' => "Blinken bei neuen Nachrichten aktivieren (nur wenn Chat-Fenster minimiert ist)",
	'elggchat:admin:settings:enable_extensions' => "Erweiterungen aktivieren (das ElggChat-Plugin selbst enthält keine Erweiterungen. Wenn diese Option aktiviert ist, ist es aber anderen Plugins möglich, durch Erweiterung der View \"elggchat/extensions\" eigene Funktionalität zur ElggChat-Leiste hinzuzufügen)",

	'elggchat:admin:settings:online_status:active' => "Max. Zeit (in Sekunden), bis ein Chat-Teilnehmer als pausierend (\"idle\") betrachtet wird",
	'elggchat:admin:settings:online_status:inactive' => "Max. Zeit (in Sekunden), bis ein Chat-Teilnehmer als inaktiv betrachtet wird",

	// User settings
	'elggchat:usersettings:enable_chat' => "ElggChat-Toolbar aktivieren?",
	'elggchat:usersettings:allow_contact_from' => "Folgende Mitglieder dürfen mich über ElggChat kontaktieren",
	'elggchat:usersettings:allow_contact_from:all' => "Alle können mich kontaktieren",
	'elggchat:usersettings:allow_contact_from:friends' => "Nur Freunde dürfen mich kontaktieren",
	'elggchat:usersettings:allow_contact_from:none' => "Niemand darf mich kontaktieren",
	'elggchat:usersettings:show_offline_user' => "Mitglieder, die offline sind, anzeigen",

	// Toolbar actions
	'elggchat:toolbar:minimize' => "ElggChat-Toolbar minimieren",
	'elggchat:toolbar:maximize' => "ElggChat-Toolbar maximieren"
);