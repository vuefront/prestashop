<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 * @version   0.1.0
 */

class ModelCommonVuefront extends Model
{
    public function editApp($name, $appSetting)
    {
        $appSetting['codename'] = $name;

        $option = Configuration::get('vuefront-apps');

        $setting = array();

        try {
            $setting = Tools::jsonDecode($option, true);
        } catch (Exception $e) {
        }

        $app = $this->getApp($name);

        if (!empty($app)) {
            foreach ($setting as $key => $value) {
                if ($value['codename'] == $name) {
                    $setting[$key] = $appSetting;
                }
            }
        } else {
            $setting[] = $appSetting;
        }

        Configuration::updateValue('vuefront-apps', Tools::jsonEncode($setting), null, 0, 0);
    }
    
    public function checkAccess()
    {
        $option = Configuration::get('vuefront-settings');

        $setting = array();

        try {
            $setting = Tools::jsonDecode($option, true);
        } catch (Exception $e) {
        }

        if (!Tools::getValue('accessKey')) {
            return false;
        }

        $result = false;
        foreach ($setting as $key => $value) {
            if ($key === 'accessKey' && Tools::getValue('accessKey') === $value) {
                $result = true;
            }
        }
        return $result;
    }

    public function getApp($name)
    {
        $option = Configuration::get('vuefront-apps');

        $setting = array();

        try {
            $setting = Tools::jsonDecode($option, true);
        } catch (Exception $e) {
        }
        foreach ($setting as $value) {
            if ($value['codename'] == $name) {
                return $value;
            }
        }

        return false;
    }

    public function getAppsForEvent()
    {
        $option = Configuration::get('vuefront-apps');

        $setting = array();

        try {
            $setting = Tools::jsonDecode($option, true);
        } catch (Exception $e) {
        }
        $result = [];
        foreach ($setting as $value) {
            if (!empty($value['eventUrl'])) {
                $result[] = $value;
            }
        }

        return $result;
    }

    public function pushEvent($name, $data)
    {
        $apps = $this->getAppsForEvent();

        foreach ($apps as $value) {
            $output = $this->request($value['eventUrl'], [
                'name' => $name,
                'data' => $data,
            ]);

            if ($output) {
                $data = $output;
            }
        }

        return $data;
    }

    public function request($url, $data, $token = false)
    {
        $ch = curl_init();
        $headers = array();

        $headers[] = 'Content-type: application/json';

        if ($token) {
            $headers[] = 'Authorization: Bearer '.$token;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);

        $error = curl_error($ch);

        if ($error) {
            throw new Exception($error);
        }

        $result = json_decode($result, true);
        return $result;
    }


    public function mergeSchemas($files)
    {
        $rootQueryType = '';
        $types = '';
        $rootMutationType = '';
        foreach ($files as $value) {
            preg_match('/type\s+RootQueryType\s\{\s*\n([^\}]+)/', $value, $matched);
            if (!empty($matched[1])) {
                $rootQueryType = $rootQueryType.PHP_EOL.$matched[1];
            }
            preg_match('/type\s+RootMutationType\s\{\s*\n([^\}]+)/', $value, $mutationMatched);
            if (!empty($mutationMatched[1])) {
                $rootMutationType = $rootMutationType.PHP_EOL.$mutationMatched[1];
            }
            preg_match('/([a-zA-Z0-9\=\s\}\_\-\@\{\:\[\]\(\)\!\"]+)type RootQueryType/', $value, $typesMatched);
            if (!empty($typesMatched[1])) {
                $types = $types.PHP_EOL.$typesMatched[1];
            }
        }
        return "${types}".PHP_EOL."type RootQueryType {".PHP_EOL."${rootQueryType}"
            .PHP_EOL."}".PHP_EOL."type RootMutationType {".PHP_EOL."${rootMutationType}"
            .PHP_EOL."}";
    }
}
