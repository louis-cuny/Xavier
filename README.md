# Teaching language to Xavier - a vocal robot with a website !...





## Installation

### Clone our project using git and modify the .env file to your needs

### Create the project using Composer
``` bash
$ composer install
```


### Download client-side libraries
``` bash
$ npm install
```
This will install Gulp dependencies and Semantic UI in `public/assets/lib/semantic/`.

### Gulp
This skeleton uses Gulp to manage assets. The CSS and Javascript files are located in `assets/`, so you have to use Gulp after creating your project to generate the minified files in `public/`, which will be ignored by Git.

#### Install Gulp
You can install Gulp globally on your system with the following command if you haven't done it yet
``` bash
$ npm install -g gulp-cli
```

#### Generate assets
If you just want to generate the default CSS and JS that comes with this skeleton, run the following command
``` bash
$ gulp build
```

If you want to run a watcher and begin coding, just run
``` bash
$ gulp
```

### Setup cache files permissions
The skeleton uses a cache system for Twig templates and the Monolog library for logging, so you have to make sure that PHP has write permissions on the `var/cache/` and `var/log/` directories.

### Update your database schema
First, create a database with the name you set in the `.env` (usually Xavier) file. Then you can create the tables by running this command:
``` bash
$ php bin/console db
```

You can contact us here if you have troubles with our project !
Luc, Allan & Louis
