# PHP Developer assignment

## Task

Your task is to create a PHP application that is a feeds reader. The app can read feed from multiple feeds and store them to database. Sample feeds http://www.feedforall.com/sample-feeds.htm.

## Requirements
- The application must be developed by using a php framework and follow coding standard of that framework.
- As a developer, I want to run a command which help me to setup database easily with one run.
- As a developer, I want to run a command which accepts the feed urls (separated by comma) as argument to grab items from given urls. Duplicate items are accepted.
- As a developer, I want to see output of the command not only in shell but also in pre-defined log file. The log file should be defined as a parameter of the application.
- As a user, I want to see the list of items which were grabbed by running the command line. I also should see the pagination if there are more than one page. The page size is up to you.
- As a user, I want to filter items by category name on list of items.
- As a user, I want to create new item manually
- As a user, I want to update/delete an item

## How to do
1. Fork this repository
2. Start coding
3. Use gitflow to manage branches on your repository
4. Open a pull request to this repository after done

# Feed Reader Application
A PHP web application can read multiple rss feeds and store them to database. Moreover, it allows user manipulate both feeds and their items. The project include two type of communication, console and web application.

Sample feeds http://www.feedforall.com/sample-feeds.htm. 

Some technicals applied to this project:
- CSRF Token
- Validation
- Pagination
- Bootstrap
- Packages management
- Code standard PSR1, PSR2

REQUIREMENTS
------------
- Apache 2.2 or later
- PHP 5.4.0 or later
- MySQL 5.5 or later
- Composer 1.1.2 or later

CONFIGURATION
-------------

**Comment** these below lines in `web/app_dev.php`
### Environment
    if (isset($_SERVER['HTTP_CLIENT_IP'])
        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1']) || php_sapi_name() === 'cli-server')
    ) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
    }

Modify file `app/config/parameters.yml` with your environment data.
### Database

    parameters:
        database_host: mysql
        database_port: null
        database_name: feeds_reader_symfony
        database_user: root
        database_password: root

Modify file `app/config/config.yml` with your log file.
### Log file path

    monolog:
        handlers:
            feeds-reader-info:
                type: stream
                # log to var/logs/(environment).log
                path: "%kernel.logs_dir%/info.feed-reader.log"
                # log *all* messages (debug is lowest level)
                level: 'info'
    
            feeds-reader-error:
                type: stream
                # log to var/logs/(environment).log
                path: "%kernel.logs_dir%/error.feed-reader.log"
                # log *all* messages (debug is lowest level)
                level: 'error'

INSTALLATION
------------
1. You only need to run install file follow command in project root directory

        $./install

3. The project is installed successful if you can see this message.

        END INSTALL DATABASE FOR FEEDS READER APPLICATION

USAGE
-----
### Use in command line

You can add multiple feeds by run the command in root directory of project.

    $php bin/console feeds-reader:add "url_1, url_2"

You can monitor any console message response in log file. Ex.

    ${project.basedir}/var/logs/info.feed-reader.log
    ${project.basedir}/var/logs/error.feed-reader.log
    ${project.basedir}/var/logs/dev.log
    
### Use in dashboard

You can access website by route

    http://localhost/feedreader/app_dev.php/

In addtion, you also can:

    - Subscribe multiple feeds
    - Unsubscribe a feed
    - View a list of feeds with 10 items per page.
    - Mannually create a feed item
    - Update a feed item
    - Delete a feed item
    - Filter feeds by category

CREDIT
------
The project is powered by **Symfony frameworks v3.4**.

    https://symfony.com/

AUTHOR
------
Phong Le phong.lenguyen89@gmail.com

