<?php

namespace Jotadevs\BotonArrepentimiento\Controller\Carga;

use Jotadevs\BotonArrepentimiento\Controller\Boton;
use Jotadevs\BotonArrepentimiento\Model\CasoFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;

class Guardar extends Boton
{
    private static $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
    private $_secret;
    private static $_version = "php_1.0";
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $formKeyValidator;
    protected $dateTime;
    /**
     * @var CasoFactory
     */
    protected $casoFactory;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        DateTime $dateTime,
        CasoFactory $casoFactory
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->dateTime = $dateTime;
        $this->messageManager = $context->getMessageManager();
        $this->casoFactory = $casoFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $captcha = $this->getRequest()->getParam('g-recaptcha-response');
        $secret = "6Lf7uIoaAAAAAFA9XTv30a44TQ-JRZ2-aJ0XkHLz";
        $response = null;
        $path = self::$_siteVerifyUrl;
        $dataC = array(
            'secret' => $secret,
            'remoteip' => $_SERVER["REMOTE_ADDR"],
            'v' => self::$_version,
            'response' => $captcha
        );
        $req = "";
        foreach ($dataC as $key => $value){
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }
        $req = substr($req,0,strlen($req)-1);
        $response = file_get_contents($path . $req);
        $answer = json_decode($response,true);

        if(trim($answer['success']) == true){
            $resultRedirect = $this->resultRedirectFactory->create();
            if (
            !$this->formKeyValidator->validate(
                $this->getRequest()
            )
            ) {
                return $resultRedirect->setRefererUrl();
            }

            $nombre = $this->getRequest()->getParam('nombre');
            $apellido = $this->getRequest()->getParam('apellido');
            $email = $this->getRequest()->getParam('email');
            $motivo = $this->getRequest()->getParam('motivo');
            $ciudad = $this->getRequest()->getParam('ciudad');
            $provincia = $this->getRequest()->getParam('provincia');
            $fecha = $this->getRequest()->getParam('fecha');
            $dni = $this->getRequest()->getParam('dni');
            $identificadorCompra = $this->getRequest()->getParam('identificadorCompra');

            try {
                $caso = $this->casoFactory->create();
                $caso->setNombre($nombre)
                    ->setApellido($apellido)
                    ->setEmail($email)
                    ->setMotivo($motivo)
                    ->setCiudad($ciudad)
                    ->setProvincia($provincia)
                    ->setFecha($fecha)
                    ->setDni($dni)
                    ->setIdentificadorCompra($identificadorCompra);
                $caso->save();
            } catch (Exception $e) {
                $this->messageManager->addError(__('Error occurred during ticket creation.'));
            }
            return $resultRedirect->setRefererUrl();
        }else{
            return $this->messageManager->addErrorMessage("Error en el captcha");
        }


    }
}
