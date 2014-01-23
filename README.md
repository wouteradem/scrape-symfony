Symfony2 application to scrape https://eloket.privacycommission.be

// RUNNING THE APPLICATION
Run from a server:
$ ssh europaservice@datahub-001.bitsoflove.openminds.be
$ cd crawler
$ ./go.sh

Last run was done on 23th of January and 95843 web pages were scraped

Run from localhost:
$ php app.php scrape:website [BEGIN] [END]

// DATA
Data is available from mongoLab.com
$mongodb://<dbuser>:<dbpassword>@ds027809.mongolab.com:27809/privacy_commission
mongo ds027809.mongolab.com:27809/privacy_commission -u <dbuser> -p <dbpassword>
<dbuser> = vicrau
<dbpassword> = b1t5mongolab

Data is imported using the json files which are stored in src/BOL/data.
This cannot be done from the server, instead do it from your localhost.

$ mongoimport -h ds027809.mongolab.com:27809 -d privacy_commission -c privacycommission -u vicrau -p b1t5mongolab --file [NUMBER].json --jsonArray


