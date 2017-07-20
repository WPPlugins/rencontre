=== Rencontre - Dating Site ===
Contributors: sojahu
Donate link: http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html
Tags: date, dating, meet, meeting, love, chat, webcam, rencontre, match, social, members, friends, messaging
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: 2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.

== Description ==

This WordPress plugin allows you to create a professional **dating website** with Wordpress. It is simple to install and administer with numerous possibilities.

The features are as follows :

* Login Required to access functionality ;
* Home unconnected with overview of the latest registered members and tiny quick search ;
* **Private messaging** between members ;
* **Extended and customizable profile** ;
* Private Members **chat with webcam** ;
* Sending smiles and contact requests ;
* **Advanced Search** ;
* Proximity search on **GoogleMap** ;
* Reporting of non-compliant member profiles ;
* Connecting with a **FaceBook** account ;
* Rapid registration ;
* Import photo from Facebook account ;
* **Sending regular emails to members** in accordance with the quota server ;
* Using the wp_users table for members to benefit of WordPress functions ;
* Daily cleaning to maintain the level of performance ;
* **Low resource**, optimized for shared web server ;
* Unlimited number of members ;
* Many adjustable parameters ;
* **Modularity** to fit many projects ;
* Adaptable **templates** ;
* Multilingual ;
* Easy **administration with filtering members** ;
* Blacklist by email ;
* IP Localization ;
* Import/Export members in CSV with photos ;
* **Standalone**, not depend on other services or other plugins.

[**Kit Premium**](http://www.boiteasite.fr/fiches/rencontre-premium.html) :

* Sophisticated **payment system** for members with numerous settings, restrictions and promotions - Compatible with WooCommerce gateways  ;
* Search by profile elements ;
* Search by **Astrological** affinity - Very powerful ;
* Pictures **moderation** on image wall - Very useful ;
* Blacklist by IP - Limitation by IP country ;
* Messages supervision for reported members - Very useful ;
* Insert Google AdSense in different places to **make money** ;
* Google AdWords conversion code for registration.

= Internationalization =

Rencontre is currently available in :

* English (main language)
* French - thanks to me :-)
* Spanish - thanks to Sanjay Gandhi
* Danish - thanks to [C-FR](http://www.C-FR.net/ "C-FR")
* Chinese - thanks to Lucien Huang
* Portuguese - thanks to Patricio Fernandes
* Italian - thanks to Gaelle Dozzi

If you have translated the plugin in your language or want to, please let me know on Support page.

== Installation ==

= Install and Activate =

1. Unzip the downloaded rencontre zip file
2. Upload the `rencontre` folder and its contents into the `wp-content/plugins/` directory of your WordPress installation
3. Activate Rencontre from Plugins page
4. Follow the instructions PRIMO to QUINTO below

= Implement =

If you are using a theme 2013, 2015 or 2016 you can download the [**child themes**](http://www.boiteasite.fr/fiches/telechargement/rencontre-child-theme.zip) and jump directly to QUARTO.

If you use the Twenty Seventeen theme (2017), you should watch this video.

[youtube http://www.youtube.com/watch?v=UwrIQWu4Vd8]

Connect to the Dashboard in ADMIN.

**Primo**

In 'Pages', edit or create the page of your choice (Home ?). Add the shortcode `[rencontre]` in your page content.

If you downloaded the child themes, select the template 'page-rencontre'. Save.

**Secundo (not required)**

For visitors not connected, you can view thumbnails and small profile of the last registered members :

Add the shortcode `[rencontre_libre]` in your page content. You can add it next to the other one : `[rencontre][rencontre_libre]` or in another page.

See FAQ for differents options.

**Tertio**

You need a WordPress Login/logout/Register link. Select one or more of these possibilities :

1. In Appearance / Menus, in the Rencontre block, select 'Log in' and 'Register' and add to the menu. Save. If you don't see the Rencontre block, check "screen options" at the top right.
2. Add the Rencontre registration form Shortcode `[rencontre_imgreg]` in your page content. (see FAQ).
3. Add the shortcode `[rencontre_login]` in your page content. Add also `[rencontre_loginFB]` to have the Facebook button.
4. Use the WordPress default widget 'Meta'.
5. Install a specific plugin like [baw-login-logout-menu](https://wordpress.org/plugins/baw-login-logout-menu/).
6. Use the widget from another plugin (BBPress has one).
7. Add this small code in your header.php file (or other one...), next to the menu :
`&lt;?php Rencontre::f_login(); ?&gt;`
or
`&lt;?php Rencontre::f_login('fb'); ?&gt;`
to have Facebook too.
 
**Quarto**

In Dashboard :

* In Settings / General, check the box 'Anyone can register' with role 'Subscriber'. Save.
* In Settings / Reading, check "a static page" and select "front-page" : the page with the shortcode. Save.

**Quinto**

Register as a member : Click Register, add login and email.

If you are localhost, you can't validate the email, but, in Admin panel / users, you can change the password of this new member.
Then, log in with this new login/password. Welcome to the dating part.

To facilitate the settings after installing, you can download and install via CSV (Rencontre/General) [**20 test profiles with photo**](http://www.boiteasite.fr/fiches/telechargement/rencontre_import_20_profiles.zip).
You are **not allowed** to use these pictures outside testing on your site.

More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

== Frequently Asked Questions ==

= I'm a newbie and I'm a real beginner with WordPress =
Expect some difficulties. It's a little more than plug and play.

= What template file from my theme to use =
You can download the 2013, 2015 and 2016 [**child themes here**](http://www.boiteasite.fr/fiches/telechargement/rencontre-child-theme.zip).
Take a look at the code of the page_rencontre.php

If nothing happens, add 
`&lt;h1&gt;*** HELLO ***&lt;/h1&gt;`
in your template.
If you don't see this title in front office, you are not using the right template. Try another file.

= Useful plugins to work with Rencontre =
* Email Templates : Send beautiful emails with the WordPress Email Templates plugin ;
* WP GeoNames : Insert all or part of the global GeoNames database in your WordPress base - Suggest city to members.

= Conditions to appear in un-logged homepage =
* Wait few days (parameter in admin) ;
* Have a photo on my profile ;
* Have an attention-catcher and an ad with more than 30 characters ;
* Rencontre::f_ficheLibre() is on the right template.

= How to personalize style =
The default style file is located in `rencontre/css/rencontre.css`.
You simply need to copy lines to be modified in the css file of your theme. And you can as well add other lines.
To overwrite default css file, add `#widgRenc` (and space) at the beginning of every new line.
Example :
`in rencontre.css :
.rencTab {background-color:#e8e5ce;}
in your css file :
#widgRenc .rencTab {background-color:#aaa; padding:1px;}`

You can also copy this file in your theme : /my_theme_folder/templates/rencontre.css. Then the original file will never be loaded.

= How to use unconnected search =

For visitors not connected, you can add a tiny quick search form :

*Method 1* : Shortcode : Add the shortcode `[rencontre_search nb=6]` in your page content. nb is the max result number.

*Method 2* : PHP in your theme (best solution for integrator) :

`&lt;?php if(!is_user_logged_in()) Rencontre::f_rencontreSearch(0, array('nb'=>6) ); ?&gt;`

= How to use Rencontre Templates =
Copy the files you have changed in your theme : /my_theme_folder/templates/. Don't copy unchanged files.

= How to show only the girls in un-logged homepage =
There are four categories differentiated by a different class CSS : girl, men, gaygirl and gaymen.
To see only the heterosexual girls, add in the CSS file of your theme :
` /* CSS */
 #widgRenc .rencBox.men,
 #widgRenc .rencBox.gaygirl,
 #widgRenc .rencBox.gaymen{display:none;}`

= How to set the plugin multilingual =
Add little flags in the header of your theme. On click, you create cookie with the right language. Then, the site changes language (back and front office) :
`&lt;div id="lang"&gt;
	&lt;a href="" title="Fran&ccedil;ais" onClick="javascript:document.cookie='lang=fr_FR;path=/'"&gt;
		&lt;img src="&lt;?php echo plugins_url('rencontre/images/drapeaux/France.png'); ?&gt;" alt="Francais" /&gt;
	&lt;/a&gt;
	&lt;a href="" title="English" onClick="javascript:document.cookie='lang=en_US;path=/'"&gt;
		&lt;img src="&lt;?php echo plugins_url('rencontre/images/drapeaux/Royaume-Uni.png'); ?&gt;" alt="English" /&gt;
	&lt;/a&gt;
&lt;/div&gt;`

= How to customize translation =
Put your po/mo files in wp-content/languages/plugins/ with the same name as the plugin (rencontre-xx_YY.mo).
You can also email us your best version so that we insert it in the plugin.

= User role & user removed =
All WordPress roles for the new Rencontre members are removed by this plugin to improve security and speed. That can be a conflict with other plugin.
The members without Rencontre account are automaticaly removed after two days if they can't "edit_posts".

If you want to keep users WP roles, you have just to check the option in the general tab.
Note that if you do this, user deletion (user himself or Admin) will only concern data in Rencontre. Account in WordPress will still exists.

= User registration =
Registration is divided in two part :

* WP registration : email and login Form => clic the email => you are on WP.
* Rencontre registration : phase one to four => you are in rencontre.

With the **fast registration** option :

* WP registration : email and login Form => you are in rencontre with a **limited status**. You have 3 days to complete your account and validate your email to be unlimited.

ADMIN side :

* Members : New user => he is in WP.
* Rencontre / Members : Add new from WordPress => he is in Rencontre.

= How to add profil search in search tab (like quick search) =
This is a Premium option. The number of items that can be added is unlimited.

= The automatic sending of emails =
There are two various types of email :

* Regular emails. They give the informations since the precedent regular email. They are sending every month (or 15 or 7 days). One serie during the maintenance hour and another serie the hour after. 
* Instant emails. They just give a instant information (contact request, message in box, smile). There is a sending per hour except during regular emails period. Only one email per person per hour.

= What to include with WP-GeoNames =
* Columns : minimum is name, latitude, longitude, country code, feature class & code.
* Type of data : only P (city).

It's better to limit the data size.

More details in french [here](http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html).

= Available Shortcodes =

* [rencontre] : Display the plugin
* [rencontre_libre] or [rencontre_libre gen=mix] : Display the unconnected part (home page for example)
   * gen=mix : men & girl in same number (&plusmn;5), gen=girl : only girl, gen=men : only men
* [rencontre_nbmembre] or [rencontre_nbmembre gen=girl ph=1] : Display the number of user
   * gen=girl or gen=men
   * ph=1 : only with photo
* [rencontre_search nb=8] : Display a search form for unconnected member (home page for example)
   * nb: number of results
* [rencontre_login] : link to login/logout/register
* [rencontre_loginFB] : Display the button to log with Facebook
* [rencontre_imgreg title= selector= left= top=] - Display registration form (See screenshots for example)
   * title='Register to ...'
   * selector='.site-header .wp-custom-header img' (jQuery selector of the image where you want to display the form - See Screenshots)
   * left=20 (Left position in purcentage of the parent container size). From 0 to 99. To set less than 10%, write 0 first (ex : 5% => 05)
   * top=10 (Top position in purcentage of the parent container size)

= How to use the Facebook login =
You need to create a Facebook application in your Facebook account. That will give you a ID. All details are in the Facebook documentation.
Set this ID in the right field in Rencontre/ General / Connection .
After that, you can use the shortcode [rencontre_loginFB] or the PHP code in your template Rencontre::f_login('fb');

= What is the Framework for the Facebook Like button ? =
The framework for the like button is like this :

`
<div id="fb-root"></div>
<script>(function(d, s, id) {var js,fjs=d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js=d.createElement(s);js.id=id;js.src="//connect.facebook.net/en_US/all.js#xfbml=1";fjs.parentNode.insertBefore(js, fjs);}(document,'script','facebook-jssdk'));</script>
<div class="fb-like" data-href="http://mysite.com" data-layout="button" data-action="like" data-show-faces="false" data-share="true"></div>
`

[Facebook doc](https://developers.facebook.com/docs/plugins/like-button?locale=en_US#configurator)

= Which Google Map API key to choose ? =
A same key can activate differents API. You need 3 API :

* Google Maps Geocoding API
* Google Maps JavaScript API
* Google Places API Web Service

https://console.developers.google.com/apis/library After connection to your account (gmail)

== Screenshots ==

1. Visitor's home page when not connected - Theme Twenty seventeen (2017).
2. The home page of a connected member - Theme Twenty thirteen (2013).
3. Visitor's home page when not connected - Theme Twenty fifteen (2015).
4. Private webcam chat.
5. Proximity search on GoogleMap.
6. Administration members.
7. Administration of available profiles.
8. Registration and connection statistics.

== Changelog ==

= 2.1 =
22/01/2017 :

* Rencontre menu items in WordPress menu are available from all the site. Connection link added. Registration link added.
* Shortcodes [rencontre_libre_mix], [rencontre_libre_girl], [rencontre_libre_men], [rencontre_nbmembre_girl], [rencontre_nbmembre_men], [rencontre_nbmembre_girlphoto], [rencontre_nbmembre_menphoto] removed. See FAQ.
* New shortcode for Registration Form on the main page (see screenshots theme 2017).
* Fix issue with my locked member list.
* Fix back-line issue in my ad.
* Fix error with bip.ogg & bip.mp3 URL.
* CSS and JS files only loaded when needed.
* TEMPLATE - rencontre-search - fix error in Gender select.
* TEMPLATE - account & registration_part2 - add autocomplete="off" in the City input.
* TEMPLATE - portrait_edit - Fix error : replace $u with $u0 line 44.

= 2.0 =
15/10/2016 :

* Overhaul of the code - Creation 26 Templates files.
* Smartphone display improved.
* Admin Dashboard with tabs to be more readable.
* GoogleMap API key is now needed.
* Facebook Graph API upgrade v2.7.

03/11/2016 : 2.0.1

* Add image rotate option on upload.
* Fix sidebar hidden.
* Shortcode "Rencontre" launched after init.
* Fix an issue in the message page that can cause CPU overload.
* TEMPLATE - registration_part2 - Add GoogleMap Key also in registration (forget it).
* TEMPLATE - sidebar_top - Fix error whis user name (bracket).
* TEMPLATE - account & registration_part1, 2 ,3 & rencontre.css - remove size in select tag, remove CSS .9em in corresponding options.
* TEMPLATE - message_conversation - confirm before deletion.

12/12/2016 : 2.0.2

* Add monthly message deletion option.
* Dashboard is now enable to all users who can 'edit posts'.
* Fix image rotation issue on IOS.
* The messages sent are displayed in italic in Inbox.
* Blocked member cannot send message (issue).
* New update DBIP (dec 2016).
* TEMPLATE - message_inbox - add class msgin or msgout. msgout in italic in rencontre.css
* TEMPLATE - message_conversation - delete "Date : ".

= 1.10.5 =
25/09/2016 :

* Add redirection option after login.
* Fix an issue with the auto deletion in fast registration that remove partially users.
* Method POST in place of GET in "Edit My Profile" and "Messaging" (Google AdSense warning).
* Add i_status column in table creation in rencontre.php (oops !).
* Fix a pagination issue in the online page.
* The "close" link is now at the top right of the pop up (it wasn't accessible on a smartphone).
* Fix issues in conversation list.
* Fix issues in CSV and updates.
* Auto load country DB on a new installation.
* Admin can validate the email of a fast registration user.
* Fix a bug in profil with custom gender.

= 1.10 =
27/06/2016 :

* New field in profile creation : Numeric SELECT from X to Y, step Z, unit U. Example : "How much sport do you practice a month" : 0, 60, 1, hour.
* Option to use the Rencontre user picture as WordPress avatar.
* Use i_status (rencontre_users) in place of user_status (users) to limit interference with other plugins.
* Fix bugs.

= 1.9.12 =
06/06/2016 :

* Add the options weight in pounds and size in feets and inches.
* Ability to remove a member in WordPress (case WP role not destroy).
* Fix error when remove users.
* Fix bug that can delete users.
* Add range in miles.
* Fix display bug in msgbox.
* Fix bug on immediate email sending.
* Add tooltip with ad of the member onmouseover ID in Admin tab.
* Admin can create new member from WP users list.
* Admin can modify the account datas of a member.
* Admin can reset reports and actions datas of a member.
* A member can refuse to receive email from the site.
* New fast registration system (activation in dashboard).
* Fix session bug in chat.
* Fix html tag in email.
* Complete the subject when it is not filled with the message.
* Complete the attention-catcher when it is not filled with the ad.
* Upload photo from Facebook account.
* Ability to limit sending messages for members without photo or profile.
* Search member by ID in Admin.
* The plugin cache is now in the WordPress Upload folder.
* Locale files loaded for all country (ex : pt_PT for pt_BR).
* Improve Admin graph stat.
* New message format like conversation - Remove subject - Add smiley.
* Rencontre menu can be included in the WP menu. See the "Appearance/Menus" admin tab and the "Rencontre/Custom" admin tab.
* Add shortcodes. See F.A.Q.
* The rencontre sidebar (quick search...) can be remove and added in the theme sidebar with a new widget. See the "Appearance/Widgets" admin tab and the "Rencontre/Custom" admin tab.
* Add Italian language - thanks to Gaelle Dozzi.
* Improve GoogleMap display.
* Age limit 18 to 99 can be changed in the Custom tab.
* Ability to choose different profiles between girls and mens.
* Cancel redirection after login if Admin.
* Option to hide the "delete account" button.
* Option to hide the "no email from this site" button.
* Option to show only mini-portrait in search result.
* Dbip version stored.
* Fix bugs.

= 1.9 =
06/01/2016 : 

* Upload photo from Facebook account.
* Ability to limit sending messages for members without photo or profile.
* Search member by ID in Admin.
* The plugin cache is now in the WordPress Upload folder.
* Fix bugs.

= 1.8.9 =
29/12/2015 :

* Fix bug on incomplete fields.
* Add option to remove flags on home page.
* CSS more responsive.
* Follow-up e-mail.
* Auto-connexion when one click an email link.
* Fix bug with tchat (asks oneself in a particular case).
* Ability to change words 'Smile' and 'Look'.
* Improve regular emails style with buttons to smile, write and send contact request.
* DBIP database in GZ in place of ZIP.
* Add popup to advise user without photo.
* Inline CSS in mail.
* Fix bug in some case with session.

= 1.8 =
30/10/2015 : 

* Add graph stat in Admin tab.
* Add automatic deletion of old messages.
* Possibility to choose several types of relationship.
* Fix bugs.

= 1.7.15 =
14/10/2015 : 

* Set a country by default on a new install (needed for google-map).
* Fix a bug in search on Google-Map when WP_Geonames is installed without country.
* Password in the new user email is replaced by an email with a connexion link : Needed for WordPress 4.3.
* Reorder the Profiles is now very easy - Add quick search with profile "checkbox".
* Add session_start : Needed for WordPress 4.3.
* All dates in local time
* Fix bug in "a member contact you" email.
* Fix a bad username availability when registered.
* New IP data base included (from ipdb.com). Plugin "GeoIP Detect" is no longer used and can be removed.
* Mini photo reloaded in browser after change.
* Big photo reloaded in browser after change.
* Option to keep WP roles (compatibility with some plugins)
* Fix bug with Rencontre icon in Dashboard.
* Add Shortcode [rencontre_loginFB]
* Fix new user notification issue.
* Fix issue when new user change login and password.
* Compatibility with Premium V1.4 (WooCommerce Payment & moderation).

= 1.7 =
01/08/2015 : 

* Add an Admin tab with numerous options to customize the plugin.
* Add quick search in un-logged homepage.
* Improve plugin installation.
* Fix an important security issue that can give admin access to a member.
* Fix bugs.

= 1.6.9 =
09/07/2015 :

* Admin can now block a member.
* Option to send an email for a smile.
* CSS responsive and homogeneous.
* Add link to the recipient in sent message.
* Fix bug when user delete account.
* Cut the admission form in four part with progress bar.
* Fix bug in Chat when WordPress is not at the site root : Change relative URL to absolute in Chat
* Change the image encoding detection.
* Hide better a blocked member.
* Sort countries and regions by alphabetical order.
* Option in Admin part to block sending messages by a specific member.
* Fix V1.6 bug when change profil in admin : synchro button is now visible.

= 1.6 =
06/05/2015 : 

* Add hook for the new Premium Kit.
* Add online link on main page.
* Add search by relation.
* 15% speed improvement.
* Fix some bugs.

= 1.5.2 =
16/04/2015 :

* Fix pagination bug in search result after first page.
* Fix bug when change profil in admin.
* Search user in admin by Alias or E-mail.
* add shortcode [rencontre] to simplify installation.
* fix CSS bug with footer.

= 1.5 =
16/03/2015 : Change main language from French to English

= 1.4.7 =
22/02/2015 :

* Fix style bug.
* Change the request to GET.
* Add custom text in image copyright.
* Number of pictures configurable from one to eight.
* Add dropbox to pictures.
* Add previous msg when reply.
* Fix bug in msg with same subject.
* Style select box, fix some bugs.
* Display date of last connection.
* Fix unsubscribe and subscribe bug.
* Fix warning during installation.

= 1.4 =
06/12/2014 :

* Proximity search with GoogleMap.
* Improve separation between gay / heterosexual.
* Fix bug with Shortcode.
* Search result order by date of last connection.

= 1.3.6 =
01/12/2014 :

* Countries and profiles in Chinese language (thanks to Lucien Huang).
* Homogeneous distribution between men and women in un-logged homepage.
* Improves the automatic email sending (backlink, monthly/fortnightly/weekly options).
* Fix defect with smiles in search result.
* Translation correction.
* Suggest a city from the geonames database if plugin wp-geonames is installed.

= 1.3 =
15/10/2014 :

* Import/Export members in CSV with photos.
* Add Chinese language (thanks to Lucien Huang).
* Add pseudo in chat.
* Add code to get number of members in base.
* Fix some bugs.

= 1.2.8 =
08/10/2014 :

* Fix conflict with Yop-Poll.
* Add Danish language (thanks to C-FR.net).
* Add pagination in search result.
* Fix bug in country select (sort in all languages).
* Fix incompatibility with some servers for the small copyright on members photos.
* Fix the country selected in -my account-.
* Change quick search result when option Members without photo less visible disabled.
* Add option in Admin to set default country.
* Add class CSS "girl, men, gaygirl, gaymen" in unconnected overview list.
* Fix error in small copyright function (again).
* Fix Deletion of the Admin account (again).
* Add link to user profile in message tab.
* Fix bug in search result.
* Add multilingual hook.

= 1.2 =
14/09/2014 :

* Multilingual Countries with Admin panel to add or change countries and regions.
* Fix HTML format in e-mail.
* Fix some bugs.
* Add some translations in Admin part.

= 1.1.8 =
04/09/2014 :

* Fix Facebook connect bug.
* Add Spanish language (thanks to Sanjay Gandhi).
* Memory of the search.
* Installation page in readme file.
* Limit number of result in search.
* Fix Deletion of the Admin account by cron schedule.
* Add CSS clear in fiche libre.
* Input default CSS file in fiche libre.
* Remove auto-zoom in fiche libre (unconnected).
* Fix CSS in fiche libre.
* Fix bug if no WPLANG in wp-config.php.
* Add my homepage setup in admin.
* Fix default CSS.
* auto close chat if inactif.
* Fix warning php opendir.

= 1.1 =
19/06/2014 :

* Email sending : optimization and improvement.
* Emails translation.
* Fixed some bug...

= 1.0 =
09/06/2014 - First stable version.
