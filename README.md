#About


This PHP Library allows you to connect with a Maniaplanet Server to display e.g. the current players on your website. It´s a rewrite of the originally with dedicated server deliverd GbxRemote.inc.php using php´s xmlrpc extension.

#Documentation
##Install php xmlrpc extension
###Linux
```
apt-get install php5-xmlrpc
```
###Windows
uncomment the following line in your php.ini (remove ; in front of the line)
```
;extension=php_xmlrpc.dll
```
##Sample Code
```php
$client = new GBXRemote();
$client->connect("127.0.0.1", 5000);
$client->query("Authenticate", "SuperAdmin", "SuperAdminPassword");
$version = $client->query("GetVersion");
$maps = $client->query('GetMapList', 100, 0);
$client->close();
```

