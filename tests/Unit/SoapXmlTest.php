<?php

declare(strict_types=1);

namespace Tests\Unit;

use Rudashi\Client\SoapXml;
use RuntimeException;
use ValueError;

covers(SoapXml::class);

it('parse MIME from xml', function (): void {
    $response = '
--uuid:ace3c001-edf5-4806-94a8-a5b766e3b787+id=37955
Content-ID: <https://uri.org/0>
Content-Transfer-Encoding: 8bit
Content-Type: application/xop+xml;charset=utf-8;type="application/soap+xml"

<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Body>
        <GetValueResponse xmlns="https://example.com">
            <GetValueResult>1</GetValueResult>
        </GetValueResponse>
    </s:Body>
</s:Envelope>
--uuid:ace3c001-edf5-4806-94a8-a5b766e3b787+id=37955--
';

    expect(SoapXml::parse($response))
        ->toBe('<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Body>
        <GetValueResponse xmlns="https://example.com">
            <GetValueResult>1</GetValueResult>
        </GetValueResponse>
    </s:Body>
</s:Envelope>');
});

it('parse simple xml', function (): void {
    $response = '
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
  <soap:Body>
    <m:ListOfContinentsByNameResponse xmlns:m="https://www.oorsprong.org/websamples.countryinfo">
      <m:ListOfContinentsByNameResult>
        <m:tContinent>
          <m:sCode>AF</m:sCode>
          <m:sName>Africa</m:sName>
        </m:tContinent>
      </m:ListOfContinentsByNameResult>
    </m:ListOfContinentsByNameResponse>
  </soap:Body>
</soap:Envelope>
';

    expect(SoapXml::parse($response))
        ->toBe('<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
  <soap:Body>
    <m:ListOfContinentsByNameResponse xmlns:m="https://www.oorsprong.org/websamples.countryinfo">
      <m:ListOfContinentsByNameResult>
        <m:tContinent>
          <m:sCode>AF</m:sCode>
          <m:sName>Africa</m:sName>
        </m:tContinent>
      </m:ListOfContinentsByNameResult>
    </m:ListOfContinentsByNameResponse>
  </soap:Body>
</soap:Envelope>');
});

it('parse xml soap 1.2 version', function (): void {
    $response = '
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
 <env:Header>
     <t:transaction
        xmlns:t="https://thirdparty.example.org/transaction"
          env:encodingStyle="https://example.com/encoding"
           env:mustUnderstand="true">5</t:transaction>
 </env:Header>  
 <env:Body>
     <m:chargeReservationResponse 
         env:encodingStyle="http://www.w3.org/2003/05/soap-encoding"
             xmlns:m="https://travelcompany.example.org/">
       <m:code>FT35ZBQ</m:code>
       <m:viewAt>
         https://travelcompany.example.org/reservations?code=FT35ZBQ
       </m:viewAt>
     </m:chargeReservationResponse>
 </env:Body>
</env:Envelope>
';

    expect(SoapXml::parse($response))
        ->toBe('<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
 <env:Header>
     <t:transaction
        xmlns:t="https://thirdparty.example.org/transaction"
          env:encodingStyle="https://example.com/encoding"
           env:mustUnderstand="true">5</t:transaction>
 </env:Header>  
 <env:Body>
     <m:chargeReservationResponse 
         env:encodingStyle="http://www.w3.org/2003/05/soap-encoding"
             xmlns:m="https://travelcompany.example.org/">
       <m:code>FT35ZBQ</m:code>
       <m:viewAt>
         https://travelcompany.example.org/reservations?code=FT35ZBQ
       </m:viewAt>
     </m:chargeReservationResponse>
 </env:Body>
</env:Envelope>');
});

it('parse xml soap 1.1 version', function (): void {
    $response = '
POST /StockQuote HTTP/1.1
Host: www.stock.com
Content-Type: text/xml; charset="utf-8"
Content-Length: n
SOAPAction: "Some-URI"

<SOAP-ENV:Envelope
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
   <SOAP-ENV:Header>
       <t:Transaction
           xmlns:t="some-URI"
           SOAP-ENV:mustUnderstand="1">
               5
       </t:Transaction>
   </SOAP-ENV:Header>
   <SOAP-ENV:Body>
       <m:GetLastTradePrice xmlns:m="Some-URI">
           <symbol>DEF</symbol>
       </m:GetLastTradePrice>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

    expect(SoapXml::parse($response))
        ->toBe('<SOAP-ENV:Envelope
  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
   <SOAP-ENV:Header>
       <t:Transaction
           xmlns:t="some-URI"
           SOAP-ENV:mustUnderstand="1">
               5
       </t:Transaction>
   </SOAP-ENV:Header>
   <SOAP-ENV:Body>
       <m:GetLastTradePrice xmlns:m="Some-URI">
           <symbol>DEF</symbol>
       </m:GetLastTradePrice>
   </SOAP-ENV:Body>
</SOAP-ENV:Envelope>');
});

describe('throw exceptions', function (): void {
    it('throw exception on unknown xml structure', function () {
        $response = '<soap:Envelope xmlns:soap="http://www.w3.org/2001/12/soap-envelope"></soap:Envelope>';

        expect(fn () => SoapXml::parse($response))
            ->toThrow(
                exception: ValueError::class,
                exceptionMessage: 'Unknown SOAP XML type.',
            );
    });

    it('throw exception when missing namespace', function () {
        $response = '<soap:Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/"></soap:Envelope>';

        expect(fn () => SoapXml::parse($response))
            ->toThrow(
                exception: RuntimeException::class,
                exceptionMessage: 'SOAP namespace prefix not found.',
            );
    });

    it('throw exception when missing Envelope element', function () {
        $response = '<soap:e xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"></soap:e>';

        expect(fn () => SoapXml::parse($response))
            ->toThrow(
                exception: RuntimeException::class,
                exceptionMessage: 'Missing mandatory "Envelope" element.',
            );
    });
});
