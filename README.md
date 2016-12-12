# Drupal Console Launcher 

DrupalConsole global executable.

## Installing Drupal Console Launcher
```
# Run this in your terminal to get the latest Console version:
curl https://drupalconsole.com/installer -L -o drupal.phar

# Or if you don't have curl:
php -r "readfile('https://drupalconsole.com/installer');" > drupal.phar

# Accessing from anywhere on your system:
mv drupal.phar /usr/local/bin/drupal

# Apply executable permissions on the downloaded file:
chmod +x /usr/local/bin/drupal

# Copy configuration files.
drupal init --override

# Check and validate system requirements
drupal check
```

## Enabling Autocomplete
```
# You can enable autocomplete by executing
drupal init

# Bash: Bash support depends on the http://bash-completion.alioth.debian.org/
# project which can be installed with your package manager of choice. Then add
# this line to your shell configuration file.
source "$HOME/.console/console.rc" 2>/dev/null

# Zsh: Add this line to your shell configuration file.
source "$HOME/.console/console.rc" 2>/dev/null

# Fish: Create a symbolic link
ln -s ~/.console/drupal.fish ~/.config/fish/completions/drupal.fish
```
