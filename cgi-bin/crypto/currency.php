<?php

if(!isset($_SESSION)) {
  session_start();
} ;
ob_start();
$_SESSION['url'] = $_SERVER['REQUEST_URI'];

require '../composer/vendor/autoload.php';
include '../connectToDB.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='refresh' content='30' >
<title id='pageTitle'>Adam's Sandbox</title>";
include('../header.php');
echo "</head><body><div class='container'>";
include('../navigation.php');

$api = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?slug=';

if (isset($_SESSION['userid'])) {
    $user_id = $_SESSION['userid'];
    if ($user_id == 2) {
        $user_id = 1;
    }
    $sqlcomplete   = "SELECT * FROM orion.crytpo WHERE user_id ='$user_id' AND currency <> 'usd'";
    $querycomplete = $db->query($sqlcomplete);
    $count         = $querycomplete->rowCount();
    
    echo "<div class='container text-center'><h1>Crypto Currency Tracker</h1>";
    if ($count > 0) {
        echo "<h3>Total Value:<div id='value'></div> </h3><h3>Total Investment:<div id='cost'></div> </h3><h3>Total Profit:<div id='profit'></div></h3>";
        echo "<table id='myTable'>";
        echo "<thead><tr><td>Coin</td><td>Amount</td><td>Current Price</td><td>Current Value</td><td>Cost</td><td>Profit/Loss</td></tr></thead><tbody>";
        
        foreach ($querycomplete as $item) {
            $headers  = array(
                "X-CMC_PRO_API_KEY" => "6a77721a-4bfb-4542-a1d0-68002ca717bd",
                "Accepts" => "application/json"
            );
            $response = Unirest\Request::get($api . $item['api_id'], $headers);
            
            $json  = json_decode($response->raw_body, true);
            $price = $json['data'][key($json['data'])]['quote']['USD']['price'];
            
            echo "<tr><td>" . ($item['currency'] . "</td><td>" . $item['amount'] . " " . $json[0]['symbol'] . "</td><td>$" . number_format($price, 3, ".", ",") . "</td><td>$" . number_format(($price * $item['amount']), 2, ".", ",") . "</td><td>$" . number_format($item['cost'], 2, ".", ",") . "</td><td>$" . number_format((($price * $item['amount']) - $item['cost']), 2, ".", ",") . "</td></tr>");
        }
        echo "</tbody></table>";
    }
    
    
} else
    header("location: ../users/signin.php");
?>
</div></body>
<link rel='stylesheet' type='text/css' href='//cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css'/>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js'></script>
<script type='text/javascript'>
$(document).ready(function(){
  var table = $('#myTable').dataTable( {
    'order': [[ 5, 'desc' ]],
    'paging': false,
    'searching': false,

      'info':false,
      'footerCallback': function ( row, data, start, end, display ) {
            var api = this.api(), data;
            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            // Total over all pages
            total = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            total_cost = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            total_value = api
                .column( 3 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            var total_f = '$' + total.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            var total_cost_f = '$' + total_cost.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            var total_value_f = '$' + total_value.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            // Update footer
            $('#profit').html(total_f);
            $('#cost').html(total_cost_f);
            $('#value').html(total_value_f);
            document.title = total_f;
        }
    });
  });
</script>
</html>