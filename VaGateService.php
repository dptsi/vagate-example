<?php
namespace App;
use DateTime;
use stdClass;
use Exception;
use App\VaGate;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

class VaGateService
{
    /**
     * @param VaGate $vaGate
     * @param string $method
     * @param array $data
     * @return ResponseInterface|null
     * @throws GuzzleException
     */
    private function fetchData(VaGate $vaGate, string $method, array $data): ?ResponseInterface
    {
        $response = null;
        $client = new Client(['verify' => false]);

        try {
            if ($method === 'get') {
                $response = $client->request($method, $vaGate->getEndPoint());
            } else {
                $response = $client->request($method, $vaGate->getEndPoint(), [
                    'form_params' => $data
                ]);
            }
        } catch (ServerException $serverException) {
            throw $serverException;
        } catch (ConnectException $connectException) {
            throw $connectException;
        } catch (GuzzleException $guzzleException) {
            throw $guzzleException;
        } catch (Exception $exception) {
            Log::error($exception);
        }

        return $response;
    }

    /**
     * @param string $method
     * @param bool $isReuse
     * @return VaGate
     */
    private function fetchVaData(string $method, bool $isReuse = false): VaGate
    {
        if ($method == 'create') {
            $unitId = '';
            $amuKey = '';
        }

        if ($isReuse) {
            $vaGate = new VaGate($unitId, $amuKey, true);
        } else {
            $vaGate = new VaGate($unitId, $amuKey);
        }

        return $vaGate;
    }

    /**
     * @param VaGate $vaGate
     * @return string
     */
    private function generateTrxId(VaGate $vaGate): string
    {
        $unitId = $vaGate->getUnitId();
        $day = date('d');
        $month = date('m');
        $year = date('y');
        $random_string = Str::random(10);

        $trx_id = $unitId . $year . $month . $day . $random_string;

        return $trx_id;
    }

    /**
     * @param string $method
     * @param object $data
     * @return stdClass|null
     * @throws GuzzleException
     */
    public function requestVa(string $method, object $data): ?stdClass
    {
        $request = '0.0.0.0';
        $inputDate = date('Y-m-d H:i:s');
        $vaGate = $this->fetchVaData($method);
        $trxId = $this->generateTrxId($vaGate);

        $params = [
            'type' => 'requestva',
            'trx_id' => $trxId,
            'id_unit' => $vaGate->getUnitId(),
            'nama' => $data->nama,
            'nominal' => $data->nominal,
            'tanggal_input' => $inputDate,
            'tanggal_expired' => $data->expired,
            'ip_address' => $request,
            'amu_key' => $vaGate->getAmuKey(),
        ];

        try {
            $response = $this->fetchData($vaGate, 'post', $params);
        } catch (Exception $e) {
            throw $e;
        }

        if ($response && $response->getStatusCode() == '200') {
            $vaGateArray = json_decode($response->getBody(), true);

            if ($vaGateArray['status'] === 'fail') {
                return null;
            }

            $obj = new stdClass();
            $obj->trxId = $trxId;
            $obj->virtualAccount = $vaGateArray['virtual_account'];
            $obj->expiredAt = $data->expired;

            return $obj;
        }

        return null;
    }

    /**
     * @param string $type
     * @param object $data
     * @return stdClass|null
     * @throws GuzzleException
     */
    public function reuseVa(string $type, object $data): ?stdClass
    {
        $request = request();
        $inputDate = new Date(new DateTime('now'));
        $vaGate = $this->fetchVaData($type, true);
        $trxId = $this->generateTrxId($vaGate);

        $params = [
            'type' => 'reuseva',
            'trx_id' => $trxId,
            'virtual_account' => $data->va,
            'id_unit' => $vaGate->getUnitId(),
            'nama' => $data->nama,
            'nominal' => $data->nominal,
            'tanggal_input' => $inputDate->toIsoDateString(),
            'tanggal_expired' => $data->expired,
            'ip_address' => $request->ip(),
            'amu_key' => $vaGate->getAmuKey(),
            'nrp' => $data->nrp,
            'keterangan' => $data->keterangan
        ];

        try {
            $response = $this->fetchData($vaGate, 'post', $params);
        } catch (Exception $e) {
            throw $e;
        }

        if ($response && $response->getStatusCode() == '200') {
            $vaGateArray = json_decode($response->getBody(), true);

            if ($vaGateArray['status'] === 'fail') {
                return null;
            }

            $obj = new stdClass();
            $obj->trxId = $trxId;
            $obj->virtualAccount = $vaGateArray['virtual_account'];
            $obj->expiredAt = $data->expired;

            return $obj;
        }

        return null;
    }
}
