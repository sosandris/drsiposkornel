<?php

get_stock_and_price('uport%201110',$r,$ar); 
echo $r." / ".$ar;

function get_stock_and_price($arg_model_name, &$arg_include_stock='', &$arg_include_price='') {
		/* Hívd meg ezt a függvényt, ha meg akarod tudni, hogy mekkora a raktárkészlet
			egy vagy több modellbõl, és mennyibe kerül(nek).
			Bemeneti paraméterek:
				$arg_model_name: a kérdéses modell neve, lehet tömb is
				&$arg_include_stock='': üres változó, amibe beletölti a raktárkészletet,
						ha a modell tömb, ez is tömb lesz
				&$arg_include_price='': üres változó, amibe beletölti a modell árát, 
						ha a modell tömb, ez is tömb lesz
			
			Kimeneti paraméterek:
				Visszatérési érték: true, ha sikerült; false, ha nem sikerült
				&$arg_include_stock: a raktárkészlet. Ha a modell tömb, ez is tömb lesz 
					Ha a kért modell ismeretlen: "N/A"
					Ha <0:
						-1: socket_create hiba
						-2: socket_connect hiba
						-3: socket_write hiba
				&$arg_include_price : az ár.  Ha a modell tömb, ez is tömb lesz
					Ha a kért modell ismeretlen: "N/A"
					Ha <0, u.a. mint fent
				
			TCP kapcsolatot épít ki a Com-Forth belsõ hálózatával a 12654-es porton.
			A webszerveren engedélyezve kell, hogy legyen a php.ini-ben az enable-socket,
			és a Com-Forth irodai routerén a 12654-ös 
			porton érkezõ adatokat továbbítania kell a válaszolni tudó szervernek.
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
						// space-telenítés
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