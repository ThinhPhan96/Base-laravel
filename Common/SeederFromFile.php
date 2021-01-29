<?php


namespace App\Http\SeederFile;


class SeederFromFile
{

    /**
     * Seeder form file json
     *
     * @param mixed $filename
     * @return array|false|mixed
     */
    public static function seederFromJSON($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $data = array();

        if (($handle = fopen($filename, 'r')) !== false) {
            $jsonString = file_get_contents($filename);
            $data = json_decode($jsonString, true);
            fclose($handle);
        }
        $dataResult = isset($data[2]['data']) ? $data[2]['data'] : $data;
        return $dataResult;
    }

    /**
     * Collect data from a given CSV file and return as array
     *
     * @param $filename
     * @param string $deliminator
     * @return array|bool
     */
    private function seedFromCSV($filename, $delimitor = ",")
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = array();
        $data = array();

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimitor)) !== false) {
                /*if(!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }*/
                $param = array(
                    'id' => $row[0],
                    'code' => $row[1],
                    'name' => $row[2],
                    'kana' => $row[3],
                );
                array_push($data, $param);
            }

            fclose($handle);
        }

        return $data;
    }

}
