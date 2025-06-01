# TMF YTDL (youtube-dl/yt-dlp)
A TrackMania (Nations/United) Forever server (XASECO) plugin which allows players to search for YouTube uploads (`/youtube <query>`), as well as submit direct links of various media (`/ytdl <url>`), to be downloaded by the server and used as music.

This plugin assumes you have runnable versions of [yt-dlp](https://github.com/yt-dlp/yt-dlp) (as well as [ffmpeg](https://ffmpeg.org/)) added to your `PATH`.

This plugin is interoperable with the Music Server plugin by Xymph (`plugin.musicserver.php`), but there are a few drawbacks (most notably, YTDL "jukeboxes"/requests always have priority over the Music Server's jukebox). This plugin is also interoperable with Records Eyepiece by Undef (`plugin.records_eyepiece.php`), but in order for song names to show up properly, modifying the `re_getCurrentSong` function is required. See `get_current_song_eyepiece.php` for more info.

> [!NOTE]
> This repository is currently an **early work-in-progress**, any and all information is subject to change without notice and may be missing or incorrect.
>
> Additionally, the plugin provided in this repository is merely a prototype, as many options have been hardcoded and functionality is lacking, and does not adequately represent the vision of the final product.
