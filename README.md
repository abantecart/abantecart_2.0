
# AbanteCart eCommerce Platform v2.0 DEV

**Important!**
## This is a development branch for v2.0. Do not use on live sites!!

## Overview

AbanteCart is a free open source ecommerce platform to power online retail. AbanteCart is a ready to run web application as well as reliable foundation to build custom eCommerce solutions. 
AbanteCart ecommerce platform is designed to fit a wide variety of online businesses and applications, as well as can be configured or customized to perform very specific business requirements. Chosen by many shop owners launching their business online for the first time, AbanteCart is also picked by developers looking for a user-friendly interface and powerful features set. 

## Reporting issues

Read the instructions below before you report an issue or a bug.

 1. Search the [AbanteCart forum](http://forum.abantecart.com), ask the community if they know about the issue or can suggest how to resolve it.
 2. Check open and closed issues on the [GitHub bug tracker](https://github.com/abantecart/abantecart-src/issues).
 3. READ the [changelog for the master branch](https://github.com/abantecart/abantecart-src/blob/master/release_notes.txt)
 4. Try [Google](http://www.google.com) to search for your issue.
 5. As many issues occur due to hosting environment related settings and problems, please make sure that your issue is not related only  to your hosting set up. 

If issue you report is not yet reported and related to the AbanteCart core code, please report it on GitHub. Provide as much details as needed and include screenshots.

**Important!**
- Issues that are not related to the core code (such as a third party extension or your server configuration) might be closed without explanation. You need to contact extension developer, use the forum or find a third partner to resolve a custom code issue.
- If you need to report a security bug, please PM an AbanteCart moderator/administrator on the forum first. All security reports are taken seriously but you must include detailed steps to reproduce them. Please do not report theoretical security flaws and donot post security flaws in a public location.

## Making a suggestion

We like improvements, but improvements are not bugs or issue. Please do not create an issue report if you think something needs improving (such as features or change to code standards etc).
We welcome public suggestions on our [AbanteCart forum](http://forum.abantecart.com).

## How to contribute

Fork the repository, edit and submit a pull request to branch that has latest version in development.

Please be very clear on your commit messages and pull request, empty pull request messages are not accepted.

Your code standards should match the AbanteCart coding standards. 

## Releases

AbanteCart will announce to developers 1-2 week prior to public release of BULD versions, this is to allow for testing of their own extensions for compatibility. For bigger releases an extended period will be considered following an announced release candidate. Revision versions may have a significantly reduced developer release period.
Prerelease will be also announced on a forum.

To receive developer notifications about release information, sign up to the newsletter on the [AbanteCart website](http://www.AbanteCart.com) - located in the footer. 

## How to install
1. Clone repository 

2. go to abantecart/abc/ directory in terminal

3. run command: wget https://getcomposer.org/composer.phar

4. run command:  php composer.phar install

5. set write permissions to: abc/system/logs ,  /public/, /public/resources/, /public/images/, abc/config/, abc/system/, abc/system/cache/, abc/downloads/, abc/extensions/…

6. run installation command from abc/ directory with required parameters. 

**php abcexec install:app --db_host=localhost --db_user=[DB USER] --db_password=[DB PASSWORD] --db_name=[DB NAME] --db_driver=mysql --admin_secret=[YOUR SECRET WORD] --username=[USER_NAME] --password=[PASSWORD] --email=[EMAIL] --http_server=[URL] --with-sample-data**

See php **abcexec --help** for more options

NOTE: You can run installation for the URL as well. You will need to point your web server to directory above install and load [URL]/install for installation steps.

7. Point your webserver web root to public/ directory  


## Command line tools:
Comand line tool is called abcexec and it is located in abc folder. 
To get a list of possible commands run:

**php abcexec --help**

To publish assests to public directory, run

**php abcexec publish:all --stage='default'**


## License

[Open Software License (OSL 3.0)](https://github.com/abantecart/abantecart-src/blob/master/LICENSE.txt)

## Links

- [AbanteCart homepage](http://www.abantecart.com/)
- [AbanteCart forums](http://forum.abantecart.com/)
- [AbanteCart Marketplace](http://marketplace.abantecart.com/)
- [How to documents](http://docs.abantecart.com/)
