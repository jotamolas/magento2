<?php

namespace Jotadevs\OCAEPackShipping\Model\Query;

use Magento\Framework\HTTP\ZendClient;

class OcaApi
{
    protected $zendClient;
    protected $uri = 'http://webservice.oca.com.ar/ePak_tracking/Oep_TrackEPak.asmx/';
    protected $cuit = '30-71569993-8';
    protected $operativa = '302177';
    protected $cod_postal_suc = '5000';
    protected $peso_total = '2';
    protected $volumen_total = '0.00425';
    protected $cantidad = 1;
    public function __construct(ZendClient $zendClient)
    {
        $this->zendClient = $zendClient;
    }

    public function tarifarEnvio(
        $cod_postal_dest,
        $operativa = null,
        $cuit = null,
        $cod_postal_suc = null,
        $peso_total = null,
        $volumen_total = null,
        $cantidad = null
    ) {
        ($operativa) ? $this->operativa = $operativa : null;
        ($cuit) ? $this->cuit = $cuit : null;
        ($cod_postal_suc) ? $this->cod_postal_suc = $cod_postal_suc : null;
        ($peso_total) ? $this->peso_total = $peso_total : null;
        ($volumen_total) ? $this->volumen_total = $volumen_total : null;
        ($cantidad) ? $this->cantidad = $cantidad : null;
        $this->zendClient->resetParameters();

        try {
            $this->zendClient->setUri($this->uri . "Tarifar_Envio_Corporativo");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setParameterGet(
                [
                    'PesoTotal' => $this->peso_total,
                    'VolumenTotal' => $this->volumen_total,
                    'CodigoPostalOrigen' => $this->cod_postal_suc,
                    'CodigoPostalDestino' => $cod_postal_dest,
                    'CantidadPaquetes' => $this->cantidad,
                    'ValorDeclarado' => '0',
                    'Cuit' => $this->cuit,
                    'Operativa' => $this->operativa
                ]
            );
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'test/xml'
                ]
            );

            $response = $this->zendClient->request();
            //TODO tengo que ver esto... tengo que quitar el absolute URL #Oca_e_Pak porque no funca ---> resolvido
            $response_formated = str_replace('xmlns="#Oca_e_Pak"', '', $response->getBody());
            $response_formated = str_replace(["diffgr:","msdata:"], '', $response_formated);
            $xml_response = simplexml_load_string($response_formated);
            return [
                'state' => 'ok',
                'Ambito' => (string) $xml_response->diffgram->NewDataSet->Table->Ambito,
                'Precio' => (string) $xml_response->diffgram->NewDataSet->Table->Total,
                'PlazoEntrega' => (string) $xml_response->diffgram->NewDataSet->Table->PlazoEntrega
            ];

            //return $xml_response->diffgram->NewDataSet->Table;
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    public function GetCentrosImposicionPorCP($cod_postal_dest)
    {
        $this->zendClient->resetParameters();
        try {
            $this->zendClient->setUri("http://webservice.oca.com.ar/oep_tracking/Oep_Track.asmx/GetCentrosImposicionPorCP");
            $this->zendClient->setMethod(ZendClient::GET);
            $this->zendClient->setParameterGet(
                [
                    'CodigoPostal' => $cod_postal_dest
                ]
            );
            $this->zendClient->setHeaders(
                [
                    'Content-Type' => 'test/xml'
                ]
            );

            $response = $this->zendClient->request();
            //TODO tengo que ver esto... tengo que quitar el absolute URL #Oca_e_Pak porque no funca ---> resolvido
            $response_formated = str_replace('xmlns="#Oca_Express_Pak"', '', $response->getBody());
            $response_formated = str_replace(["diffgr:","msdata:"], '', $response_formated);
            $xml_response = simplexml_load_string($response_formated);
            return [
                'state' => 'ok',
                'Descripcion' => (string) $xml_response->diffgram->NewDataSet->Table->Descripcion,
                'Calle' => (string) $xml_response->diffgram->NewDataSet->Table->Calle,
                'Numero' => (string) $xml_response->diffgram->NewDataSet->Table->Numero,
                'Telefono' => (string) $xml_response->diffgram->NewDataSet->Table->Telefono
            ];
        } catch (\Zend_Http_Client_Exception $e) {
            return [
                'state' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }
}
