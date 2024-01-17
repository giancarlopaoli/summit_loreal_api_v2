<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Billex</title>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap");

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
        width: 100%;
        height: 1px;
        margin: 0 auto;
        border-bottom: 1px solid #E6E8F4;
      }

      .line-full {
        width: 100%;
        height: 1px;
        margin: 10px auto 0 auto;
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
        font-size: 14px;
        font-weight: 400;
        margin: 0;
        color: #000541;
        width: 98%;
        margin: 20px 0 5px 0;
      }

      .information_details {
        width: 85%;
        display: flex;
        margin: 0 auto 20px auto;
        background-color: #F6F9FF;
        border-radius: 10px;
        padding: 20px 30px;
        border-radius: 10px;
      }

      .information_details h4,.information_details p {
        font-size: 12px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
      }

      .information_details h4 {
        font-weight: 400;
        margin-bottom: 5px;
      }

      .information_details span {
        margin-left: 10px;
      }

      .information_details_item {
        margin: 0 auto;
      }

      .information_details_item p{
        margin-top: 10px;
      }

      .information_details_item_line {
        width: 1px;
        margin: 0 20px;
        height: 40px;
        border-right: 1px dashed #C7D1F9;
      }
      .information_details_item.hour {
        display: flex;
        align-items: center;
        margin-top: 22px;
        margin-left: 20px;
      }

      .information_details_item span {
        margin-left: 5px;
        margin-top: 2.5px;
        font-weight: 600;
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
        @if ( $executive_id == 2339 || $executive_id == 2801 || $executive_id == 2811)
          <img src="https://bill-upload.s3.amazonaws.com/static/img/user_woman.png" alt="">
        @else
          <img src="https://bill-upload.s3.amazonaws.com/static/img/user.png" alt="">
        @endif
        <h1>Estoy para ayudarte</h1>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line"></div>
      </td>
    </tr>
    <tr>
      <td align="center" class="information">
        <h2>¡Hola! {{ $name }}</h2>
        <p>Te saluda {{ $executive_name }}, soy tu ejecutivo de Billex y la persona encargada de tu cuenta.</p>
        <p>Quería darte nuevamente la bienvenida y cortante que te acompañare en cada momento de tus operaciones para ayudarte a conseguir el tipo de cambio más competitivo.</p>
        <p>Por otra parte, te enviaré información relevante como reportes sobre el tipo de cambio, invitaciones a nuestros eventos online y otros contenidos de gran valor para tu negocio.</p>
        <p>Sin otro particular, quedaré atento a cualquier consulta o requerimiento que puedas tener.</p>
      </td>
    </tr>
    <tr>
      <td style="padding-top: 20px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr style="width: 650px;">
      <td class="information_details">
        <div class="information_details_item">
          <h4 style="margin: 5px 0 0 0;">{{ $executive_name }}</h4>
          <p style="margin: 0;">Sectorista Empresarial</p>
        </div>
        <div class="information_details_item_line"></div>
        <div class="information_details_item">
          <p> <img src="https://bill-upload.s3.amazonaws.com/static/img/phone.png" alt=""> <span>{{ $executive_phone }}</span></p>
        </div>
        <div class="information_details_item" style="padding-left: 15px;">
          <p> <img src="https://bill-upload.s3.amazonaws.com/static/img/mail.png" alt="">  <span>{{ $executive_email }}</span></p>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <div class="line-full"></div>
      </td>
    </tr>
    <tr>
      <td style="padding-top: 30px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr>
      <td align="center" style="font-size: 13px;">
        Gracias por utilizar Billex.
      </td>
    </tr>
    <tr>
      <td style="padding-top: 30px;width: 100%;
      background-color: #fff;">
      </td>
    </tr>
    <tr>
      <td style="width: 100%;
      background-color: #F7B825; padding: 0; padding-top: 5px;">
      </td>
    </tr>
    <tr>
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
          ©2023 Billex. Todos los derechos reservados</p>
        <p style="font-size: 11px;
        margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 30px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
      </td>
    </tr>
  </table>
</body>
</html>
