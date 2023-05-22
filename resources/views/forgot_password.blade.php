<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billex</title>
  <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet' type='text/css'>
  <style type="text/css">
    body {
      font-family: "Poppins",sans-serif;
      font-size: 16px;
      line-height: 1.5;
      margin: 0;
      padding: 0;
      color: #333333;
    }
    * {
      font-family: "Poppins", sans-serif;
    }

    .header {
      background-color: #001489;
    }

    .check {
      padding: 30px 0 10px 0;
    }

    .check h1 {
      font-size: 22px;
      color: #001489;
      font-weight: 600;
    }

    .line {
      width: 90%;
      height: 1px;
      margin: 0 auto;
      border-bottom: 1px solid #E6E8F4;
    }

    .line-full {
      width: 100%;
      height: 1px;
      margin: 0 auto;
      border-bottom: 1px solid #E6E8F4;
    }

    .information h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 8px;
      margin-top: 30px;
      color: #2C54DC;
    }
    
    .information p {
      font-size: 12px;
      font-weight: 400;
      margin: 0;
      color: #000541;
      width: 70%;
    }

    .information_details {
      width: 30%;
      display: flex;
      margin: 10px auto;
      background-color: #F6F9FF;
      border-radius: 10px;
      padding: 10px 30px;
      border-radius: 10px;
    }

    .information_details h4,.information_details p {
      font-size: 12px;
      font-weight: 400;
      margin: 0 auto;
      display: flex;
      align-items: center;
      text-align: center;
    }

    .information_details p {
      font-weight: 500;
      margin-bottom: 5px;
      text-align: center;
      font-size: 22px;
    }

    .information_details {
      vertical-align: middle;
      color: #001489;
      font-size: 22px;
      font-weight: 500;
      text-align: center;
    }


    .advice {
      text-align: center;
      padding: 10px 0;
      border: 1px dashed #2C54DC;
      border-radius: 8px;
      width: 90%;
      margin: 30px 20px 20px 20px;
      display: flex;
      align-content: center;
    }

    .advice img {
      margin-left: 32px;
    }
    
    .advice p {
      font-size: 10.5px;
      margin: 0 5px;
      margin-top: 3.1px;
      align-items: center;
      text-align: center;
    }



    .buttons{
      margin: 0 auto;
    }

    .buttons button {
      padding: 8px 25px;
      margin: 0 5px;
      border-radius: 8px;
      border: 0;
      cursor: pointer;
    }
    
    .buttons a:first-child  button{
      background-color: #F7B825;
      color: #fff;
    }
    
    .buttons a:last-child button {
      border: 1px solid #001489;
      background-color: transparent;
      color: #001489;
      font-weight: 600;
    }

  </style>
</head>
<body>
  <table style="margin: 0; padding: 20px 40px; width: 650px; margin: 0 auto; background-color: #fff;" cellpadding="0" cellspacing="0">
    <tr>
      <td class="header" align="center">
        <img src="https://bill-upload.s3.amazonaws.com/static/img/logo.png" alt="" />
      </td>
    </tr>
    <tr>
      <td align="center" class="check">
        <img src="https://bill-upload.s3.amazonaws.com/static/img/lock.png" alt="">
        <h1>Te ayudamos a recuperar tu contraseña</h1>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line"></div>
      </td>
    </tr>
    <tr>
      <td align="center" class="information">
        <h2>¡Hola! {{ $names }}</h2>
        <p>¿Se te olvidó tu contraseña? Traquilo, Te compartimos tu nueva <br> contraseña para que puedas seguir disfrutando de los beneficios de cambiar divisisas en nuetrsa plataforma.</p>
      </td>
    </tr>
    <tr>
      <td style="padding-top: 20px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr style="width: 650px;">
      <td class="information_details" align="center">
        <p>{{ $new_password }}</p> 
      </td>
    </tr>
    <tr>
      <td style="padding-top: 20px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr>
      <td class="buttons" align="center">
        <a href=""><button>Reestablecer Contraseña</button></a>
        <a href=""><button>Ir a la web principal</button></a>
      </td>
    </tr>
    <tr>
      <td align="center">
        <div class="advice">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/warning.jpg" alt="" width="20" height="20">
          <p>Te sugerimos cambiar tu contraseña por una propia, desde tu perfil de usuario.</p>
        </div>
      </td>
    </tr>
    <tr>
      <td style="padding-top: 10px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr>
      <td style="width: 100%;
      background-color: #F7B825; padding: 0; padding-top: 5px;">
      </td>
    </tr>
    <tr>
      <!-- <td align="center" style="background-color: #001489; padding: 25px 0 0 0;">
        <a href=""><img src="./images/social1.png" alt="" style="margin: 0 15px;"></a>
        <a href=""><img src="./images/social2.png" alt="" style="margin: 0 15px;"></a>
        <a href=""><img src="./images/social3.png" alt="" style="margin: 0 15px;"></a>
      </td> -->
      <td align="center" style="background-color: #001489; padding: 25px 0 0 0;">
        <a href="https://www.facebook.com/BillexDivisas/" target="_blank">
            <img src="https://bill-upload.s3.amazonaws.com/static/img/social1.png" alt="" width="32" height="32" style="margin: 0 15px;">
        </a>
        <a href="https://www.linkedin.com/company/billex-divisas/" target="_blank">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/social2.png" alt="" width="32" height="32" style="margin: 0 15px;">
        </a>
        <a href="https://www.youtube.com/channel/UC0hRfNpJN-1aX_VoaKa-ZVg/featured" target="_blank">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/social3.png" alt="" width="32" height="32" style="margin: 0 15px;">
        </a>
    </td>
    </tr>
    <tr>
      <td style="background-color: #001489; width: 100%; height: 100%; padding: 0 0 10px 0;">
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0;">Este mensaje es solo informativo, favor de no responder a este correo. <br>
          ©2022 Billex. Todos los derechos reservados</p>
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 30px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
      </td>
    </tr>
  </table>
</body>
</html> 