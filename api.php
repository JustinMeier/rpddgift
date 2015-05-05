<?
$cardNo = "";
$amount = "";

// Read RPOWER POST
$rpXMLData = file_get_contents('php://input');

$xml = simplexml_load_string($rpXMLData);
$command = $xml->XyzzyHeader[0]['api_command'];

if(isset($xml->CCX_QUERY->Parms[0]['trk1'])){
$amtWtip = $xml->CCX_QUERY->Parms[0]['trk1'];
}

if(isset($xml->CCX_QUERY->Parms[0]['parm1'])){
$amount = $xml->CCX_QUERY->Parms[0]['parm1'];
}

if(isset($xml->CCX_QUERY->Card[0]['num'])){
$cardNo = $xml->CCX_QUERY->Card[0]['num'];
}

if ($command == "CHARGE") {
redeem($cardNo, $amtWtip);
}
elseif ($command == "BALINQUIRY" || $command == "QUERY"){
balance($cardNo);
}
else {
die();
}

function redeem($cardNo, $amtWtip) {
// Rpower = cents, DD = float.. Divide by 100....
$amtWtip = $amtWtip/100;

// Format to DD SOAP
$ddXMLData = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:gif=\"https://www.DigitalDining.com/GiftCertificate/\">
   <soapenv:Header/>
   <soapenv:Body>
      <gif:ProcessGiftCertificate_v1>
         <gif:request>
  <gif:Product>EPOS GC</gif:Product>
  <gif:Version>0.1a</gif:Version>
  <gif:SiteID>RP</gif:SiteID>
  <gif:MerchantID>10</gif:MerchantID>
            <gif:RequestID>1</gif:RequestID>
            <gif:RequestType>Redeem</gif:RequestType>
            <gif:CurrencyCode>USD</gif:CurrencyCode>
            <gif:CertificateID>$cardNo</gif:CertificateID>
            <gif:InvoiceID>1</gif:InvoiceID>
            <gif:Amount>$amtWtip</gif:Amount>
         </gif:request>
      </gif:ProcessGiftCertificate_v1>
   </soapenv:Body>
</soapenv:Envelope>";
$URL = "http://10.46.6.1/DDGCWebService/DDGiftCertificateWebService.asmx?op=ProcessGiftCertificate_v1";
$ch = curl_init($URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "$ddXMLData");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

$response = str_replace('https://www.DigitalDining.com/GiftCertificate/', '', $output);
$xml = simplexml_load_string($response);
$ret = $xml->xpath('//response');
$balance = $ret[0]->Balance;
$parm1 = $balance*100;
$date = date("Y-m-d")."T".date("G:i:sP");
$tz = date("T");

// Format to RPOWER spec
echo "<?xml version=\"1.0\"?>
<XyzzyTalk>
<XyzzyHeader xyzzy_version=\'6.10.29.4\' api_id=\'CUSCNX\' api_version=\'14.5.22.1\'
             api_command=\'CHARGE\' app_version=\'14010703\'
             date_time=\'$date\' tz=\'$tz\'/>
  <CCX_RESPONSE>
    <Tran sref=\'1188\'/>
    <Parms parm1=\'$amount\'/>
    <Info>
      <Bal cd=\'\$US$balance\'/>
    </Info>
  </CCX_RESPONSE>
</XyzzyTalk>";
}


function balance($cardNo) {
$ddXMLData = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:gif=\"https://www.DigitalDining.com/GiftCertificate/\">
   <soapenv:Header/>
   <soapenv:Body>
      <gif:ProcessGiftCertificate_v1>
         <gif:request>
  <gif:Product>EPOS GC</gif:Product>
  <gif:Version>0.1a</gif:Version>
  <gif:SiteID>Jackalope</gif:SiteID>
  <gif:MerchantID>10</gif:MerchantID>
            <gif:RequestID>1</gif:RequestID>
            <gif:RequestType>BalanceInquiry</gif:RequestType>
            <gif:CurrencyCode>USD</gif:CurrencyCode>
            <gif:CertificateID>$cardNo</gif:CertificateID>
            <gif:InvoiceID>1</gif:InvoiceID>
         </gif:request>
      </gif:ProcessGiftCertificate_v1>
   </soapenv:Body>
</soapenv:Envelope>";
$URL = "http://10.46.6.1/DDGCWebService/DDGiftCertificateWebService.asmx?op=ProcessGiftCertificate_v1";
$ch = curl_init($URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "$ddXMLData");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);

$response = str_replace('https://www.DigitalDining.com/GiftCertificate/', '', $output);
$xml = simplexml_load_string($response);
$ret = $xml->xpath('//response');
$balance = $ret[0]->Balance;

// Format to RPOWER spec
$date = date("Y-m-d")."T".date("G:i:sP");
$tz = date("T");
echo "
<?xml version=\"1.0\"?>
<XyzzyTalk>
<XyzzyHeader xyzzy_version=\'6.10.29.4\' api_id=\'CUSCNX\' api_version=\'14.5.22.1\'
             api_command=\'BALINQUIRY\' app_version=\'14010703\'
             date_time=\'$date\' tz=\'$tz\'/>
  <CCX_RESPONSE>
    <Info>
      <Bal cd=\'\$US$balance\'/>
    </Info>
  </CCX_RESPONSE>
</XyzzyTalk>";
}

?>
