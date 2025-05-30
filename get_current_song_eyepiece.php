<?php

// $music_server is imported from plugin.musicserver.php
function re_getCurrentSong () {
	global $aseco, $music_server, $re_config, $re_cache;

	// Get current song and strip server path
	$aseco->client->query('GetForcedMusic');
	$current = $aseco->client->getResponse();

	// YTDL START
	global $ytdl_song, $ytdl_title, $ytdl_artist;
	if (isset($ytdl_song) && $ytdl_song == true)
	{
		if (!empty($ytdl_title))
		{
			$re_config['CurrentMusicInfos']['Title'] = $ytdl_title;
		}
		else
		{
			$re_config['CurrentMusicInfos']['Title'] = "Downloaded music";
		}
		
		if (!empty($ytdl_artist))
		{
			$re_config['CurrentMusicInfos']['Artist'] = $ytdl_artist;
		}
		else
		{
			$re_config['CurrentMusicInfos']['Artist'] = "YTDL";
		}
		
		$ytdl_song = false;
		return;
	}
	// YTDL END

	if ( ($current['Url'] != '') || ($current['File'] != '') ) {
		$songname = str_replace(strtolower($music_server->server), '', ($current['Url'] != '' ? strtolower($current['Url']) : strtolower($current['File'])));
		for ($i = 0; $i < count($re_cache['MusicServerPlaylist']); $i ++) {
			if (strtolower($re_cache['MusicServerPlaylist'][$i]['File']) == strtolower($songname)) {
				$re_config['CurrentMusicInfos'] = array(
					'Artist'	=> $re_cache['MusicServerPlaylist'][$i]['Artist'],
					'Title'		=> $re_cache['MusicServerPlaylist'][$i]['Title']
				);
				return;
			}
		}
	}

	$re_config['CurrentMusicInfos'] = array(
		'Artist'	=> 'nadeo',
		'Title'		=> 'In-game music',
	);
}

?>