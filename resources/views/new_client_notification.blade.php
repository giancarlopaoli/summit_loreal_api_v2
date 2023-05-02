<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml">
 
<head>
 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 
<title>Billex</title>
 
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
 
</head>

<body style="margin: 0; padding: 0;">
 
	<table cellpadding="0" cellspacing="0" width="100%">
		<tr>
 			<td style="padding: 20px 0 30px 0;">
  				<table align="center" 
  						cellpadding="0" 
  						cellspacing="0" 
  						width="650" 
  						style="border-collapse: collapse;">
					
  					<!-- {HEADER} -->

					<tr>
						<td>
				 			<img src="https://www.billex.pe/wp-content/uploads/2020/10/legal.jpg" alt="Creating Email Magic" width="650" height="290" style="display: block;" />

						</td>
				 	</tr>

				 	<!-- {BODY} -->

				 	<tr>
						<td bgcolor="#ffffff" style="padding: 50px 50px 50px 50px;">
				 			
				 			<table cellpadding="0" cellspacing="0" width="100%">
								<tr>
							 		<td style="font-family: 'Roboto', sans-serif; 
							 					font-size: 16px;
							 					color: #4a4a4a; 
							 					text-align: left;">
							 
							  			Hola Equipo Billex,

							  			<p>El cliente {{ $bussiness_name }} se registró en la plafaforma. {{ $register_message }}</p>

							  			<p>Los datos del contacto son:</p>
							  			<p><li>Nombre: {{ $contact_name }}</li></p>
							  			<p><li>Teléfono: {{ $contact_phone }}</li></p>
							  			<p><li>Email: {{ $contact_email }}</li></p>
							  			@if (isset($ejecutivo_referido))
							  			<p><li>Ejecutivo referido: {{ $ejecutivo_referido->name }} {{ $ejecutivo_referido->last_name }}</li></p>
							  			@endif
							  			<p><li>Ejecutivo asignado: {{ $ejecutivo_asignado }}</li></p>
							 
							 		</td>
								</tr>
								
								<tr>
							 		<td style="font-family: 'Roboto', sans-serif; 
							 					font-size: 16px;
							 					color: #4a4a4a; 
							 					text-align: left;">


										<p>Plataforma Billex.</p>	
							 
							 		</td>
								</tr>
							 </table>

						</td>
				 	</tr>


				 	<!-- {FOOTER} -->

				 	<tr>
						<td bgcolor="#001489" 
							style="padding: 30px 0px;">
				 			
							<table align="center" 
									cellpadding="0" 
									cellspacing="0" 
									width="300px">

								<tr>
									<td>
										
										<table border="0" 
												cellpadding="0" 
												cellspacing="0" 
												align="center">

											<tr>
												<td>
													<a href="https://www.linkedin.com/company/billex-divisas/" target="_blank">
 													<img src="https://www.billex.pe/wp-content/uploads/2020/10/linkedin-icon.png" alt="Linkedin" width="32" height="32" style="display: block;" border="0" target="_blank" />
  													</a>
												</td>

												<td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>

												<td>
													<a href="https://www.youtube.com/channel/UC0hRfNpJN-1aX_VoaKa-ZVg/featured" target="_blank">
 													<img src="https://www.billex.pe/wp-content/uploads/2020/10/youtube-icon.png" alt="Youtube" width="32" height="32" style="display: block;" border="0" target="_blank" />
  													</a>
												</td>

												<td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>

												<td>
													<a href="https://www.facebook.com/BillexDivisas/" target="_blank">
 														<img src="https://www.billex.pe/wp-content/uploads/2020/10/facebook-icon.png" alt="Facebook" width="32" height="32" style="display: block;" border="0" />
  													</a>
												</td>
											</tr>
												
										</table>

									</td>
								</tr>
								<tr>
									<td style="font-family: 'Roboto', sans-serif;
							 					color: #ffffff; 
							 					text-align: center; 
							 					padding-top: 30px;">

										<p style="font-size: 14px;">
										&copy; 2021 Bill Financial Services S.A.</p>
										
										<p style="font-size: 12px;">
										Av. Alfredo Benavides 1944 piso 9, Miraflores, Lima Perú.</p>

									</td>
								</tr>
							</table>

						</td>
				 	</tr>
 
				</table>
 			</td>
		</tr>
 	</table>
 
</body>

</html>