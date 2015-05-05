# rpddgift
RPOWER POS Gift Card Interface To Digital Dining

This API will allow RPOWER to accept gift cards from a Digital Dining Multi-Store Gift Site.
The DD site must be on 7.4.2 or higher with the multi-stite module.
The web server needs a recent version of php with curl extension enabled.

api.php -
Change the 2 URLs in the soap requests to the IP/host of the DD server's web service
$URL = "http://10.46.6.1/DDGCWebService/DDGiftCertificateWebService.asmx?op=ProcessGiftCertificate_v1";

It is possible on an upgrade to RP, you will need to update the following lines to match the newer version of RP
<XyzzyHeader xyzzy_version=\'6.10.29.4\' api_id=\'CUSCNX\' api_version=\'14.5.22.1\'
             api_command=\'CHARGE\' app_version=\'14010703\'
             date_time=\'$date\' tz=\'$tz\'/>
             
  cuscnx.ini -
  Change server= to the ip/host:port of the web server
  Change servertarget= to the path of the api.php file
  This has not been tested with HTTPS....
  Obviously, change the cardrule and acctprefix to match your rpower settings.
  
  
