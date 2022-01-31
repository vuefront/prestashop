<?php
/**
 * 2019 (c) VueFront
 *
 * MODULE VueFront
 *
 * @author    VueFront
 * @copyright Copyright (c) permanent, VueFront
 * @license   MIT
 *
 * @version   0.1.0
 */

class ResolverCommonHome extends Resolver
{
    public function get()
    {
        $meta_info = Meta::getMetaByPage('index', $this->context->language->id);

        return array(
            'meta' => array(
                'title' => $meta_info['title'],
                'description' => $meta_info['description'],
                'keyword' => $meta_info['keywords'],
            ),
        );
    }

    public function searchUrl($args)
    {
        $this->load->model('common/seo');

        $result = $this->model_common_seo->searchKeyword($args['url']);

        return $result;
    }

    public function updateApp($args)
    {
        $this->load->model('common/vuefront');
        $this->model_common_vuefront->editApp($args['name'], $args['settings']);

        return $this->model_common_vuefront->getApp($args['name']);
    }

    public function updateSite($args)
    {
        try {
            $tmpFile = tempnam(sys_get_temp_dir(), 'TMP_');
            rename($tmpFile, $tmpFile .= '.tar');
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, "https://vuefront2019.s3.amazonaws.com/sites/".$args['number']."/vuefront-app.tar");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 150);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $data = curl_exec($ch);
            curl_close($ch);
            file_put_contents($tmpFile, $data);
            $this->removeDir(_PS_ROOT_DIR_ . '/vuefront');
            $phar = new PharData($tmpFile);
            $phar->extractTo(_PS_ROOT_DIR_ . '/vuefront');
            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return false;
    }

    private function removeDir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object)) {
                        $this->removeDir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    public function authProxy($args)
    {
        $this->load->model('common/vuefront');

        if (!$this->context->cookie->isLogged()) {
            return;
        }
        $app_info = $this->model_common_vuefront->getApp($args['app']);

        $url = str_replace(':id', $this->context->cookie->id_customer, $app_info['authUrl']);
        $result = $this->model_common_vuefront->request($url, [
            'customer_id' => $this->context->cookie->id_customer,
        ], $app_info['jwt']);

        if (!$result) {
            return '';
        }

        return $result['token'];
    }

    public function version($args)
    {
        return '1.0.0';
    }
}
