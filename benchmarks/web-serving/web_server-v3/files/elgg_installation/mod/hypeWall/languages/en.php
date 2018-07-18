<?php

$english = array(

	'item:object:hjwall' => 'Wall posts',
	
	'wall' => 'Wall',

	'wall:settings:model' => 'Model',
	'wall:settings:model:select' => 'Select the model for the wall posts',
	'wall:settings:model:wall' => 'Use default wall functionality',
	'wall:settings:model:wire' => 'Use wire',
	'wall:settings:model:character_limit' => 'Limit the number of characters in a status message to (0 for no limit)',

	'wall:settings:form' => 'Wall forms',
	'wall:settings:default_form' => 'Default form',
	'wall:settings:features' => 'Wall post features',
	'wall:settings:status' => 'Update status',
	'wall:settings:url' => 'Share a link',
	'wall:settings:photo' => 'Share a photo',
	'wall:settings:file' => 'Share a file',
	'wall:settings:content' => 'Share content',
	'wall:settings:geopositioning' => 'Enable geopositioning',
	'wall:settings:tag_friends' => 'Enable tagging of friends',
	'wall:settings:third_party_wall' => 'Allow users to post on walls of other users that are not friends (each user will have to opt in to receive wall posts from non friends)',

	'wall:usersettings:river_access_id' => 'Visibility of wall tags',
	'wall:usersettings:river_access_id:help' => 'Who can see that you were tagged in someone else\'s wall post, if the original post was not shared with them?',
	'wall:usersettings:third_party_wall' => 'Allow people who are not your friends to post on your wall',

	'wall:write' => 'Post on the wall',
	'wall:view' => 'View wall',

	'wall:empty' => 'This wall is empty',
	
	'wall:tag:friends' => 'Tag friends',
	'wall:tag:friends:hint' => 'Tag friends: start search by typing their name',
	'wall:tag:location:hint' => 'Add a location: search for previously tagged locations or add a new one',
	'wall:tag:location:findme' => 'Find me - Your browser might request you to allow this site to use your current location',
	'wall:tag:river' => '%s tagged %s in a %s',
	'wall:tag:river:post' => 'post',

	'wall:status:placeholder' => 'What\'s on your mind?',
	'wall:url:placeholder' => 'Add a link',

	'wall:tagged:notification:subject' => '%s tagged you in a post',
	'wall:tagged:notification:message' => '
		%s tagged you in a post: <br />
		<blockquote>
			%s
		</blockquote>
		You can view the post here:
		%s
	',

	'wall:new:notification:generic' => 'New post',
	'wall:new:notification:summary' => 'New post %s', // New post on X's wall
	'wall:new:notification:subject' => '%s posted %s',	// X posted on Y's wall
	'wall:new:notification:message' =>  '
		%s posted %s: <br />
		<blockquote>
			%s
		</blockquote>
		You can view the post here:
		%s
	',

	'wall:owner:suffix' => ' on %s\'s wall',
	'wall:byline' => ' by %s',
	'wall:with' => '- with %s',
	'wall:at' => ' near %s',
	'wall:attached:single' => '[1 attachment]',
	'wall:attached' => ' [%s attachments]',
	
	'wall:new:wall:post' => '%s posted on %s\'s wall',
	'wall:status' => 'Update status',
	'wall:url' => 'Share a link',
	'wall:content' => 'Share content',
	'wall:attachment' => 'Make an attachment',
	'wall:location' => 'Add location',
	'wall:tag_friends' => 'Tag friends',
	'wall:upload_file' => 'Select a file',
	'wall:find_me' => 'Find me',
	'wall:post' => 'Post',
	'wall:photo' => 'Share a photo',
	'wall:file' => 'Share a file',
	'wall:owner' => '%s\'s Wall',
	'wall:moreposts' => 'More posts',
	'wall:filefolder' => 'Wall Uploads',
	'wall:upload' => 'Wall File Upload',
	'wall:photo:placeholder' => 'Tell something about this photo',
	'wall:file:placeholder' => 'Tell something about this file',
	'wall:filehasntuploaded' => 'Please wait for the file to upload',


	'wall:create:success' => 'Wall post was successfully saved',
	'wall:create:error' => 'Wall post could not be created',
	'wall:process:posting' => 'Posting...',

	'wall:error:ajax' => 'Remote page is not accessible',
	'wall:error:container_permissions' => 'You you do not have sufficient permissions to post here',
	'wall:error:empty_form' => 'Please tell us what\'s on your mind or add a link first',

	'wall:delete' => 'Delete wall post',
	'wall:delete:success' => 'Wall post was successfully deleted',
	'wall:delete:error' => 'Wall post could not be deleted',
	
	'wall:remove_tag' => 'Remove tag',
	'wall:remove_tag:success' => 'You are no longer tagged in this post',
	'wall:remove_tag:error' => 'Tag could not be removed',

	'wall:post:status_update' => 'Status update %s',
	'wall:post:wall_to_wall' => 'Wall post %s',

	'wall:ecml:url' => 'Wall URL address',
	'wall:ecml:attachment' => 'Wall attachment',
	'wall:ecml:river' => 'River layout',

	'wall:upload:success' => 'File uploaded successfully',
	'wall:upload:error' => 'File could not be uploaded',

	'wall:characters_remaining' => "characters remaining",
	'wall:make_bookmark' => 'Save this link to my bookmarks',

	'wall:numbertodisplay' => 'Number of latest posts to display',

	'wall:target:thewire' => 'wire',
	'wall:target:hjwall' => 'wall',

	'wall:ownership:own' => 'on their %s',
	'wall:ownership:your' => 'on your %s',
	'wall:ownership:owner' => 'on %s\'s %s',

	'wall:widget:showaddform' => 'Show a form to add new posts',

	'wall:groups:enable' => 'Enable group wall',
	'wall:groups' => 'Group wall',
	'wall:groups:post' => 'Post',

	'wall:settings:status_input_type' => 'Type of input to display for status field',
	'wall:settings:status_input_type:text' => 'One-liner',
	'wall:settings:status_input_type:plaintext' => 'Multiline (no editor)',
	'wall:settings:status_input_type:longtext' => 'Multiline (with editor, if enabled)',
);

add_translation("en", $english);