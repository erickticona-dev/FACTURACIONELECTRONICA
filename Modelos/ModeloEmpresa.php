<?php
namespace Modelos;
use Conect\Conexion;
use PDO;

class ModeloEmpresa{
        // MOSTRAR EMISOR
        public static function mdlMostrarEmisor($tabla, $item, $valor){
        
            if($item != null){
    
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla  WHERE $item = :$item");
                $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
        
                $stmt->execute();
                return $stmt->fetch();
    
            }else{
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
                //$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);    
                $stmt->execute();
                return $stmt->fetchall();
    
            }
           
    
            $stmt->close();
            $stmt = null;
        }
    public static function mdlActualizarDatosEmpresa($datos){

    $stmt = Conexion::conectar()->prepare("UPDATE emisor SET ruc=:ruc, razon_social=:razon_social, nombre_comercial=:nombre_comercial, direccion=:direccion, telefono=:telefono, pais=:pais, departamento=:departamento, provincia=:provincia, distrito=:distrito, ubigeo=:ubigeo, usuario_sol=:usuario_sol, clave_sol=:clave_sol, clave_certificado =:clave_certificado, certificado=:certificado, afectoigv=:afectoigv, correo_ventas=:correo_ventas, correo_soporte=:correo_soporte, servidor=:servidor, contrasena=:contrasena, puerto=:puerto, seguridad=:seguridad, tipo_envio=:tipo_envio, logo=:logo WHERE id=:id");
    $fechaDoc = date("Y-m-d");
    $fechahora = date("Y-m-d H:i:s");
        $stmt->bindParam(":id", $datos['id'], PDO::PARAM_INT);
        $stmt->bindParam(":ruc", $datos['ruc'], PDO::PARAM_STR);
        $stmt->bindParam(":razon_social", $datos['razon_social'], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_comercial", $datos['nombre_comercial'], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos['direccion'], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos['telefono'], PDO::PARAM_STR);
        $stmt->bindParam(":pais", $datos['pais'], PDO::PARAM_STR);
        $stmt->bindParam(":departamento", $datos['departamento'], PDO::PARAM_STR);
        $stmt->bindParam(":provincia", $datos['provincia'], PDO::PARAM_STR);
        $stmt->bindParam(":distrito", $datos['distrito'], PDO::PARAM_STR);
        $stmt->bindParam(":ubigeo", $datos['ubigeo'], PDO::PARAM_STR);
        $stmt->bindParam(":usuario_sol", $datos['usuario_sol'], PDO::PARAM_STR);
        $stmt->bindParam(":clave_sol", $datos['clave_sol'], PDO::PARAM_STR);
        $stmt->bindParam(":clave_certificado", $datos['clave_certificado'], PDO::PARAM_STR);
        $stmt->bindParam(":certificado", $datos['certificado'], PDO::PARAM_STR);
        $stmt->bindParam(":afectoigv", $datos['afectoigv'], PDO::PARAM_STR);
        $stmt->bindParam(":correo_ventas", $datos['correo_ventas'], PDO::PARAM_STR);
        $stmt->bindParam(":correo_soporte", $datos['correo_soporte'], PDO::PARAM_STR);
        $stmt->bindParam(":servidor", $datos['servidor'], PDO::PARAM_STR);
        $stmt->bindParam(":contrasena", $datos['contrasena'], PDO::PARAM_STR);
        $stmt->bindParam(":puerto", $datos['puerto'], PDO::PARAM_STR);
        $stmt->bindParam(":seguridad", $datos['seguridad'], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_envio", $datos['tipo_envio'], PDO::PARAM_STR);
        $stmt->bindParam(":logo", $datos['logo'], PDO::PARAM_STR);
      
    
       if($stmt->execute()){
             return   'ok';
        } else {
            return  'error';
        } 
    
        $stmt->close();
        $stmt = null;
    
    }
    public static function mdlActualizarModoProduccion($item, $valor, $datos){

    $stmt = Conexion::conectar()->prepare("UPDATE emisor SET modo=:modo WHERE $item=:$item");
    
        $stmt->bindParam("".$item, $valor, PDO::PARAM_INT);       
        $stmt->bindParam(":modo", $datos['modo'], PDO::PARAM_STR);
      
    
       if($stmt->execute()){
             return   'ok';
        } else {
            return  'error';
        } 
    
        $stmt->close();
        $stmt = null;
    }    
    public static function mdlActualizarBienesServiciosSelva($item, $valor, $itembs, $valorbs){

    $stmt = Conexion::conectar()->prepare("UPDATE emisor SET $itembs=:$itembs WHERE $item=:$item");
    
        $stmt->bindParam("".$item, $valor, PDO::PARAM_INT);       
        $stmt->bindParam("".$itembs, $valorbs, PDO::PARAM_STR);
      
    
       if($stmt->execute()){
             return   'ok';
        } else {
            return  'error';
        } 
    
        $stmt->close();
        $stmt = null;
    }    
    
     // MOSTRAR MODO PRODUCCIÓN
     public static function mdlMostrarModo() {      
        
        // $open = fopen(dirname(__FILE__)."/config/config.txt","rb"); //abres el fichero en modo lectura/escritura


        // @$config = fgets($open); //recuperas el contenido del fichero
        // return $config;
        
        // fclose($open);//cierras el fichero
        
        
       
    }
    // ACTUALIZAR LOGO EMPRESA============
    public static function mdlCambiarLogo($datos){
        $stmt = Conexion::conectar()->prepare("UPDATE emisor SET logo=:logo WHERE id=:id");
        $stmt->bindParam(":id", $datos['id'], PDO::PARAM_INT);
        $stmt->bindParam(":logo", $datos['logo'], PDO::PARAM_STR);
      
    
       if($stmt->execute()){
             return   'ok';
        } else {
            return  'error';
        } 
    
        $stmt->close();
        $stmt = null;
    } 
    // ACTUALIZAR BIENES Y SERVICIOS SELVA, EMPRESA============
    public static function mdlBienesServicios($item, $valor){
        $stmt = Conexion::conectar()->prepare("UPDATE emisor SET logo=:logo WHERE id=:id");
        $stmt->bindParam(":id", $datos['id'], PDO::PARAM_INT);
        $stmt->bindParam(":logo", $datos['logo'], PDO::PARAM_STR);
      
    
       if($stmt->execute()){
             return   'ok';
        } else {
            return  'error';
        } 
    
        $stmt->close();
        $stmt = null;
    } 
    // ACTUALIZAR LOGO EMPRESA============
    public static function mdlCambiarPlantilla($datos){
        $stmt = Conexion::conectar()->prepare("UPDATE emisor SET plantilla=:plantilla WHERE id=:id");
        $stmt->bindParam(":id", $datos['id'], PDO::PARAM_INT);
        $stmt->bindParam(":plantilla", $datos['plantilla'], PDO::PARAM_STR);
      
    
       if($stmt->execute()){
             return   'ok';
        } else {
            return  'error';
        } 
    
        $stmt->close();
        $stmt = null;
    }

// PASAR A MODO PRODUCCIÓN EL SISTEMA
public static function mdlProduccion() {
    $tabla = "serie";
    $item = null;
    $valor = 0;
    $serie = ModeloEmpresa::mdlMostrarEmisor($tabla, $item, $valor);
   foreach ($serie as $k => $v) {
    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET correlativo=:correlativo");
    $stmt->bindParam(":correlativo", $valor, PDO::PARAM_STR);
  

   if($stmt->execute()){
         return   'ok';
    } else {
        return  'error';
    } 

    $stmt->close();
    $stmt = null;
   }

}
public static function mdlProduccionTablas() {

$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE venta");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE detalle");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE nota_credito");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE nota_credito");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE nota_credito_detalle");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE nota_debito");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE nota_debito_detalle");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE envio_resumen");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE envio_resumen_detalle");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE guia");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE guia");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE guia_detalle");
$stmt->execute();
$stmt = Conexion::conectar()->prepare("TRUNCATE TABLE pago_credito");
$stmt->execute();
 
$stmt = Conexion::conectar()->prepare("UPDATE productos set ventas = :ventas");
    $cantidad = 0 ;
    $stmt->bindParam(":ventas", $cantidad, PDO::PARAM_STR);


   $stmt->execute();


$files = glob(dirname(__FILE__).'/../api/xml/*'); //obtenemos todos los nombres de los ficheros
foreach($files as $file){
    if(is_file($file))
    unlink($file); //elimino el fichero
}
$files = glob(dirname(__FILE__).'/../api/cdr/*'); //obtenemos todos los nombres de los ficheros
foreach($files as $file){
    if(is_file($file))
    unlink($file); //elimino el fichero
}
}
}