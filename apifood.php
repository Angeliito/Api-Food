<?php

    include_once('conexion.php');
	include_once 'library/configapibot.php';


    header('Content-Type: application/json');

    $message = file_get_contents('php://input'); 
    $idtrans = "";
    $metodo  = "";
    $usuario_no= "";
    $file = '';


    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $file = fopen('Gloria_Api.txt','w');
        fwrite($file, 'REGISTROS DE PAGOS');
        //fwrite( $file ,  $message );
        $obj = json_decode($message);
        $count = $obj->count;
        $orders =$obj->orders;
        $status = $orders[0]->status;
        if($status == "accepted"){
            // INFORMACION DEL CLIENTE 
            $nombre=$orders[0]-> client_first_name;
            $apellido = $orders[0]->client_last_name;
            $email = $orders[0]-> client_email;
            $telefono = $orders[0]-> client_phone;
            $latitude=$orders[0]->latitude;
            $longitude=$orders[0]->longitud;

            //INFORMACION DEL RESTAURANTE 
            $nombre_restaurante = $orders[0]-> restaurant_name;
            $id_restaurante = $orders[0]-> restaurant_id;
            $telefono_restaurante = $orders[0]-> restaurant_phone;

            //INFORMACION DE LA ORDEN 
            $id=$orders[0]->id;
            $price=$orders[0]->total_price;
            $tipo =$orders[0]->type;
            $tipo_pago = $orders[0]-> payment;
			
			// INGRESO A LA BASE DE DATOS
			$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname_paya);
			$query_insert_orden = "INSERT INTO ordenes_gloria (id_orden,id_restaurante,nombre_restaurante,precio_orden) VALUES( '".$id."' , '".$id_restaurante."', '".$nombre_restaurante."', '".$price."' );";
			$conn->query($query_insert_orden);
			$conn -> close();
			fwrite($file, "El query aplicado es  : ".$query_insert_orden."\n");
        
            //$type = $orders[0]['tax_list'][1]['type'];
                fwrite($file, "INFORMACION DEL CLIENTE: \n");
                fwrite($file, "El nombre del cliente es: ".$nombre."\n");
                fwrite($file, "El apellido del cliente es: ". $apellido."\n");
                fwrite($file, "El email del cliente es: ". $email."\n");
                fwrite($file, "El telefono del cliente es: ". $telefono."\n");
                fwrite($file, "La localizacion del cliente es: lalitud: ".$latitude." longitud: ". $longitude."\n");
                fwrite($file, "\n INFORMACION DEL RESTAURANTE: \n");
                fwrite($file, "El nombre del restaurante es: ". $nombre_restaurante."\n");
                fwrite($file, "El ID del restaurante es: ". $id_restaurante."\n");
                fwrite($file, "El telefono del restaurante es: ". $telefono_restaurante. "\n");
                fwrite($file, "\n INFORMACION DE LA ORDEN: \n");
                fwrite($file, "El ID de la orden es: ".$id."\n");
                fwrite($file, "El precio es: ".$price."\n");
                fwrite($file, "El tipo de la orden es: ". $tipo."\n");
                fwrite($file, "El metodo de pago es: ".$tipo_pago."\n");
				fwrite($file, "La orden tiene este STATUS: ".$status."\n");
                
					$info_client = array(
						'id' => $id,
						'firstName' => $nombre,
						'lastName' => $apellido,
						'email'=> $email,
						'phone' => $telefono,
						'addressStreet' => "Calle 90 Este, Panamá, Panama",
						'addressHome' => "Casa 22 B"
					);

					$info_product = array(
						'id'=> 260,
						'name' => "Vitálica"     
					);

					$body1 = array(
						'id'=> 260,
						'product' => $info_product,
						'notes' => "Nota de producto vitalica",
						'additionals' => [],
						'remove'=> [],
						'quantity' => 2.0
					);
					
					$remove = array(
						'id' => 8,
						'name' => "Almendras Fileteadas"
						
					);  

					$aditionals = array(
						'id' => 477,
						'quantity' => "1.0",
						'name' => "Quinoa",
						'price' => 15.0
						
					); 

					$body2 = array(
						'id' => 266,
						'product' => $info_product,
						'quantity' => 2.0,
						'remove' => array($remove),
						'additionals' => array($aditionals)
					);
					
					$fullbody = array(
					
						$body1,$body2
					);
					

					$ordenes =  array(
							'id' => '4587',
							'systemTypeId' => 1,
							'trackingNumber' => 8585,
							'trackingShort' =>  8585,
							'notes' => "Nota de orden",
							'payformId' => 2,
							'createdAt' => 1602456580000,
							'client' => $info_client,
							'subtotal' => 409.48,
							'tax' => 65.52,
							'total' => $price,
							'totalDiscount' => 0.0,
							'tip' => 1.0,
							'shippingCost' => 0.0,
							'body' => $fullbody,
							'orderAdditional' => [],
					);


					$data = array(
						'keyXpedidos' => '096fc2e4-66df-4d71-9b5a-5d3290d75d6d',
						'orders' => array($ordenes),
						'ordersCount' => '1',
						'dateSynch' => '1602456621447',
					);

					// Data should be passed as json format
					$data_json = json_encode($data);


					// API URL to send data
					$url = 'http://201.221.230.229:8090/xspos/api/XPosXPedidos/Send';

					// curl initiate
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, $url);

					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

					// SET Method as a POST
					curl_setopt($ch, CURLOPT_POST, 1);

					// Pass user data in POST command
					//curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
					curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));

					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					// Execute curl and assign returned data
					$response  = curl_exec($ch);
					$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				  
					if($response){
						echo "Todo bien:<br>";

					}else{
						echo "Algo esta mal";
					}
					// Close curl
					curl_close($ch);

					// See response if data is posted successfully or any error
				   // print_r ($response);


				//curl_close($curl);
				echo '<br>HTTP code: ' . $httpcode;
				echo '<br>HTTP respuesta: ' .$response;
				echo '<br><br>';
				echo $data_json;

				echo '<br><br>';
				echo json_encode($data);
				fwrite($file, "\nEl codigo HTTP fue: ".$httpcode);
				fwrite($file, "\nLa respuesta HTTP fue: ".$response);
				
                Guardar($nombre, $apellido, $email, $latitude, $longitude, $telefono);
            }
            else {
                $file = fopen('Gloria_Api.txt','w');
                fwrite($file, 'La Orden fue:'.$status);

             }

    }

?>