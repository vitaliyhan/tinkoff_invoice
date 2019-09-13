# tinkoff_invoice
Tinkoff Business invoice sender. Класс для выставление счета для пользователей  Tinkoff.Business используя ныне скрытые, но рабочие методы https://openapi.tinkoff.ru/ выставления счета


В данный момент (13.09.2019 c апреля 2019 года) существует следующая проблема - в Api Тинькофф банка  https://openapi.tinkoff.ru пропали все методы для выставления счета.

К счастью, не смотря на то, что вся документация пропала, методы до сих пор работают. Если вам удастся получить refresh token в вашей учетной записи, возможно через службу поддержки, вам открывается возмоность выставления счетов контагентам.

Применение:


$refresh_token = ""; // ваш refresh_token !!!!
$my_inn = ""; // ваш ИНН !!!!

$client_info["inn"] = "1313131313"; // Инн компании получателя
$client_info["name"] = "ООО Пупкин"; // Наименование компании получателя
$client_info["address"] = "Кудыкины горы"; // Юр Адрес компании получателя
$client_info["email"] = "ew@rer.ru"; // е-mail клиента
// $tel1, $tel2 мобильный телефон клиента для SMS оповещения,внутрь попадет только один телефон в формате "79008007060"
$client_tel1 = "";
$client_tel2 = "";
$order_id = ""; //номер заказ


$data[1]['name'] = "тестовый товар";
$data[1]['price'] = 250; // Цена
$data[1]['unit'] = 'шт.'; // единицы
$data[1]['vat'] = "Без НДС"; // НДС, БЕЗ НДС
$data[1]['amount'] = 2; // количество
// запросы к сайту тинькофф банка.

$tinkoff_invoice = new tinkoff_invoice($refresh_token, $my_inn);
// получение access_token
$tinkoff_invoice->get_access_token();

// созадем invoice
$tinkoff_invoice->create_invoice($client_info, $order_id, $client_info["email"]);
// добавляем в него товары
$tinkoff_invoice->add_products($data);
// добавляем контакты для отправки invoice

$tinkoff_invoice->send_contacts($client_info["email"], $client_tel1, $client_tel2);
// отправляем invoice
$tinkoff_invoice->send_created_invoice();
// сохраняем накладную на сервер.
$tinkoff_invoice->get_pdf();

//
$tinkoff_invoice->invoice_id;// Здесь хранится invoice_id, по нему можно получить название pdf файла - invoice_id.".pdf"

