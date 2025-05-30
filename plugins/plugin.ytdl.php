<?php

Aseco::registerEvent('onSync', 'ytdl_onSync');

Aseco::addChatCommand('ytdl', 'Add server music from direct link');
Aseco::addChatCommand('youtube', 'Add server music from YouTube search');
Aseco::addChatCommand('ytdl_drop', 'Drop the current YTDL song');

require_once('includes/ogg_comments.inc.php');

$ytdl_musicserver = false;
$ytdl_song = false;
$ytdl_title = "";
$ytdl_artist = "";

function ytdl_onSync($aseco)
{
	global $ytdl_musicserver;
	
	foreach ($aseco->plugins as $plugin)
	{
		if ($plugin == "plugin.musicserver.php")
		{
			$ytdl_musicserver = true;
			break;
		}
	}
}

function ytdl($aseco, $client, $user_arg)
{
	global $ytdl_song, $ytdl_title, $ytdl_artist, $ytdl_musicserver, $music_server;
	
	$logtitle = 'Player';
	$chattitle = 'Player';
	$perms = false;
	
	if ($aseco->isMasterAdmin($client))
	{
		$logtitle = 'MasterAdmin';
		$chattitle = $aseco->titles['MASTERADMIN'][0];
		$perms = true;
	} 
	
	else if ($aseco->isAdmin($client) && $aseco->allowAdminAbility($command['params'][0]))
	{
		$logtitle = 'Admin';
		$chattitle = $aseco->titles['ADMIN'][0];
		$perms = true;
	}
	
	else if ($aseco->isOperator($client) && $aseco->allowOpAbility($command['params'][0]))
	{
		$logtitle = 'Operator';
		$chattitle = $aseco->titles['OPERATOR'][0];
		$perms = true;
	}
	
	if (!$perms)
	{
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}> {#error}This command is for Operators only!'), $client->login);
		return;
	}
	
	if ($ytdl_song)
	{
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}> {#error}Another YTDL song is already queued. {#highlite}Drop it or wait until next map start.'), $client->login);
		return;
	}
	
	$arg = escapeshellarg($user_arg);
	$path = $aseco->server->gamedir;
	
	$message = formatText('{#server}> {#emotic}Attempting to download: {#highlite}{1}', $arg);
	$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $client->login);
	
	$aseco->client->query('ManualFlowControlEnable', true);
	
	$id = shell_exec('yt-dlp '.$arg.' -x --audio-format vorbis -I 1 --no-mtime --no-simulate --embed-metadata --max-filesize 10M --print id -o "'.$path.'Music\YTDL\%(id)s.%(ext)s"');
	
	$aseco->client->query('ManualFlowControlEnable', false);
	
	$id = trim(preg_replace('/\s\s+/', ' ', $id));
	
	$aseco->client->query('SetForcedMusic', true, "Music\YTDL\\".$id.".ogg");
	if ($aseco->client->isError())
	{
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}> {#error}Failed to download. {#highlite}Make sure the media exists and is not too long.'), $client->login);
		return;
	}
	
	if ($ytdl_musicserver)
	{
		$music_server->mannext = true;
		$aseco->client->query('SetForcedMusic', $music_server->override, $music_server->server."YTDL\\".$id.".ogg");
	}
	
	$tags = new Ogg_Comments($path."Music\YTDL\\".$id.".ogg", true);
	$title = $tags->comments['TITLE'];
	$artist = $tags->comments['ARTIST'];
	$url = preg_replace("(^https?://)", "", $tags->comments['DESCRIPTION']);

	$ytdl_song = true;
	$ytdl_title = "";
	$ytdl_artist = "";

	if (isset($title))
	{
		$ytdl_title = $title;
		
		if (isset($artist))
		{
			$ytdl_artist = $artist;
			
			if (isset($url))
			{
				$aseco->console('{1} [{2}] downloads {3} by {4} ({5})', $logtitle, $client->login, $title, $artist, $url);
	
				$message = formatText('{#server}>> {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} downloads $l[{3}]{#highlite}{4}$z$s{#admin} by {#highlite}{5}$z$s{#admin}$l.', $chattitle, $client->nickname, $url, $title, $artist);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				return;
			}
			
			$aseco->console('{1} [{2}] downloads {3} by {4}', $logtitle, $client->login, $title, $artist);
	
			$message = formatText('{#server}>> {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} downloads {#highlite}{3}$z$s{#admin} by {#highlite}{4}$z$s{#admin}.', $chattitle, $client->nickname, $title, $artist);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			return;
		}
		else if (isset($url))
		{
			$aseco->console('{1} [{2}] downloads {3} ({4})', $logtitle, $client->login, $title, $url);

			$message = formatText('{#server}>> {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} downloads $l[{3}]{#highlite}{4}$z$s{#admin}$l.', $chattitle, $client->nickname, $url, $title);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			return;
		}
		
		$aseco->console('{1} [{2}] downloads {3}', $logtitle, $client->login, $title);

		$message = formatText('{#server}>> {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} downloads {#highlite}{3}$z$s{#admin}.', $chattitle, $client->nickname, $title);
		$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		return;
	}
	
	$aseco->console('{1} [{2}] downloads {3}', $logtitle, $client->login, $id);

	$message = formatText('{#server}>> {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} downloads {#highlite}{3}$z$s{#admin}.', $chattitle, $client->nickname, $id);
	$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
}

function chat_ytdl($aseco, $command)
{
	$client = $command['author'];
	
	// no arguments - just return currently set CRT time limit
	if ($command['params'] == '')
	{
		$message = formatText('{#server}>> {#error}Empty.', $crt_timelimit);
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $client->login);
		return;
	}
	
	ytdl($aseco, $client, $command['params']);
}

function chat_youtube($aseco, $command)
{
	$client = $command['author'];
	
	// no arguments - just return currently set CRT time limit
	if ($command['params'] == '')
	{
		$message = formatText('{#server}>> {#error}Empty.', $crt_timelimit);
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $client->login);
		return;
	}
	
	ytdl($aseco, $client, "ytsearch:".$command['params']);
}

function chat_ytdl_drop($aseco, $command)
{
	$client = $command['author'];
	
	$logtitle = 'Player';
	$chattitle = 'Player';
	$perms = false;
	
	if ($aseco->isMasterAdmin($client))
	{
		$logtitle = 'MasterAdmin';
		$chattitle = $aseco->titles['MASTERADMIN'][0];
		$perms = true;
	} 
	
	else if ($aseco->isAdmin($client) && $aseco->allowAdminAbility($command['params'][0]))
	{
		$logtitle = 'Admin';
		$chattitle = $aseco->titles['ADMIN'][0];
		$perms = true;
	}
	
	else if ($aseco->isOperator($client) && $aseco->allowOpAbility($command['params'][0]))
	{
		$logtitle = 'Operator';
		$chattitle = $aseco->titles['OPERATOR'][0];
	}
	
	if (!$perms)
	{
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}> {#error}This command is for Admins only!'), $client->login);
		return;
	}
	
	global $ytdl_song, $ytdl_musicserver, $music_server;
	if (!$ytdl_song)
	{
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}> {#error}No YTDL song queued.'), $client->login);
		return;
	}
	
	$ytdl_song = false;
	if ($ytdl_musicserver)
	{
		$music_server->mannext = false;
	}
	
	$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}> {#admin}YTDL song dropped.'), $client->login);
}

?>