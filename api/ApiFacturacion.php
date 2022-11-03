<?php
namespace api;
use api\Signature;
class ApiFacturacion
{
	
	public $mensajeError;
	public $coderror;
	public $xml;
	public $xmlb64;
	public $cdrb64;
	public $codrespuesta;
	public $hash;
	public $ticketS;
	public $code;
	
    public function EnviarComprobanteElectronico($emisor, $nombre, $ruta_archivo_xml, $ruta_archivo_cdr, $rutacertificado=null)
    {
		if($emisor['modo'] == 'n'){
			$usuario_sol = $emisor['usuario_prueba'];
			$clave_sol = $emisor['clave_prueba'];
			$certificado = $emisor['certificado_prueba'];
			$wsS = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
			$pass_certificado = 'ceti';
			

		}
		if($emisor['modo'] == 's'){
			$usuario_sol = $emisor['usuario_sol'];
			$clave_sol = $emisor['clave_sol'];
			$certificado = $emisor['certificado'];
			$wsS = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl';
			$pass_certificado = $emisor['clave_certificado'];
			
		}
		
        $objfirma = new Signature();
        $flg_firma = 0; //Posicion del XML: 0 para firma
        // $ruta_xml_firmar = $ruta . '.XML'; //es el archivo XML que se va a firmar
        $ruta = $ruta_archivo_xml . $nombre . '.XML';

        $ruta_firma = $rutacertificado. 'api/certificado/'.$certificado; //ruta del archivo del certicado para firmar
        $pass_firma = $pass_certificado;
        
        $resp = $objfirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);
		//firma----------------------------------------------------------------
        //print_r($this->hash = $resp);
        //echo '</br> XML FIRMADO';
        $this->xml = $nombre.'.XML';
        //FIRMAR XML - FIN
        
        //CONVERTIR A ZIP - INICIO
        $zip = new \ZipArchive();

        $nombrezip = $nombre.".ZIP";
        $rutazip = $ruta_archivo_xml . $nombrezip;
        
        if($zip->open($rutazip, \ZipArchive::CREATE) === TRUE)
        {
            $zip->addFile($ruta, $nombre.'.XML');
            $zip->close();
        }
        
       // echo '</br>XML ZIPEADO';
        
        //CONVERTIR A ZIP - FIN
        
        
        //ENVIAR EL ZIP A LOS WS DE SUNAT - INICIO
        $ws = $wsS; //ruta del servicio web de pruebad e SUNAT para enviar documentos
        
        $ruta_archivo = $rutazip;
		$nombre_archivo = $nombrezip;

        $contenido_del_zip = base64_encode(file_get_contents($ruta_archivo)); //codificar y convertir en texto el .zip
        
        //echo '</br> '. $contenido_del_zip;
        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                        <soapenv:Header>
                        <wsse:Security>
                            <wsse:UsernameToken>
                                <wsse:Username>'.$emisor['ruc'].$usuario_sol.'</wsse:Username>
                                <wsse:Password>'.$clave_sol.'</wsse:Password>
                            </wsse:UsernameToken>
                        </wsse:Security>
                        </soapenv:Header>
                        <soapenv:Body>
                        <ser:sendBill>
                            <fileName>'.$nombre_archivo.'</fileName>
                            <contentFile>'.$contenido_del_zip.'</contentFile>
                        </ser:sendBill>
                        </soapenv:Body>
                    </soapenv:Envelope>';
        
            $header = array(
                "Content-type: text/xml; charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: ",
                "Content-lenght: ".strlen($xml_envio)
                );
        
        $ch = curl_init(); //iniciar la llamada
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
        curl_setopt($ch,CURLOPT_URL, $ws);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        
        //para ejecutar los procesos de forma local en windows
        //enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem"); //solo en local, si estas en el servidor web con ssl comentar esta línea
        
        $response = curl_exec($ch); // ejecucion del llamado y respuesta del WS SUNAT.
        
        $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE); // objten el codigo de respuesta de la peticion al WS SUNAT
        $estadofe = "0"; //inicializo estado de operación interno
        
        if($httpcode == 200)//200: La comunicacion fue satisfactoria
        {
            $doc = new \DOMDocument();//clase que nos permite crear documentos XML
            $doc->loadXML($response); //cargar y crear el XML por medio de text-xml response
        
            if(isset( $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue ) ) // si en la etique de rpta hay valor entra
            {
				
                $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue; //guadarmos la respuesta(text-xml) en la variable 
			
                $cdr = base64_decode($cdr); //decodificando el xml
                file_put_contents($ruta_archivo_cdr . 'R-' . $nombrezip, $cdr ); //guardo el CDR zip en la carpeta cdr
				
				$this->cdrb64 = "R-".$nombrezip;

                $zip = new \ZipArchive();
                if($zip->open($ruta_archivo_cdr. 'R-' . $nombrezip ) === true ) //rpta es identica existe el archivo
                {
                    $zip->extractTo($ruta_archivo_cdr, 'R-' . $nombre . '.XML');
                    $zip->close();

					$this->xmlb64 = "R-".$nombre.'.XML';					
					
                }
				
				$xml_decode = file_get_contents($ruta_archivo_cdr.'R-' . $nombre . '.XML') or die("Error: Cannot create object");
				$xml_decode = str_replace('<ar:', '<cac:', $xml_decode);
				$xml_decode = str_replace('</ar:', '</cac:', $xml_decode);
				$xml_decode = str_replace('<cbc:', '<cac:', $xml_decode);
				$xml_decode = str_replace('</cbc:', '</cac:', $xml_decode);
				$xml_decode = str_replace('<ar:', '<', $xml_decode);
				$xml_decode = str_replace('</ar:', '</', $xml_decode);
				$xml_decode = str_replace('<cac:', '<', $xml_decode);
				$xml_decode = str_replace('</cac:', '</', $xml_decode);
				$xml_decode = str_replace('<ext:', '<', $xml_decode);
				$xml_decode = str_replace('</ext:', '</', $xml_decode);
				$xml_decode = simplexml_load_string(utf8_encode($xml_decode));
				// $xml_decode = json_decode(json_encode((array)$xml_decode), true);
			
			function xmlarray ($xmlObject, $out = array () )
			{
				foreach ( (array) $xmlObject as $index => $node )
					$out[$index] = ( is_object ( $node ) ) ? xmlarray ( $node ) : $node;

				return $out;
			}
		    	$xml_decode = xmlarray($xml_decode);

				$cod_hash = $xml_decode["UBLExtensions"] ["UBLExtension"]["ExtensionContent"]["Signature"] ["SignedInfo"] ["Reference"]["DigestValue"];
				$responseCode = $xml_decode['DocumentResponse']['Response']['ResponseCode'];
				$description = $xml_decode['DocumentResponse']['Response']['Description'];

			if($responseCode == 0){
				$estadofe = '1';
			}else{
				$estadofe = $responseCode;
			}
                
					

                echo  '<div class="btnsuccess">'.$description.' por Sunat</div>';

				$this->codrespuesta = $estadofe;				
				
            }
            else {
				
                $estadofe = '2';
                $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                //LOG DE TRAX ERRORES DB
				$code= preg_replace('/[^0-9]/', '', $codigo); 
				if ($code >= 2000 && $code <= 3999) {
				$this->coderror = $codigo;
				$this->mensajeError = $mensaje;
				$this->codrespuesta = $estadofe;
				$this->code = $code;
				}else{
				 // echo 'Ocurrio un error con código: ' . $codigo . ' Msje:' . $mensaje;
				$this->coderror = '';
				$this->mensajeError = '';
				$this->codrespuesta = 3;
				$this->code = $code;
				}
               
            }
        }
        else { //Problemas de comunicacion
            $estadofe = "3";
            //LOG DE TRAX ERRORES DB
            echo curl_error($ch);
			echo "<script>
			Swal.fire({
				title: 'Existe un problema de conexión',
				text: '¡OJO!',
				html: `<h4>El comprobante ya fue registrado y se encuentra en <a href='ventas'>Administrar ventas</a>, puede enviarlo cuando se restablezca su conexión</h4>`,
				icon: 'warning',			
				showCancelButton: true,
				showConfirmButton: false,
				allowOutsideClick: false,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cerrar',
			})
			</script>";
         
			$this->codrespuesta = $estadofe;
        }
        
        curl_close($ch);
        
        //ENVIAR EL ZIP A LOS WS DE SUNAT - FIN
        
    }
    public function EnviarGuiaRemision($emisor, $nombre, $ruta_archivo_xml, $ruta_archivo_cdr, $rutacertificado=null)
    {
		
		if($emisor['modo'] == 'n'){
			$usuario_sol = $emisor['usuario_prueba'];
			$clave_sol = $emisor['clave_prueba'];
			$certificado = $emisor['certificado_prueba'];
			$wsS = 'https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService';
			$pass_certificado = 'ceti';
			

		}
		if($emisor['modo'] == 's'){
			$usuario_sol = $emisor['usuario_sol'];
			$clave_sol = $emisor['clave_sol'];
			$certificado = $emisor['certificado'];
			$wsS = 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService?wsdl';
			$pass_certificado = $emisor['clave_certificado'];
			
		}
		
        $objfirma = new Signature();
        $flg_firma = 0; //Posicion del XML: 0 para firma
        // $ruta_xml_firmar = $ruta . '.XML'; //es el archivo XML que se va a firmar
        $ruta = $ruta_archivo_xml . $nombre . '.XML';

        $ruta_firma = $rutacertificado. 'api/certificado/'.$certificado; //ruta del archivo del certicado para firmar
        $pass_firma = $pass_certificado;
        
        $resp = $objfirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);
		//firma----------------------------------------------------------------
        // print_r($this->hash = $resp);
        //echo '</br> XML FIRMADO';
        $this->xml = $nombre.'.XML';
        //FIRMAR XML - FIN
        
        //CONVERTIR A ZIP - INICIO
        $zip = new \ZipArchive();

        $nombrezip = $nombre.".ZIP";
        $rutazip = $ruta_archivo_xml .$nombre.".ZIP";
        
        if($zip->open($rutazip, \ZipArchive::CREATE) === TRUE)
        {
            $zip->addFile($ruta, $nombre.'.XML');
            $zip->close();
        }
        
       // echo '</br>XML ZIPEADO';
        
        //CONVERTIR A ZIP - FIN
        
        
        //ENVIAR EL ZIP A LOS WS DE SUNAT - INICIO
        $ws = $wsS; //ruta del servicio web de pruebad e SUNAT para enviar documentos
        
        $ruta_archivo = $rutazip;
		$nombre_archivo = $nombrezip;

        $contenido_del_zip = base64_encode(file_get_contents($ruta_archivo)); //codificar y convertir en texto el .zip
        
        //echo '</br> '. $contenido_del_zip;
        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                        <soapenv:Header>
                        <wsse:Security>
                            <wsse:UsernameToken>
                                <wsse:Username>'.$emisor['ruc'].$usuario_sol.'</wsse:Username>
                                <wsse:Password>'.$clave_sol.'</wsse:Password>
                            </wsse:UsernameToken>
                        </wsse:Security>
                        </soapenv:Header>
                        <soapenv:Body>
                        <ser:sendBill>
                            <fileName>'.$nombre_archivo.'</fileName>
                            <contentFile>'.$contenido_del_zip.'</contentFile>
                        </ser:sendBill>
                        </soapenv:Body>
                    </soapenv:Envelope>';
        
            $header = array(
                "Content-type: text/xml; charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: ",
                "Content-lenght: ".strlen($xml_envio)
                );
    
        $ch = curl_init(); //iniciar la llamada
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
        curl_setopt($ch,CURLOPT_URL, $ws);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        
        //para ejecutar los procesos de forma local en windows
        //enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem"); //solo en local, si estas en el servidor web con ssl comentar esta línea
       if(curl_error($ch) === false){
		   echo "Error: " . curl_error($ch);
		}else{
		   $response = curl_exec($ch);
	   }				
        // ejecucion del llamado y respuesta del WS SUNAT.
    
        $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE); // objten el codigo de respuesta de la peticion al WS SUNAT
	
        $estadofe = "0"; //inicializo estado de operación interno
        
        if($httpcode == 200)//200: La comunicacion fue satisfactoria
        {
            $doc = new \DOMDocument();//clase que nos permite crear documentos XML
            $doc->loadXML($response); //cargar y crear el XML por medio de text-xml response
        
            if(isset( $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue ) ) // si en la etique de rpta hay valor entra
            {
				
                $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue; //guadarmos la respuesta(text-xml) en la variable 
			
                $cdr = base64_decode($cdr); //decodificando el xml
                file_put_contents($ruta_archivo_cdr . 'R-' . $nombrezip, $cdr ); //guardo el CDR zip en la carpeta cdr
				
				$this->cdrb64 = "R-".$nombrezip;

                $zip = new \ZipArchive();
                if($zip->open($ruta_archivo_cdr. 'R-' . $nombrezip ) === true ) //rpta es identica existe el archivo
                {
                    $zip->extractTo($ruta_archivo_cdr, 'R-' . $nombre . '.XML');
                    $zip->close();

					$this->xmlb64 = "R-".$nombre.'.XML';					
					
                }
				
				$xml_decode = file_get_contents($ruta_archivo_cdr.'R-' . $nombre . '.XML') or die("Error: Cannot create object");
				$xml_decode = str_replace('<ar:', '<cac:', $xml_decode);
				$xml_decode = str_replace('</ar:', '</cac:', $xml_decode);
				$xml_decode = str_replace('<cbc:', '<cac:', $xml_decode);
				$xml_decode = str_replace('</cbc:', '</cac:', $xml_decode);
				$xml_decode = str_replace('<ar:', '<', $xml_decode);
				$xml_decode = str_replace('</ar:', '</', $xml_decode);
				$xml_decode = str_replace('<cac:', '<', $xml_decode);
				$xml_decode = str_replace('</cac:', '</', $xml_decode);
				$xml_decode = str_replace('<ext:', '<', $xml_decode);
				$xml_decode = str_replace('</ext:', '</', $xml_decode);
				$xml_decode = simplexml_load_string(utf8_encode($xml_decode));
				// $xml_decode = json_decode(json_encode((array)$xml_decode), true);
			
			function xmlarray ($xmlObject, $out = array () )
			{
				foreach ( (array) $xmlObject as $index => $node )
					$out[$index] = ( is_object ( $node ) ) ? xmlarray ( $node ) : $node;

				return $out;
			}
		    	$xml_decode = xmlarray($xml_decode);

				$cod_hash = $xml_decode["UBLExtensions"] ["UBLExtension"]["ExtensionContent"]["Signature"] ["SignedInfo"] ["Reference"]["DigestValue"];
				$responseCode = $xml_decode['DocumentResponse']['Response']['ResponseCode'];
				$description = $xml_decode['DocumentResponse']['Response']['Description'];

			if($responseCode == 0){
				$estadofe = '1';
			}else{
				$estadofe = $responseCode;
			}
                
					
                echo  '<div class="btnsuccess">'.$description.' por Sunat</div>';

				$this->codrespuesta = $estadofe;				
				
            }
            else {
				
                $estadofe = '2';
                $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                
				$code= preg_replace('/[^0-9]/', '', $codigo); 
				if ($code >= 2000 && $code <= 3999) {
				$this->coderror = $codigo;
				$this->mensajeError = $mensaje;
				$this->codrespuesta = $estadofe;
				$this->code = $code;
				}else{
				 // echo 'Ocurrio un error con código: ' . $codigo . ' Msje:' . $mensaje;
				$this->coderror = '';
				$this->mensajeError = '';
				$this->codrespuesta = 3;
				$this->code = $code;
				}
			
               
            }
        }
        else { //Problemas de comunicacion
            $estadofe = "3";
            //LOG DE TRAX ERRORES DB
            echo curl_error($ch);
			echo "<script>
			Swal.fire({
				title: 'Existe un problema de conexión',
				text: '¡OJO!',
				html: `<h4>El comprobante ya fue registrado y se encuentra en <a href='ventas'>Guía de Remisión - Listar Guías de Remisión </a>, puede enviarlo cuando se restablezca su conexión</h4>`,
				icon: 'warning',			
				showCancelButton: true,
				showConfirmButton: false,
				allowOutsideClick: false,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cerrar',
			})
			</script>";
         
			$this->codrespuesta = $estadofe;
        }
        
        curl_close($ch);
        
        //ENVIAR EL ZIP A LOS WS DE SUNAT - FIN
        
    }

	public function EnviarResumenComprobantes($emisor,$nombrexml, $ruta_archivo_xml, $rutacertificado=null)
	{
		if($emisor['modo'] == 'n'){
			$usuario_sol = $emisor['usuario_prueba'];
			$clave_sol = $emisor['clave_prueba'];
			$certificado = $emisor['certificado_prueba'];
			$wsS = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
			$pass_certificado = 'ceti';

		}
		if($emisor['modo'] == 's'){
			$usuario_sol = $emisor['usuario_sol'];
			$clave_sol = $emisor['clave_sol'];
			$certificado = $emisor['certificado'];
			$wsS = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl';
			$pass_certificado = $emisor['clave_certificado'];
		}
		//firma del documento
		$objSignature = new Signature();

		$flg_firma = "0";
		//$ruta_archivo_xml = "xml/";
		$ruta = $ruta_archivo_xml.$nombrexml.'.XML';

		$ruta_firma = $rutacertificado."api/certificado/".$certificado;
		$pass_firma = $pass_certificado;

		$resp = $objSignature->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);

		//print_r($resp); //hash

		//Generar el .zip

		$zip = new \ZipArchive();

		$nombrezip = $nombrexml.".ZIP";
		$rutazip = $ruta_archivo_xml.$nombrexml.".ZIP";

		if($zip->open($rutazip,\ZIPARCHIVE::CREATE)===true){
			$zip->addFile($ruta, $nombrexml.'.XML');
			$zip->close();
		}


		//Enviamos el archivo a sunat

		$ws = $wsS;

		$ruta_archivo = $rutazip;
		$nombre_archivo = $nombrezip;
		// $ruta_archivo_cdr = "cdr/";

		$contenido_del_zip = base64_encode(file_get_contents($ruta_archivo));


		$xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
				 <soapenv:Header>
				 	<wsse:Security>
				 		<wsse:UsernameToken>
				 			<wsse:Username>'.$emisor['ruc'].$usuario_sol.'</wsse:Username>
				 			<wsse:Password>'.$clave_sol.'</wsse:Password>
				 		</wsse:UsernameToken>
				 	</wsse:Security>
				 </soapenv:Header>
				 <soapenv:Body>
				 	<ser:sendSummary>
				 		<fileName>'.$nombre_archivo.'</fileName>
				 		<contentFile>'.$contenido_del_zip.'</contentFile>
				 	</ser:sendSummary>
				 </soapenv:Body>
				</soapenv:Envelope>';


			$header = array(
						"Content-type: text/xml; charset=\"utf-8\"",
						"Accept: text/xml",
						"Cache-Control: no-cache",
						"Pragma: no-cache",
						"SOAPAction: ",
						"Content-lenght: ".strlen($xml_envio)
					);


			$ch = curl_init();
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);
			curl_setopt($ch,CURLOPT_URL,$ws);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
			curl_setopt($ch,CURLOPT_TIMEOUT,30);
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$xml_envio);
			curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
			//para ejecutar los procesos de forma local en windows
			//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");


			$response = curl_exec($ch);

			$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
			$estadofe = "0";

			$ticket = "0";
			if($httpcode == 200){
				$doc = new \DOMDocument();
				$doc->loadXML($response);

				if (isset($doc->getElementsByTagName('ticket')->item(0)->nodeValue)) {
	                $ticket = $doc->getElementsByTagName('ticket')->item(0)->nodeValue;
					echo "NÚMERO DE TICKET: ".$ticket;
					$this->ticketS = $ticket;
				}else{		

					$codigo = $doc->getElementsByTagName("faultcode")->item(0)->nodeValue;
					$mensaje = $doc->getElementsByTagName("faultstring")->item(0)->nodeValue;
					echo "error ".$codigo.": ".$mensaje; 
				}

			}else{
				echo curl_error($ch);
				echo "Problema de conexión";
			}

			curl_close($ch);
			return $ticket;
			

	}

    public function ConsultarTicket($emisor, $cabecera, $nombrexml, $ticket, $ruta_archivo_xml, $ruta_archivo_cdr, $datos_comprobante)
    {
		if($emisor['modo'] == 'n'){
			$usuario_sol = $emisor['usuario_prueba'];
			$clave_sol = $emisor['clave_prueba'];
			$certificado = $emisor['certificado_prueba'];
			$wsS = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
			$pass_certificado = 'ceti';

		}
		if($emisor['modo'] == 's'){
			$usuario_sol = $emisor['usuario_sol'];
			$clave_sol = $emisor['clave_sol'];
			$certificado = $emisor['certificado'];
			$wsS = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl';
			$pass_certificado = $emisor['clave_certificado'];
		}

		$ws = $wsS;
		$nombre	= $nombrexml;
		$nombre_xml	= $nombre.".XML";

		//===============================================================//
		//FIRMADO DEL cpe CON CERTIFICADO DIGITAL
		$objSignature = new Signature();
		$flg_firma = "0";
		$ruta = $ruta_archivo_xml.$nombre_xml;
		$this->xml = $nombre_xml;

		$ruta_firma = "api/certificado/".$certificado;
		$pass_firma = $pass_certificado;

		//===============================================================//

		//ALMACENAR EL ARCHIVO EN UN ZIP
		$zip = new \ZipArchive();
		$nombrezip = $nombrexml.".ZIP";
		$rutazip = $ruta_archivo_xml.$nombrexml.".ZIP";

		if($zip->open($rutazip, \ZIPARCHIVE::CREATE)===true){
			$zip->addFile($ruta, $nombre_xml);
			$zip->close();
		}

		//===============================================================//

		//ENVIAR ZIP A SUNAT
		$ruta_archivo = $rutazip;
		$nombre_archivo = $nombrezip;
		//$ruta_archivo_cdr = "cdr/";

		//$contenido_del_zip = base64_encode(file_get_contents($ruta_archivo.'.ZIP'));
		//FIN ZIP

		$xml_envio = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                    <wsse:Username>'.$emisor['ruc'].$usuario_sol.'</wsse:Username>
                    <wsse:Password>'.$clave_sol.'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:getStatus>
                    <ticket>' . $ticket . '</ticket>
                </ser:getStatus>
            </soapenv:Body>
        </soapenv:Envelope>';


		$header = array(
					"Content-type: text/xml; charset=\"utf-8\"",
					"Accept: text/xml",
					"Cache-Control: no-cache",
					"Pragma: no-cache",
					"SOAPAction: ",
					"Content-lenght: ".strlen($xml_envio)
				);


		$ch = curl_init();
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);
		curl_setopt($ch,CURLOPT_URL,$ws);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
		curl_setopt($ch,CURLOPT_TIMEOUT,120);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml_envio);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		//para ejecutar los procesos de forma local en windows
		//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
		curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");

		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

		echo "codigo:".$httpcode;

		if($httpcode == 200){
			$doc = new \DOMDocument();
			$doc->loadXML($response);

			if(isset($doc->getElementsByTagName('content')->item(0)->nodeValue)){
				$cdr = $doc->getElementsByTagName('content')->item(0)->nodeValue;
				$cdr = base64_decode($cdr);				
				file_put_contents($ruta_archivo_cdr."R-".$nombre_archivo, $cdr);
				$this->cdrb64 = "R-".$nombrezip;
			
				
				$zip = new \ZipArchive;
				if($zip->open($ruta_archivo_cdr."R-".$nombre_archivo)===true){
					$zip->extractTo($ruta_archivo_cdr,'R-'.$nombrexml.'.XML');
					$zip->close();

					$this->xmlb64 = "R-".$nombrexml.'.XML';

				}

				$xml_decode = file_get_contents($ruta_archivo_cdr.'R-' . $nombre . '.XML') or die("Error: Cannot create object");
				$xml_decode = str_replace('<ar:', '<cac:', $xml_decode);
				$xml_decode = str_replace('</ar:', '</cac:', $xml_decode);
				$xml_decode = str_replace('<cbc:', '<cac:', $xml_decode);
				$xml_decode = str_replace('</cbc:', '</cac:', $xml_decode);
				$xml_decode = str_replace('<ar:', '<', $xml_decode);
				$xml_decode = str_replace('</ar:', '</', $xml_decode);
				$xml_decode = str_replace('<cac:', '<', $xml_decode);
				$xml_decode = str_replace('</cac:', '</', $xml_decode);
				$xml_decode = str_replace('<ext:', '<', $xml_decode);
				$xml_decode = str_replace('</ext:', '</', $xml_decode);
				$xml_decode = simplexml_load_string(utf8_encode($xml_decode));
				// $xml_decode = json_decode(json_encode((array)$xml_decode), true);
			
			function xmlarray ($xmlObject, $out = array () )
			{
				foreach ( (array) $xmlObject as $index => $node )
					$out[$index] = ( is_object ( $node ) ) ? xmlarray ( $node ) : $node;

				return $out;
			}
		    	$xml_decode = xmlarray($xml_decode);

				$cod_hash = $xml_decode["UBLExtensions"] ["UBLExtension"]["ExtensionContent"]["Signature"] ["SignedInfo"] ["Reference"]["DigestValue"];
				$responseCode = $xml_decode['DocumentResponse']['Response']['ResponseCode'];
				$description = $xml_decode['DocumentResponse']['Response']['Description'];

			if($responseCode == 0){
				$estadofe = '1';
			}else{
				$estadofe = $responseCode;
			}
                
					

                echo  '<div class="btnsuccess">'.$description.' por Sunat</div>';
				
				$this->codrespuesta = $estadofe;
			}else{		
				$estadofe = '2';
				$codigo = $doc->getElementsByTagName("faultcode")->item(0)->nodeValue;
				$mensaje = $doc->getElementsByTagName("faultstring")->item(0)->nodeValue;
				echo "error ".$codigo.": ".$mensaje; 

				$this->coderror = $codigo;
				$this->mensajeError = $mensaje;
				$this->codrespuesta = $estadofe;
			}

		}else{
			$estadofe = '3';
			echo curl_error($ch);
			echo "Problema de conexión";
			$this->codrespuesta = $estadofe;
		}

		curl_close($ch);
	}

    function consultarComprobante($emisor, $comprobante)
    {
		
		try{
			if($emisor['modo'] == 'n'){
			$usuario_sol = $emisor['usuario_prueba'];
			$clave_sol = $emisor['clave_prueba'];
			$certificado = $emisor['certificado_prueba'];
			$wsS = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';

		}
		if($emisor['modo'] == 's'){

			$usuario_sol = $emisor['usuario_sol'];
			$clave_sol = $emisor['clave_sol'];
			$certificado = $emisor['certificado'];
			// $wsS = 'https://ww1.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';
			$wsS = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';
			

		}
				$ws = $wsS;
				$soapUser = "";  
				$soapPassword = "";

				$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
				xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
					<soapenv:Header>
						<wsse:Security>
							<wsse:UsernameToken>
								<wsse:Username>'.$emisor['ruc'].$usuario_sol.'</wsse:Username>
								<wsse:Password>'.$clave_sol.'</wsse:Password>
							</wsse:UsernameToken>
						</wsse:Security>
					</soapenv:Header>
					<soapenv:Body>
						<ser:getStatus>
							<rucComprobante>'.$emisor['ruc'].'</rucComprobante>
							<tipoComprobante>'.$comprobante['tipocomp'].'</tipoComprobante>
							<serieComprobante>'.$comprobante['serie'].'</serieComprobante>
							<numeroComprobante>'.$comprobante['correlativo'].'</numeroComprobante>
						</ser:getStatus>
					</soapenv:Body>
				</soapenv:Envelope>';
				
			
			
				$headers = array(
					"Content-type: text/xml;charset=\"utf-8\"",
					"Accept: text/xml",
					"Cache-Control: no-cache",
					"Pragma: no-cache",
					"SOAPAction: ",
					"Content-length: " . strlen($xml_post_string),
				); 			
			
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_URL, $ws);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
				//para ejecutar los procesos de forma local en windows
				//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
				curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");

				$response = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				var_dump($response);
				
			} catch (\Exception $e) {
				echo "SUNAT ESTA FUERA SERVICIO: ".$e->getMessage();
			}
    }
}

?>