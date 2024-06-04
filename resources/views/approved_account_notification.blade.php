<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Billex</title>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");

      body {
        font-family: "Poppins";
        font-size: 16px;
        line-height: 1.5;
        margin: 0;
        padding: 0;
        color: #333333;
      }
            * {
                font-family: "Poppins";
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
      margin: 0 0 20px 0;
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

    .information p {
      font-size: 12px;
      font-weight: 400;
      margin: 0;
      color: #000541;
      width: 70%;
      margin: 25px 0 0 0;
    }

    .buttons{
      margin: 0 auto;
    }

    .buttons button {
      padding: 11px 45px;
      margin: 30px 5px;
      border-radius: 8px;
      border: 0;
      cursor: pointer;
    }

    .buttons a button{
      background-color: #F7B825;
      color: #fff;
      cursor: pointer;
    }

    </style>
  </head>
  <body style="margin: 0; padding: 20px 0; width: 650px; margin: 0 auto;">
    <table cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td class="header" align="center">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/logo.png" alt="" />
        </td>
      </tr>
      <tr>
        <td align="center" class="check">
          <img src='https://bill-upload.s3.amazonaws.com/static/img/account.png' alt="">
          <h1>Cuenta verificada con éxito.</h1>
        </td>
      </tr>
      <tr>
        <td>
          <div class="line"></div>
        </td>
      </tr>
      <tr>
        <td align="center" class="information">
          <p>Hola {{$client_name}}</p>
          <p>¡Felicidades! La cuenta bancaria que registraste ha sido verificada.</p>
          <p>Puedes revisar el estado de tus cuentas bancarias desde la plataforma Billex.</p>
        </td>
      </tr>
      
      <tr>
        <td class="buttons" align="center">
          <a href="https://operaciones.billex.pe"><button>¡Quiero empezar ahora!</button></a>
        </td>
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
          <td align="center" style="background-color: #001489; padding: 30px 0 0 0;">
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
                  ©2023 Billex. Todos los derechos reservados</p>
              <p style="font-size: 11px;
              margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 40px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
          </td>
      </tr>
    </table>
  </body>
</html>
