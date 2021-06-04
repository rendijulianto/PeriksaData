<?php
class Tools
{
    public function curl($url, $data = null, $headers = null, $proxy = null)
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
        );

        if ($proxy != "")
        {
            $options[CURLOPT_HTTPPROXYTUNNEL] = true;
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4;
            $options[CURLOPT_PROXY] = $proxy;
        }

        if ($data != "")
        {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        if ($headers != "")
        {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    public function fetch_value($str, $find_start, $find_end)
    {
        $start = @strpos($str, $find_start);
        if ($start === false)
        {
            return "";
        }
        $length = strlen($find_start);
        $end = strpos(substr($str, $start + $length) , $find_end);
        return trim(substr($str, $start + $length, $end));
    }
    public function periksaData($email)
    {
        $data = 'email=' . $email . '';

        $result = $this->curl('https://periksadata.com/', $data);

        if (preg_match('/<section class="bg--dark">/', $result))
        {
            $pecahSectionKasus = explode('<section class="bg--dark">', $result);
            $stopSectionkasus = explode('</section>', $pecahSectionKasus[1]);
            $pecahDetailKasus = explode(('<div class="col-md-6">') , $stopSectionkasus[0]);
            $data = array();
            for ($i = 1;$i < count($pecahDetailKasus);$i++)
            {
                $kasus = $pecahDetailKasus[$i];
                $aplikasi = $this->fetch_value($kasus, '<h5>', '</h5>');
                $tanggal = $this->fetch_value($kasus, '<p><small>Tanggal kejadian</small><br><b>', '<br>');
                $dataBocor = $this->fetch_value($kasus, '<small>Data yang bocor</small><br><b>', '</b>');
                $data[] = ['aplikasi' => $aplikasi, 'tanggal' => $tanggal, 'databocor' => $dataBocor];
            }

            $result = ['status' => true, 'catatan' => 'Email ' . $email . ' tercatat sudah mengalami ' . (number_format(count($pecahDetailKasus) - 1)) . ' kebocoran data.', 'data' => $data];

        }
        else
        {
            $result = ['status' => false, 'catatan' => 'Belum ditemukan kebocoran terhadap email anda'];
        }
        return json_encode($result);
    }
}


echo " _____                _ _        _       _ \n";
echo "|  __ \              | (_)      | |     | |\n";
echo "| |__) |___ _ __   __| |_       | |_   _| |\n";
echo "|  _  // _ \ '_ \ / _` | |  _   | | | | | |\n";
echo "| | \ \  __/ | | | (_| | | | |__| | |_| | |\n";
echo "|_|  \_\___|_| |_|\__,_|_|  \____/ \__,_|_|\n";
echo "===========================================\n";
echo "Data diambil dari periksadata.com\n";
echo "===========================================\n";
echo "\n";
echo "Masukan Email :  ";
$email = trim(fgets(STDIN));

$tools = new Tools;
$periksaData = json_decode($tools->periksaData($email));
echo "\n";
echo "=======Result======== \n";
if($periksaData->status) {
    echo "Pesan : $periksaData->catatan \n";
    foreach ($periksaData->data as $data) {
        echo "Kebocoran Aplikasi $data->aplikasi \n";
        echo "Tanggal : $data->tanggal \n";
        echo "Jenis Kebocoran : $data->databocor \n";
    }
} else {
    echo "Pesan : $periksaData->catatan ";
}

