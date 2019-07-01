# Description
The goal of this project is get an **instant email notification service** monitoring a folder for specific files.
When a file is added, the scripts

- makes a call to Themoviedb.com to retrieve infos for desided local language for the new file added (poster, genres, date of release, plot, user's rating average). If plot is not localized in desired language, it will be translated using Google Translate
- makes a call to Youtube.com to retrieve a public trailer link and 
- generate an email from a template and send to recipients defined


For example, you want to notify your friends about your new matrioska files added on your WD My cloud nas.

Remember: *you do it at your own risk.*

# In depth 

As we know, this nas is based on busybox (so it's not opened to modifications).

So, first we need to install the Debian chroot environment app (search online how to do it, credits goes to Fox_Exe for this app).

Once we have installed the alternative environment, we can use it to install packages and make custom scheduled entries that will "live" even after shutting down/rebooting the nas.

# Technical infos

The principle behind all is use the file watcher given with the `inotify-tool` package.

So, opening an ssh session with the nas:

```language
    chroot /mnt/HD/HD_a2/Nas_Prog/Debian/chroot /bin/bash
```

Now we are on our Debian environment. 

Let's install the watcher (and related dependencies):
```language
    apt-get update
    apt install inotify-tools
```

For send emails, we can use `mutt`.

So again from our ssh session:
```language
    apt install mutt
```
In this project, i use a Gmail address to send emails with 2fa (so i have generated a password from the Google dashboard).

The configuration is stored in the *.muttrc* file.

For the scripts:
```language
    apt install php5-cli php5-gd php5-curl php5-curl php5-json
```

Now we need to register a new entry service in the /etc/init.d folder using the inotify.sh file (search online how to do it). 
Edit this file to define which folder will be monitored and the recipients that will receives the emails notification.

This custom service will activate on every boot a pipe sequence that:
1. watch a folder specified (recursively) in the .sh file with .mkv extension (case insensitive)
2. pass the filename to the scripts to be processed
3. send emails to recipients 


## Extras & credits
----------
The scripts uses:
- Google Drive Rest API
- Google Youtube API
- [PHP GoogleTranslate library](https://github.com/statickidz/php-google-translate-free)
- [mutt](http://www.mutt.org/)
- [inotifywait](https://linux.die.net/man/1/inotifywait)

You have to download them separately

That's me with the bad guy :D

![me_wd](http://esempivari.altervista.org/me_wd.jpg)

