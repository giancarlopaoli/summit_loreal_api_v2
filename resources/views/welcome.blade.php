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
    </style>
  </head>
  <body style="margin: 0; padding: 20px 0; width: 650px; margin: 0 auto;">
    <table cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td style="background-color: #001489;" align="center">
          <img src="https://bill-upload.s3.amazonaws.com/static/img/logo.png" alt="" />
        </td>
      </tr>
      <tr>
        <td>
          <tr style="background-image: url('https://bill-upload.s3.amazonaws.com/static/img/main_background.jpg'); background-size: contain; background-repeat: no-repeat;" width="650px" height="280px">
            <td style="display: flex; flex-direction: column; justify-content: center; padding-top: 75px; padding-left: 80px;">
              <h1 style="font-size: 23px; margin: 0; font-weight: 500; width: 60%; word-wrap: break-word; color: #001489;"><span style="font-weight: 700;">¡Hola!</span> {{$names}}</h1>
            </td>
            <td style="display: flex; flex-direction: column; justify-content: center; padding-left: 80px;">
              <p style="font-size: 14px; margin: 0;">Te damos la bienvenida a Billex.</p>
            </td>
          </tr>
          <tr>
            <td style="text-align: center;">
              <h2 style="font-size: 13px; font-weight: 500; margin-bottom: 5px;">Te registramos en Billex con el correo electrónico</h2>
              <h1 style="max-width: 650px; word-wrap: break-word; font-weight: 600; font-size: 16px; color: #2c54dc; margin: 5px 0 30px 0;">{{$email}}</h1>
              @if ( !is_null($company))
              <p style="font-size: 13px;">
                Gracias por registrar a la empresa {{$company}}
              </p>
              @else
              <p style="font-size: 13px;">
                Gracias por registrarte en Billex, estas a un paso de pertenecer a nuestra comunidad. <br />
              </p>
              @endif
              <p style="font-size: 13px;">
                En estos momentos estamos revisando tus datos para seguridad de ambas partes. En breve te comunicaremos cuando tu cuenta esté 100% activa para que empieces a vivir la experiencia Billex.
              </p>
              <p style="font-size: 13px; margin-top: 20px;">
                Además, en el adjunto de este correo encontrarás tu contrato y el manual de usuario <br />
                de la plataforma. Gracias por confiar en Billex.
              </p>
            </td>
          </tr>
        </td>
      </tr>
            <tr>
                <td style="padding-top: 10px;width: 100%;
                background-color: #fff;">
                </td>
            </tr>
            <tr>
                <td style="border: 1px dashed #2C54DC;
                text-align: left;
                border-radius: 8px;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 15px 60px;
                margin: 20px auto;
                width: 62%;">
                    <img src="https://bill-upload.s3.amazonaws.com/static/img/warning.jpg" alt="" width="20" height="20">
                    <p style="font-size: 9.9px;
                    margin: 0 10px;
                    font-weight: 500;">Si no deseas registrarte o no has solicitado tu registro, te pedimos hacer <br> caso omiso a este correo.</p>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 10px;width: 100%;
                background-color: #fff;">
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;width: 100%;
                background-color: #F7B825;">
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

            <!-- <tr>
              <td align="center" style="background-color: #001489; padding: 30px 0 0 0;">
                <a href="https://www.linkedin.com/company/billex-divisas/" target="_blank">
                <img src="https://www.billex.pe/wp-content/uploads/2020/10/linkedin-icon.png" alt="Linkedin" width="32" height="32" style="display: block;" border="0" target="_blank" />
                  </a>

                <a href="https://www.youtube.com/channel/UC0hRfNpJN-1aX_VoaKa-ZVg/featured" target="_blank">
                <img src="https://www.billex.pe/wp-content/uploads/2020/10/youtube-icon.png" alt="Youtube" width="32" height="32" style="display: block;" border="0" target="_blank" />
                  </a>

                <a href="https://www.facebook.com/BillexDivisas/" target="_blank">
                  <img src="https://www.billex.pe/wp-content/uploads/2020/10/facebook-icon.png" alt="Facebook" width="32" height="32" style="display: block;" border="0" />
                  </a>
              </td>
            </tr> -->

            <tr>
                <td style="background-color: #001489; width: 100%; height: 100%; padding: 0 0 10px 0;">
                    <p style="font-size: 11px;
                    margin: 0; text-align: center; color: #fff; margin: 20px 0;">Este mensaje es solo informativo, favor de no responder a este correo. <br>
                        ©2022 Billex. Todos los derechos reservados</p>
                    <p style="font-size: 11px;
                    margin: 0; text-align: center; color: #fff; margin: 20px 0; margin-top: 40px;">Si tienes alguna duda ó pregunta, encuentra la información en nuestra <a href=""style="color: #fff;">página de ayuda</a>  o <a href=""style="color: #fff;">contáctenos</a></p>
                </td>
            </tr>
    </table>
  </body>
</html>
