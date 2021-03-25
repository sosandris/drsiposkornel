<?php

get_stock_and_price('uport%201110',$r,$ar); 
echo $r." / ".$ar;

function get_stock_and_price($arg_model_name, &$arg_include_stock='', &$arg_include_price='') {
		/* H�vd meg ezt a f�ggv�nyt, ha meg akarod tudni, hogy mekkora a rakt�rk�szlet
			egy vagy t�bb modellb�l, �s mennyibe ker�l(nek).
			Bemeneti param�terek:
				$arg_model_name: a k�rd�ses modell neve, lehet t�mb is
				&$arg_include_stock='': �res v�ltoz�, amibe belet�lti a rakt�rk�szletet,
						ha a modell t�mb, ez is t�mb lesz
				&$arg_include_price='': �res v�ltoz�, amibe belet�lti a modell �r�t, 
						ha a modell t�mb, ez is t�mb lesz
			
			Kimeneti param�terek:
				Visszat�r�si �rt�k: true, ha siker�lt; false, ha nem siker�lt
				&$arg_include_stock: a rakt�rk�szlet. Ha a modell t�mb, ez is t�mb lesz 
					Ha a k�rt modell ismeretlen: "N/A"
					Ha <0:
						-1: socket_create hiba
						-2: socket_connect hiba
						-3: socket_write hiba
				&$arg_include_price : az �r.  Ha a modell t�mb, ez is t�mb lesz
					Ha a k�rt modell ismeretlen: "N/A"
					Ha <0, u.a. mint fent
				
			TCP kapcsolatot �p�t ki a Com-Forth bels� h�l�zat�val a 12654-es porton.
			A webszerveren enged�lyezve kell, hogy legyen a php.ini-ben az enable-socket,
			�s a Com-Forth irodai router�n a 12654-�s 
			porton �rkez� adatokat tov�bb�tania kell a v�laszolni tud� szervernek.
		*/
		
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if (!is_array($arg_model_name)) {
			$model_name[0] = $arg_model_name;
		} else {
			$model_name = $arg_model_name;
		}
	    if ($socket === false) {
			$include_stock = -1;
			$include_price = -1;
		} else {
			$result = socket_connect($socket, '178.48.12.49', 21);
			if ($result === false) {
				$include_stock = -2;
				$include_price = -2;
			} else {
				foreach ((array)$model_name as $key => $value){
					$in = "?cfinfo=".$value."&";
					$out = '';
					$result = socket_write($socket, $in, strlen($in));
					if ($result === false || $result < strlen($in)) {
						$include_stock = -3;
						$include_price = -3;
					} else {
						$out = socket_read($socket, 2048); 
						$include_pos = strpos($out,";");
						$include_stock[] = substr($out,0,$include_pos);
						// space-telen�t�s
						// $out = str_replace(" ",'', $out);
						$include_price[] = substr($out,$include_pos+1);
					}
				}
				socket_close($socket);
			}
		}
		if (count($include_stock) == 1) {
			$arg_include_stock = $include_stock[0];
			$arg_include_price = $include_price[0];
			if ($arg_include_stock < 0) {
				return false;
			} else {
				return true;
			}
		} else {
			$arg_include_stock = $include_stock;
			$arg_include_price = $include_price;
			return true;
		}
	}	
?>