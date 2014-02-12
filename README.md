#About


This PHP library allows you to connect with a Maniaplanet Server to display e.g. the current players on your website. It´s a rewrite of the originally with the dedicated server deliverd GbxRemote.inc.php. It was a test if the php build in xmlrpc library is faster than a self written one. Some tests have shown that this isn´t the case for a small amount of requests. However if you are using this library with a servercontroller wtih frequent requests you will get better speed and workload. 

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
$client->Authenticate("SuperAdmin", "SuperAdminPassword");
$version = $client->GetVersion();
$maps = $client->GetMapList(100, 0);
$client->close();
```

