<?php

class tinkoff_invoice
{


    private $refresh_token = "0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000";// length=88
    private $my_inn = '0000000000';// length= 12 || 10  Ваш ИНН
    private $access_token = "00000";
    public $invoice_id = "00000";
    private $pdf_path = "/pdf";


    function __construct($refresh_token, $my_inn)
    {
        $this->refresh_token = $refresh_token;
        $this->my_inn = $my_inn;
    }

    function get_access_token()
    {
        $refresh_token = $this->refresh_token;
        $params = ['grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        ];
        $headers = [
            'POST /secure/token HTTP/1.1',
            'Content-Type: application/x-www-form-urlencoded'
        ];
        $curlURL = 'https://sso.tinkoff.ru/secure/token';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlURL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $curl_res = curl_exec($ch);
        if ($curl_res) {
            $server_output = json_decode($curl_res);
        }

        //Считываем access_token - он нужен для реализации 2 этапа
        $access_token_pos_start = strpos($curl_res, 'access_token', 0);
        $access_token_pos_start = $access_token_pos_start + 15;
        $token_type_pos_start = strpos($curl_res, "token_type", 0);
        $access_token = mb_substr($curl_res, $access_token_pos_start, ($token_type_pos_start - $access_token_pos_start - 3));
        $this->access_token = $access_token;
        echo($access_token);
        return ($access_token);

    }


    function create_invoice($bank_info, $order_id, $email)
    { //
        $access_token = $this->access_token;
        $my_inn = $this->my_inn;


        $buyer = [
            'name' => $bank_info['name'],
            'inn' => $bank_info["inn"],
            'address' => $bank_info['address'],
			'bank'=>[
        'name'=> ''
        ],
        ];
        $payment = [
            'dueDate' => date('c', mktime(0, 0, 0, date("m") + 1, date("d"), date("Y"))),
        ];


        $categories = [];
        $contacts = [['email' => $email
        ]];
        $body = [
            'buyer' => $buyer,
            'contacts' => $contacts,
            'number' => (int)$order_id,
            'comment' => 'комментарий',
            'categories' => $categories,
            'payment' => $payment,
        ];
        $body_json = json_encode($body, JSON_UNESCAPED_UNICODE);
        var_dump($body_json);
        $params =//        'Authorization'=>$access_token,
            $body_json;;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ];
        $curlURL = 'https://sme-partner.tinkoff.ru/api/v1/partner/company/' . $my_inn . '/invoice/outgoing';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlURL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $curl_res = curl_exec($ch);

        if ($curl_res) {
            $server_output = json_decode($curl_res, true);
        }
        echo "<br> id инвойса: " . $server_output["result"]["id"];

        $this->invoice_id = $server_output["result"]["id"];
        return $server_output["result"]["id"];
    }


    function add_products($data)
    {
        $invoice_id = $this->invoice_id;
        $my_inn = $this->my_inn;

        $access_token = $this->access_token;
//    $items=[
//        'name'=>'Удлинитель корпуса',     // название товара
//        'price'=>90,                      // цена
//        'unit'=>'шт.',                    // единицы
//        'vat'=>"Без НДС",                 // название товара
//        'amount'=>5,                      // количество
//    ];
        foreach ($data as $key => $data_item) {
            $items = $data_item;

            $body = $items;

            $body_json = json_encode($body, JSON_UNESCAPED_UNICODE);

            $params =//
                $body_json;;
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token

            ];

            $curlURL = 'https://sme-partner.tinkoff.ru/api/v1/partner/company/' . $my_inn . '/invoice/outgoing/' . $invoice_id . '/item/' . $key;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curlURL);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            $curl_res = curl_exec($ch);
            echo("<br>");
            echo("<br>");
            var_dump($curl_res);
            echo("<br>");
            echo("<br>");
            if ($curl_res) {
                $server_output = json_decode($curl_res);
                echo($server_output);
            } else {
                echo("Ничего");
            }


            echo("<br>");
            echo("<br>");
            var_dump($server_output);
            $server_output .= $server_output;
            echo("<br>");
            echo("<br>");
        }


        return ($server_output);

    }

    function send_contacts($email, $tel1, $tel2)
    {
        $access_token = $this->access_token;
        $invoice_id = $this->invoice_id;
        $my_inn = $this->my_inn;


        if (($tel1) != "") {
            $tel = "+" . ($tel1);
        } elseif (($tel2) != "") {
            $tel = "+" . ($tel2);
        }


        $contacts = [
            [
                'email' => $email,
                'phone' => $tel
            ]
        ];
        $body = [
            'contacts' => $contacts,
        ];
        $body_json = json_encode($contacts, JSON_UNESCAPED_UNICODE);
        var_dump($body_json);
        $params =//        'Authorization'=>$access_token,
            $body_json;;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ];
//    $curlURL='https://sme-partner.tinkoff.ru/api/v1/partner/company/'.$my_inn.'/invoice/outgoing';
        $curlURL = 'https://sme-partner.tinkoff.ru/api/v1/partner/company/' . $my_inn . '/invoice/' . $invoice_id . '/contacts';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlURL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $curl_res = curl_exec($ch);
        echo("<br>");
        echo("<br> от отправка контакта получили: ");
        var_dump($curl_res);
        echo("<br>");
        echo("<br> теперь в json");
        if ($curl_res) {
            $server_output = json_decode($curl_res, true);
        }


        return '';
    }

    function send_created_invoice()
    {
        $access_token = $this->access_token;
        $invoice_id = $this->invoice_id;
        $my_inn = $this->my_inn;

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ];
        $curlURL = 'https://sme-partner.tinkoff.ru/api/v1/partner/company/' . $my_inn . '/invoice/outgoing/' . $invoice_id . '/send';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlURL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $curl_res = curl_exec($ch);
        echo("<br>");
        echo("<br> от сервера получили 2: ");
        var_dump($curl_res);
        echo("<br>");
        echo("<br> теперь в json");
        if ($curl_res) {
            $server_output = json_decode($curl_res, true);
        }


        return $server_output["result"];
    }

    function get_pdf()
    {
        $access_token = $this->access_token;
        $invoice_id = $this->invoice_id;
        $my_inn = $this->my_inn;

        $path2 = $this->pdf_path;
        mkdir($path2);
        $body = [


        ];

        $body_json = json_encode($body, JSON_UNESCAPED_UNICODE);
        var_dump($body_json);

        $params =//        'Authorization'=>$access_token,
            $body_json;;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token

        ];
        $curlURL = 'https://sme-partner.tinkoff.ru/api/v1/partner/company/' . $my_inn . '/reports/invoice/' . $invoice_id . '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlURL);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);


        $curl_res = curl_exec($ch);
        file_put_contents('pdf/' . $invoice_id . '.pdf', $curl_res);
        echo("<br>");
        echo("<br> PDF: ");
        var_dump($curl_res);
        echo("<br>");
        echo("<br> теперь в json");
        if ($curl_res) {
            $server_output = json_decode($curl_res);
            echo($server_output);
        } else {
            echo("Ничего");
        }


        echo("<br>");
        echo("<br>");
        var_dump($server_output);
        echo("<br>");
        echo("<br>");
        return ($server_output);
    }

}

?>


