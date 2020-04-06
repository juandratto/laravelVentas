<?php
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;

// CLAVE SOL utilizada.
// Ruc: 20000000001
// Usuario: MODDATOS
// Contraseña: moddatos

$see = new See();
$see->setService(SunatEndpoints::FE_BETA);
$see->setCertificate(file_get_contents(__DIR__.'/certificate.pem'));
$see->setCredentials('20000000001MODDATOS'/*ruc+usuario*/, 'moddatos');

return $see;