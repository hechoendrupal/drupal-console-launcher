# Drupal Console Launcher 

DrupalConsole global executable aka Launcher.

## Why do I need the Launcher?
This is a global executable that enables you to run the command, `drupal`, from any directory within your site's project.
Without it you will be inconvenienced by having to run the command only from your drupal root directory.

For example, if you have Drupal root in the `web` or `docroot` directory, and a composer.json and your vendor directory in the directory above that, you will be able to run the `drupal` command from the same directory as the composer.json file. Even better, you can run it from any subdirectory under that as many levels deep as you would like to go.

## Installing Drupal Console Launcher
```
curl https://drupalconsole.com/installer -L -o drupal.phar
mv drupal.phar /usr/local/bin/drupal
chmod +x /usr/local/bin/drupal
```
