<!-- <div id="back"></div> -->

<div class="log-cont">
<div class="login-box">
  <!-- <div class="login-logo">
   <img src="vistas/img/plantilla/logo-blanco-bloque.png" class="img-responsive" alt="" style="padding: 30px 100px 0px 100px">
  </div> -->
  <!-- /.login-logo -->
  <div class="login-box-body">

    <div class="logo-empresa">
      <?php  $rand = rand(22,99999); ?>
      <img src="vistas/img/logo/logo.png?n='<?php echo $rand; ?>" alt="">
    </div>

    <p class="login-box-msg" style="display: none"></p>

    <form  method="post" class="login-u" id="form-login">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="Usuario" name="ingUsuario" id="ingUsuario" autocomplete="off">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback" style="margin:0px !important">
        <input type="password" class="form-control" placeholder="Contraseña" name="ingPassword" id="ingPassword">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        <!-- <div class="g-recaptcha" id="idrecaptcha" data-sitekey="6Lf4WrAZAAAAANIZPtMaCIhXbbgFoVnfNs_u8Ryo"></div> -->
      </div>     

      <div class="row">

     
        <!-- /.col -->
        <div class="content-fluid" style="background: #fff !important;">
          <button type="button" class="btn-flat" id="logUser">Ingresar al sistema <i class="fas fa-angle-double-right fa-lg"></i></button>
        </div>
        <!-- /.col -->
      </div>

      <?php
      
      // $login = new ControladorUsuarios();
      // $login->ctrIngresoUsuario();
     
      ?>
      <div id="resultLogin"  style="display: none;"></div>
    </form>
    <!-- <div class="social-auth-links text-center">
      <p></p>
      <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i>Ingresa usando 
        Facebook</a>
      
    </div> -->
    <!-- /.social-auth-links -->
    <!-- <div class="link-recuperar">

    <a href="#">¿Olvidaste tu contraseña?</a><br>
    ¿No tienes cuenta?<a href="#" class="text-center"> Regístrate</a>

    </div>     -->

  <div class="verifica-sunat"><img src="vistas/img/verificacion.png" alt=""></div>
  </div>
  <!-- /.login-box-body -->
</div>
</div>
<div id="fondP">
<div class="fnd"></div>
</div>
<script src="https://www.google.com/recaptcha/api.js?render=6LdTdcggAAAAAPzue7S6tJumtvWlWCS_Pa1kxPVE"></script>